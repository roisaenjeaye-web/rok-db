<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\GameAccount;
use App\Models\Character;
use App\Models\Kingdom;
use App\Models\KdFarmerStat;

class InitialRokDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users
        $users = array (
  0 => 
  array (
    'id' => 1,
    'name' => 'Rois',
    'email' => 'roisalif20@gmail.com',
    'email_verified_at' => NULL,
    'password' => '$2y$12$DNZRmBEx1ppFYFC/K6WCWOxORDvbgotahDpTplsEyx5k1fiyj4/z6',
    'created_at' => '2026-10-27T09:45:08.000000Z',
    'updated_at' => '2026-10-27T15:01:00.000000Z',
  ),
);
        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        // 2. Seed Kingdoms
        $kingdoms = array (
  0 => 
  array (
    'id' => 2,
    'kd_number' => 2359,
    'food' => '80.00',
    'wood' => '40.00',
    'stone' => '0.00',
    'gold' => '0.00',
    'created_at' => '2026-10-27T15:16:51.000000Z',
    'updated_at' => '2026-10-27T15:35:14.000000Z',
  ),
  1 => 
  array (
    'id' => 4,
    'kd_number' => 3868,
    'food' => '0.00',
    'wood' => '0.00',
    'stone' => '0.00',
    'gold' => '0.00',
    'created_at' => '2026-10-27T15:50:29.000000Z',
    'updated_at' => '2026-10-27T15:50:29.000000Z',
  ),
);
        foreach ($kingdoms as $k) {
            Kingdom::updateOrCreate(['kd_number' => $k['kd_number']], $k);
        }

        // 3. Seed GameAccounts
        $accounts = array (
  0 => 
  array (
    'id' => 2,
    'email' => 'roisalif20@gmail.com',
    'password' => 'roisalif20',
    'notes' => 'lilith',
    'created_at' => '2026-10-27T10:35:20.000000Z',
    'updated_at' => '2026-10-27T10:35:20.000000Z',
  ),
  1 => 
  array (
    'id' => 3,
    'email' => 'roisalif13@gmail.com',
    'password' => 'rois2018',
    'notes' => NULL,
    'created_at' => '2026-10-27T14:12:19.000000Z',
    'updated_at' => '2026-10-27T14:12:19.000000Z',
  ),
);
        foreach ($accounts as $a) {
            GameAccount::updateOrCreate(['email' => $a['email']], $a);
        }

        // 4. Seed Characters
        $characters = array (
  0 => 
  array (
    'id' => 2,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T10:35:41.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 4,
    'farmer_stats' => 
    array (
      'Rois' => 4,
    ),
    'last_farmed_by' => 'Rois',
  ),
  1 => 
  array (
    'id' => 3,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T10:35:41.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  2 => 
  array (
    'id' => 4,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  3 => 
  array (
    'id' => 5,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  4 => 
  array (
    'id' => 6,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  5 => 
  array (
    'id' => 7,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  6 => 
  array (
    'id' => 8,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  7 => 
  array (
    'id' => 9,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  8 => 
  array (
    'id' => 10,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  9 => 
  array (
    'id' => 11,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  10 => 
  array (
    'id' => 12,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  11 => 
  array (
    'id' => 13,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  12 => 
  array (
    'id' => 14,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  13 => 
  array (
    'id' => 15,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  14 => 
  array (
    'id' => 16,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  15 => 
  array (
    'id' => 17,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  16 => 
  array (
    'id' => 18,
    'game_account_id' => 2,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:11:34.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  17 => 
  array (
    'id' => 19,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  18 => 
  array (
    'id' => 20,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  19 => 
  array (
    'id' => 21,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  20 => 
  array (
    'id' => 22,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  21 => 
  array (
    'id' => 23,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  22 => 
  array (
    'id' => 24,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  23 => 
  array (
    'id' => 25,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  24 => 
  array (
    'id' => 26,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  25 => 
  array (
    'id' => 27,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  26 => 
  array (
    'id' => 28,
    'game_account_id' => 3,
    'kd_number' => 2359,
    'ign' => 'Farm KD 2359',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:12:39.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'farm_count' => 3,
    'farmer_stats' => 
    array (
      'Rois' => 3,
    ),
    'last_farmed_by' => 'Rois',
  ),
  27 => 
  array (
    'id' => 29,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:35.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:35.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  28 => 
  array (
    'id' => 30,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:36.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:36.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  29 => 
  array (
    'id' => 31,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:36.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:36.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  30 => 
  array (
    'id' => 32,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:36.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:36.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  31 => 
  array (
    'id' => 33,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:36.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:36.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  32 => 
  array (
    'id' => 34,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:36.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:36.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  33 => 
  array (
    'id' => 35,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:36.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:36.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  34 => 
  array (
    'id' => 36,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  35 => 
  array (
    'id' => 37,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  36 => 
  array (
    'id' => 38,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  37 => 
  array (
    'id' => 39,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  38 => 
  array (
    'id' => 40,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  39 => 
  array (
    'id' => 41,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  40 => 
  array (
    'id' => 42,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  41 => 
  array (
    'id' => 43,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:37.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:37.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  42 => 
  array (
    'id' => 44,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:38.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:38.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  43 => 
  array (
    'id' => 45,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:38.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:38.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  44 => 
  array (
    'id' => 46,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:38.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:38.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  45 => 
  array (
    'id' => 47,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:38.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:38.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
  46 => 
  array (
    'id' => 48,
    'game_account_id' => 2,
    'kd_number' => 3868,
    'ign' => 'Farm KD 3868',
    'status' => 'aktif',
    'created_at' => '2026-10-27T14:18:49.000000Z',
    'updated_at' => '2026-10-27T14:24:38.000000Z',
    'last_farmed_at' => '2026-10-27T14:24:38.000000Z',
    'farm_count' => 1,
    'farmer_stats' => NULL,
    'last_farmed_by' => NULL,
  ),
);
        foreach ($characters as $c) {
            Character::updateOrCreate(['id' => $c['id']], $c);
        }

        // 5. Seed KdFarmerStats
        $stats = array (
  0 => 
  array (
    'id' => 1,
    'kd_number' => 2359,
    'worker_name' => 'Rois',
    'farm_count' => 102,
    'last_farmed_at' => '2026-10-27T16:08:40.000000Z',
    'created_at' => '2026-10-27T14:47:38.000000Z',
    'updated_at' => '2026-10-27T16:08:40.000000Z',
  ),
  1 => 
  array (
    'id' => 2,
    'kd_number' => 2359,
    'worker_name' => 'Therta',
    'farm_count' => 20,
    'last_farmed_at' => NULL,
    'created_at' => '2026-10-27T14:47:38.000000Z',
    'updated_at' => '2026-10-27T14:48:11.000000Z',
  ),
);
        foreach ($stats as $s) {
            KdFarmerStat::updateOrCreate(
                ['kd_number' => $s['kd_number'], 'worker_name' => $s['worker_name']],
                $s
            );
        }
    }
}
