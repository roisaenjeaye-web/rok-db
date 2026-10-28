<?php

namespace App\Services;

use App\Models\BotSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Kirim pesan WhatsApp melalui Fonnte API
     */
    public static function sendMessage(string $target, string $message, ?string $token = null): array
    {
        $token = $token ?: BotSetting::get('fonnte_token', env('FONNTE_TOKEN'));

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum diisi. Masukkan token di menu Pengaturan Bot WA.',
            ];
        }

        if (empty($target)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan belum diisi.',
            ];
        }

        try {
            $payload = [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ];

            // Jika target berupa Grup WhatsApp (@g.us), lakukan tag ke seluruh member grup
            if (str_contains($target, '@g.us')) {
                $members = self::getGroupMembers($target, $token);
                if (!empty($members)) {
                    $mentionTokens = array_map(fn($m) => "@{$m}", $members);
                    $mentionString = implode(' ', $mentionTokens);

                    // Ganti teks placeholder @all langsung dengan mention member
                    if (str_contains($message, '@all')) {
                        $payload['message'] = str_replace('@all', $mentionString, $message);
                    } else {
                        $payload['message'] = $message . "\n" . $mentionString;
                    }

                    // Sertakan parameter mentions untuk gateway WhatsApp
                    $payload['mentions'] = implode(',', $members);
                }
            } else {
                // Untuk chat personal (bukan grup), bersihkan @all agar tidak janggal
                $payload['message'] = str_replace("@all\n", '', $payload['message']);
                $payload['message'] = str_replace("@all", '', $payload['message']);
            }


            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(12)->post('https://api.fonnte.com/send', $payload);

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && $json['status'] == true) {
                return [
                    'success' => true,
                    'message' => 'Pesan WhatsApp berhasil terkirim via Bot Fonnte!',
                    'data' => $json,
                ];
            }

            $errMsg = $json['reason'] ?? ($json['detail'] ?? ($json['message'] ?? 'Gagal mengirim pesan via Fonnte. Periksa token atau koneksi WhatsApp di dashboard Fonnte.'));
            return [
                'success' => false,
                'message' => $errMsg,
                'data' => $json,
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi ke Fonnte gagal: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Cek status koneksi token & device Fonnte
     */
    public static function checkDeviceStatus(?string $token = null): array
    {
        $token = $token ?: BotSetting::get('fonnte_token', env('FONNTE_TOKEN'));

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum diatur.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post('https://api.fonnte.com/device');

            $json = $response->json();

            if ($response->successful() && ($json['status'] ?? false)) {
                return [
                    'success' => true,
                    'device' => $json['device'] ?? '-',
                    'name' => $json['name'] ?? 'Akun WA',
                    'status' => $json['device_status'] ?? 'connected',
                    'quota' => $json['quota'] ?? '-',
                    'raw' => $json,
                ];
            }

            return [
                'success' => false,
                'message' => $json['reason'] ?? ($json['detail'] ?? 'Device belum terhubung atau token salah.'),
                'raw' => $json,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi Fonnte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ambil daftar grup WhatsApp yang diikuti oleh nomor bot
     */
    public static function getGroups(?string $token = null): array
    {
        $token = $token ?: BotSetting::get('fonnte_token', env('FONNTE_TOKEN'));

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum diatur.',
                'groups' => [],
            ];
        }

        try {
            // Sinkronkan daftar grup terbaru dari WhatsApp
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(12)->post('https://api.fonnte.com/fetch-group');

            // Ambil data grup yang tersinkron
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(12)->post('https://api.fonnte.com/get-whatsapp-group');

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && $json['status'] == true && !empty($json['data'])) {
                return [
                    'success' => true,
                    'groups' => $json['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $json['reason'] ?? ($json['detail'] ?? 'Belum ada grup yang diikuti oleh nomor WA ini.'),
                'groups' => [],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil grup dari Fonnte: ' . $e->getMessage(),
                'groups' => [],
            ];
        }
    }

    /**
     * Ambil daftar nomor telepon member dari suatu grup WhatsApp
     */
    public static function getGroupMembers(string $groupId, ?string $token = null): array
    {
        $token = $token ?: BotSetting::get('fonnte_token', env('FONNTE_TOKEN'));
        if (empty($token) || !str_contains($groupId, '@g.us')) {
            return [];
        }

        try {
            return cache()->remember("fonnte_members_{$groupId}", 300, function () use ($groupId, $token) {
                $response = Http::withHeaders([
                    'Authorization' => $token,
                ])->timeout(10)->post('https://api.fonnte.com/get-whatsapp-group');

                $json = $response->json();
                $foundGroup = null;

                if (!empty($json['data'])) {
                    foreach ($json['data'] as $g) {
                        if (($g['id'] ?? '') === $groupId) {
                            $foundGroup = $g;
                            break;
                        }
                    }
                }

                // Jika belum ketemu di list, fetch ulang dari server Fonnte
                if (!$foundGroup) {
                    Http::withHeaders(['Authorization' => $token])->timeout(10)->post('https://api.fonnte.com/fetch-group');
                    $retry = Http::withHeaders(['Authorization' => $token])->timeout(10)->post('https://api.fonnte.com/get-whatsapp-group');
                    $retryJson = $retry->json();
                    foreach ($retryJson['data'] ?? [] as $g) {
                        if (($g['id'] ?? '') === $groupId) {
                            $foundGroup = $g;
                            break;
                        }
                    }
                }

                if ($foundGroup && !empty($foundGroup['member'])) {
                    $raw = is_array($foundGroup['member']) ? $foundGroup['member'] : json_decode($foundGroup['member'], true);
                    if (is_array($raw)) {
                        $deviceInfo = self::checkDeviceStatus($token);
                        $botNum = $deviceInfo['device'] ?? '';

                        return array_values(array_filter($raw, function ($m) use ($botNum) {
                            return !empty($m) && $m !== $botNum;
                        }));
                    }
                }

                return [];
            });
        } catch (\Exception $e) {
            Log::warning('Fonnte getGroupMembers error: ' . $e->getMessage());
            return [];
        }
    }
}


