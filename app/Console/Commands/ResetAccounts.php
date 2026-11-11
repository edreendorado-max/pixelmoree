<?php

namespace App\Console\Commands;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ResetAccounts extends Command
{
    protected $signature = 'pixmoree:reset-accounts {--force : Skip the confirmation prompt}';

    protected $description = 'Remove local Pixmoree accounts and create the configured administrator';

    public function handle(): int
    {
        $sqliteDatabase = config('database.connections.sqlite.database');
        $sqlitePath = is_string($sqliteDatabase)
            ? (realpath($sqliteDatabase) ?: realpath(base_path($sqliteDatabase)))
            : false;
        $isLocalSqlite = config('app.env') === 'local'
            && config('database.default') === 'sqlite'
            && $sqlitePath === realpath(database_path('database.sqlite'))
            && ! config('database.connections.sqlite.url');

        $host = config('database.connections.mysql.host');
        $isLocalMysql = config('app.env') === 'local'
            && config('database.default') === 'mysql'
            && config('database.connections.mysql.database') === 'pixmoreedb'
            && in_array($host, ['127.0.0.1', 'localhost'], true)
            && (string) config('database.connections.mysql.port') === '3306'
            && ! config('database.connections.mysql.url');

        if (! $isLocalSqlite && ! $isLocalMysql) {
            $this->error('This command only runs in the local environment against the project SQLite database or the local pixmoreedb MySQL database.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('users')
            || ! Schema::hasTable('ownership_transfers')
            || ! Schema::hasColumn('users', 'username')) {
            $this->error('Run the database migrations before resetting accounts.');

            return self::FAILURE;
        }

        $admin = config('pixmoree.admin');
        $validator = Validator::make($admin, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'username' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/'],
            'password' => ['required', 'string', 'min:6', 'max:128', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ]);
        if ($validator->fails()) {
            $this->error('Set valid PIXMOREE_ADMIN_NAME, PIXMOREE_ADMIN_EMAIL, PIXMOREE_ADMIN_USERNAME, and PIXMOREE_ADMIN_PASSWORD values before running this command.');

            return self::FAILURE;
        }

        $accountCount = User::query()->count();
        if (! $this->option('force')
            && ! $this->confirm("Delete all {$accountCount} accounts and account-owned artwork, then create the configured administrator?", false)) {
            $this->info('No accounts were changed.');

            return self::SUCCESS;
        }

        DB::transaction(static function () use ($admin): void {
            DB::table('ownership_transfers')->delete();
            Artwork::query()
                ->whereIn('user_id', User::query()->select('id'))
                ->delete();
            User::query()->delete();

            User::forceCreate([
                'name' => $admin['name'],
                'email' => mb_strtolower(trim($admin['email'])),
                'username' => $admin['username'],
                'password' => $admin['password'],
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        });

        $this->info('All previous accounts and account-owned artwork were removed. The configured administrator is ready to sign in.');

        return self::SUCCESS;
    }
}
