<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    protected $signature = 'pixmoree:make-admin {email : Email address of an existing registered user}';

    protected $description = 'Grant admin access to an existing Pixmoree account';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No registered user was found with that email address.');

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info('This account already has admin access.');

            return self::SUCCESS;
        }

        $user->forceFill(['role' => 'admin'])->save();
        $this->info("Admin access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
