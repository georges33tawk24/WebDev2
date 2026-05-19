<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ExportTeamDatabaseCommand extends Command
{
    protected $signature = 'db:export-team
                            {--path= : Output path (default: database/dumps/team.sql)}';

    protected $description = 'Export the current database to a file teammates can import (commit database/dumps/team.sql)';

    public function handle(): int
    {
        $connection = config('database.default');
        $path = $this->option('path') ?? database_path('dumps/team.sql');

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($connection === 'mysql') {
            return $this->exportMysql($path);
        }

        if ($connection === 'sqlite') {
            return $this->exportSqlite($path);
        }

        $this->components->error('db:export-team supports mysql and sqlite only (current: '.$connection.').');

        return self::FAILURE;
    }

    private function exportMysql(string $path): int
    {
        $host = (string) config('database.connections.mysql.host');
        $port = (string) config('database.connections.mysql.port');
        $database = (string) config('database.connections.mysql.database');
        $username = (string) config('database.connections.mysql.username');
        $password = (string) config('database.connections.mysql.password');

        $args = [
            'mysqldump',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            $database,
        ];

        $process = new Process($args, null, $password !== '' ? ['MYSQL_PWD' => $password] : null);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->error('mysqldump failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        file_put_contents($path, $process->getOutput());
        $this->components->info('Exported MySQL `'.$database.'` → '.$path);
        $this->line('Commit this file so teammates can run: php artisan db:import-team');

        return self::SUCCESS;
    }

    private function exportSqlite(string $path): int
    {
        $sqlitePath = (string) config('database.connections.sqlite.database');

        if (! file_exists($sqlitePath)) {
            $this->components->error('SQLite file not found: '.$sqlitePath);

            return self::FAILURE;
        }

        $teamSqlite = database_path('team.sqlite');
        copy($sqlitePath, $teamSqlite);

        $process = new Process(['sqlite3', $sqlitePath, '.dump']);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->error('sqlite3 dump failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        file_put_contents($path, $process->getOutput());
        $this->components->info('Exported SQLite → '.$path.' and copied to database/team.sqlite');
        $this->line('Commit database/dumps/team.sql and/or database/team.sqlite for teammates.');

        return self::SUCCESS;
    }
}
