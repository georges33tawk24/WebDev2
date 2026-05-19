<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class PrepareDatabaseCommand extends Command
{
    protected $signature = 'db:prepare
                            {--seed : Load Lebanon demo data (users, offices, requests, …)}
                            {--fresh : Drop all tables before migrating}
                            {--import-team : Import database/dumps/team.sql (or database/team.sqlite) from git}';

    protected $description = 'Create the local database, run migrations, link storage, and optionally seed demo data for the team';

    public function handle(): int
    {
        $connection = config('database.default');

        $this->components->info('Preparing database ['.$connection.']…');

        if ($connection === 'sqlite') {
            $this->ensureSqliteFile();
        } elseif ($connection === 'mysql') {
            if (! $this->ensureMysqlDatabase()) {
                return self::FAILURE;
            }
        }

        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            $this->components->error('Database connection failed: '.$exception->getMessage());
            $this->line('Fix DB_* in `.env` — see `database/README.md`.');

            return self::FAILURE;
        }

        if ($this->option('import-team')) {
            if ($this->call('db:import-team') !== self::SUCCESS) {
                return self::FAILURE;
            }
        } else {
            if ($this->option('fresh')) {
                $this->call('migrate:fresh', ['--force' => true]);
            } else {
                $this->call('migrate', ['--force' => true]);
            }

            if ($this->option('seed')) {
                $this->call('db:seed', ['--force' => true]);
            }
        }

        if (! file_exists(public_path('storage'))) {
            $this->call('storage:link');
        }

        $this->newLine();
        $this->components->info($this->databaseLocationMessage($connection));
        $this->line('Forms and APIs persist rows via Eloquent — nothing is kept in memory only.');
        $this->line('Teammates: clone repo → `cp .env.example .env` → `composer setup` or `php artisan db:prepare --seed`.');

        return self::SUCCESS;
    }

    private function ensureSqliteFile(): void
    {
        $path = (string) config('database.connections.sqlite.database');

        if ($path === '' || $path === ':memory:') {
            return;
        }

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! file_exists($path)) {
            touch($path);
            $this->components->info('Created SQLite file: '.$path);
        }
    }

    private function ensureMysqlDatabase(): bool
    {
        $database = (string) config('database.connections.mysql.database');
        $host = (string) config('database.connections.mysql.host');
        $port = (string) config('database.connections.mysql.port');
        $username = (string) config('database.connections.mysql.username');
        $password = (string) config('database.connections.mysql.password');

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            $this->components->info("MySQL database `{$database}` is ready.");

            return true;
        } catch (Throwable $exception) {
            $this->components->error('Could not create MySQL database: '.$exception->getMessage());
            $this->line('Start MySQL, then create it manually:');
            $this->line("  CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            return false;
        }
    }

    private function databaseLocationMessage(string $connection): string
    {
        if ($connection === 'sqlite') {
            return 'Data file: '.config('database.connections.sqlite.database');
        }

        if ($connection === 'mysql') {
            $host = config('database.connections.mysql.host');
            $name = config('database.connections.mysql.database');

            return "Data store: MySQL `{$name}` on {$host}";
        }

        return 'Database ready ('.$connection.').';
    }
}
