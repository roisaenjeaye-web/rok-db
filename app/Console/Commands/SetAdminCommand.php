<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetAdminCommand extends Command
{
    protected $signature = 'user:set-admin {email} {--revoke : Cabut status admin}';
    protected $description = 'Set atau cabut status Admin untuk user berdasarkan email';

    public function handle(): void
    {
        $email = $this->argument('email');
        $revoke = $this->option('revoke');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan!");
            return;
        }

        $user->is_admin = !$revoke;
        $user->save();

        if ($revoke) {
            $this->warn("⚠️  Status Admin DICABUT dari user: {$user->name} ({$user->email})");
        } else {
            $this->info("✅ User {$user->name} ({$user->email}) sekarang adalah ADMIN!");
        }
    }
}
