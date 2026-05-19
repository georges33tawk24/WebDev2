<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Symfony\Component\Process\Process;
use Throwable;

class ImportTeamDatabaseCommand extends Command
{
    protected $signature = 'db:import-team
                            {--path= : SQL dump path (default: database/dumps/team.sql)}
                            {--sqlite-file= : Use database/team.sqlite instead of SQL dump}';

    protected $description = 'Import the team database snapshot (same data as whoever ran db:export-team)';

    public function handle(): int
    {
        $connection = config('database.default');
        $sqliteSnapshot = $this->option('sqlite-file') ?? database_path('team.sqlite');

        if ($connection === 'sqlite' && file_exists($sqliteSnapshot) && $this->option('path') === null) {
            return $this->importSqliteFile($sqliteSnapshot);
        }

        $path = $this->option('path') ?? database_path('dumps/team.sql');

        if (! file_exists($path)) {
            $this->components->error('Team dump not found: '.$path);
            $this->line('Ask a teammate to run `php artisan db:export-team` and commit database/dumps/team.sql.');

            return self::FAILURE;
        }

        if ($connection === 'mysql') {
            return $this->importMysql($path);
        }

        if ($connection === 'sqlite') {
            return $this->importSqliteSql($path);
        }

        $this->components->error('db:import-team supports mysql and sqlite only (current: '.$connection.').');

        return self::FAILURE;
    }

    private function importMysql(string $path): int
    {
        $host = (string) config('database.connections.mysql.host');
        $port = (string) config('database.connections.mysql.port');
        $database = (string) config('database.connections.mysql.database');
        $username = (string) config('database.connections.mysql.username');
        $password = (string) config('database.connections.mysql.password');

        if (! $this->ensureMysqlDatabase($host, $port, $username, $password, $database)) {
            return self::FAILURE;
        }

        $args = [
            'mysql',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            $database,
        ];

        $process = Process::fromShellCommandline(
            implode(' ', array_map('escapeshellarg', $args)).' < '.escapeshellarg($path),
            null,
            $password !== '' ? ['MYSQL_PWD' => $password] : null,
        );
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->error('mysql import failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        $this->call('migrate', ['--force' => true]);
        $this->components->info('Imported team data from '.$path.' into MySQL `'.$database.'`.');

        return self::SUCCESS;
    }

    private function importSqliteSql(string $path): int
    {
        $target = (string) config('database.connections.sqlite.database');
        $directory = dirname($target);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($target)) {
            unlink($target);
        }

        touch($target);

        $process = Process::fromShellCommandline(
            'sqlite3 '.escapeshellarg($target).' < '.escapeshellarg($path),
        );
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->error('sqlite3 import failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        $this->call('migrate', ['--force' => true]);
        $this->components->info('Imported team data into '.$target);

        return self::SUCCESS;
    }

    private function importSqliteFile(string $source): int
    {
        if (! file_exists($source)) {
            $this->components->error('Team SQLite file not found: '.$source);

            return self::FAILURE;
        }

        $target = (string) config('database.connections.sqlite.database');
        $directory = dirname($target);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        copy($source, $target);
        $this->call('migrate', ['--force' => true]);
        $this->components->info('Copied team SQLite snapshot → '.$target);

        return self::SUCCESS;
    }

    private function ensureMysqlDatabase(
        string $host,
        string $port,
        string $username,
        string $password,
        string $database,
    ): bool {
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

            return true;
        } catch (Throwable $exception) {
            $this->components->error('Could not connect to MySQL: '.$exception->getMessage());

            return false;
        }
    }
}
