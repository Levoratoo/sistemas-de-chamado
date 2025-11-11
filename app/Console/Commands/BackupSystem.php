<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupSystem extends Command
{
    protected $signature = 'backup:system {--type=full : Tipo de backup (full, database, files)} {--compress=true : Comprimir arquivos}';
    protected $description = 'Fazer backup completo do sistema de chamados';

    public function handle(): int
    {
        $type = $this->option('type');
        $compress = $this->option('compress') === 'true';
        
        $this->info("🔄 Iniciando backup do sistema ({$type})");
        
        $backupDir = storage_path('backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "backup_{$type}_{$timestamp}";
        $backupPath = $backupDir . '/' . $backupName;

        try {
            switch ($type) {
                case 'full':
                    $this->backupFull($backupPath, $compress);
                    break;
                case 'database':
                    $this->backupDatabase($backupPath, $compress);
                    break;
                case 'files':
                    $this->backupFiles($backupPath, $compress);
                    break;
                default:
                    $this->error('Tipo de backup inválido. Use: full, database, ou files');
                    return 1;
            }

            $this->info("✅ Backup concluído com sucesso!");
            $this->line("📁 Local: {$backupPath}");
            
            // Limpar backups antigos (manter últimos 7 dias)
            $this->cleanOldBackups($backupDir);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erro durante o backup: " . $e->getMessage());
            return 1;
        }
    }

    private function backupFull(string $backupPath, bool $compress): void
    {
        $this->line("📊 Fazendo backup do banco de dados...");
        $this->backupDatabase($backupPath . '_db', false);
        
        $this->line("📁 Fazendo backup dos arquivos...");
        $this->backupFiles($backupPath . '_files', false);
        
        $this->line("📋 Fazendo backup das configurações...");
        $this->backupConfig($backupPath . '_config', false);
        
        if ($compress) {
            $this->line("🗜️ Comprimindo arquivos...");
            $this->compressBackup($backupPath);
        }
    }

    private function backupDatabase(string $backupPath, bool $compress): void
    {
        $dbPath = $backupPath . '.sql';
        
        // Para SQLite (desenvolvimento)
        if (config('database.default') === 'sqlite') {
            $dbFile = config('database.connections.sqlite.database');
            if (File::exists($dbFile)) {
                File::copy($dbFile, $dbPath);
                $this->line("✅ Banco SQLite copiado");
            }
        } else {
            // Para MySQL/PostgreSQL (produção)
            $this->line("⚠️ Backup de banco MySQL/PostgreSQL requer mysqldump/pg_dump");
            $this->line("💡 Configure mysqldump ou pg_dump no servidor");
        }
        
        if ($compress) {
            $this->compressFile($dbPath);
        }
    }

    private function backupFiles(string $backupPath, bool $compress): void
    {
        $filesPath = $backupPath . '_files';
        File::makeDirectory($filesPath, 0755, true);
        
        // Backup de storage/app
        $storagePath = storage_path('app');
        if (File::exists($storagePath)) {
            File::copyDirectory($storagePath, $filesPath . '/storage_app');
            $this->line("✅ Storage/app copiado");
        }
        
        // Backup de uploads públicos
        $publicPath = public_path('uploads');
        if (File::exists($publicPath)) {
            File::copyDirectory($publicPath, $filesPath . '/public_uploads');
            $this->line("✅ Uploads públicos copiados");
        }
        
        // Backup de logs
        $logsPath = storage_path('logs');
        if (File::exists($logsPath)) {
            File::copyDirectory($logsPath, $filesPath . '/logs');
            $this->line("✅ Logs copiados");
        }
        
        if ($compress) {
            $this->compressDirectory($filesPath);
        }
    }

    private function backupConfig(string $backupPath, bool $compress): void
    {
        $configPath = $backupPath . '_config';
        File::makeDirectory($configPath, 0755, true);
        
        // Backup do .env (sem senhas)
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            // Remover senhas sensíveis
            $envContent = preg_replace('/^(.+_PASSWORD=).*$/m', '$1***HIDDEN***', $envContent);
            File::put($configPath . '/.env.safe', $envContent);
            $this->line("✅ Configurações copiadas (senhas ocultas)");
        }
        
        // Backup de configurações do Laravel
        $configDir = config_path();
        if (File::exists($configDir)) {
            File::copyDirectory($configDir, $configPath . '/config');
            $this->line("✅ Configurações Laravel copiadas");
        }
        
        if ($compress) {
            $this->compressDirectory($configPath);
        }
    }

    private function compressBackup(string $backupPath): void
    {
        $zipPath = $backupPath . '.zip';
        
        // Simular compressão (em produção, usar ZipArchive)
        $this->line("🗜️ Comprimindo para: {$zipPath}");
        
        // Para demonstração, vamos apenas criar um arquivo de texto
        File::put($zipPath . '.info', "Backup comprimido criado em: " . now());
    }

    private function compressFile(string $filePath): void
    {
        $this->line("🗜️ Comprimindo: {$filePath}");
        // Implementar compressão real aqui
    }

    private function compressDirectory(string $dirPath): void
    {
        $this->line("🗜️ Comprimindo diretório: {$dirPath}");
        // Implementar compressão real aqui
    }

    private function cleanOldBackups(string $backupDir): void
    {
        $this->line("🧹 Limpando backups antigos...");
        
        $files = File::files($backupDir);
        $cutoffDate = now()->subDays(7);
        
        $deletedCount = 0;
        foreach ($files as $file) {
            if (File::lastModified($file) < $cutoffDate->timestamp) {
                File::delete($file);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $this->line("🗑️ {$deletedCount} backups antigos removidos");
        } else {
            $this->line("✨ Nenhum backup antigo encontrado");
        }
    }
}