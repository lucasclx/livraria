<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Livro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SystemCheckCommand extends Command
{
    protected $signature = 'app:system-check';
    protected $description = 'Verifica a integridade do sistema da livraria';

    public function handle()
    {
        $this->info('🔍 Verificando integridade do sistema...');
        $this->newLine();

        $issues = [];

        // 1. Verificar usuários admin
        $issues = array_merge($issues, $this->checkAdminUsers());

        // 2. Verificar estrutura do banco
        $issues = array_merge($issues, $this->checkDatabaseStructure());

        // 3. Verificar dados
        $issues = array_merge($issues, $this->checkDataIntegrity());

        // 4. Verificar arquivos e permissões
        $issues = array_merge($issues, $this->checkFilesAndPermissions());

        // 5. Verificar configurações
        $issues = array_merge($issues, $this->checkConfigurations());

        // Resumo
        $this->newLine();
        if (empty($issues)) {
            $this->info('✅ Sistema verificado! Nenhum problema encontrado.');
        } else {
            $this->error('❌ Problemas encontrados:');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
            $this->newLine();
            $this->info('💡 Execute: php artisan app:fix-data para corrigir alguns problemas automaticamente.');
        }

        return empty($issues) ? 0 : 1;
    }

    private function checkAdminUsers()
    {
        $this->info('👤 Verificando usuários administradores...');
        $issues = [];

        $adminCount = User::where('is_admin', true)->count();
        
        if ($adminCount === 0) {
            $issues[] = 'Nenhum usuário administrador encontrado';
            $this->warn('  ⚠️  Nenhum usuário admin encontrado!');
            $this->line('     Execute: php artisan db:seed --class=AdminUserSeeder');
        } else {
            $this->info("  ✓ {$adminCount} usuário(s) admin encontrado(s)");
        }

        return $issues;
    }

    private function checkDatabaseStructure()
    {
        $this->info('🗄️  Verificando estrutura do banco...');
        $issues = [];

        $tables = ['users', 'categorias', 'livros', 'carts', 'cart_items', 'orders'];
        
        foreach ($tables as $table) {
            if (!$this->tableExists($table)) {
                $issues[] = "Tabela '{$table}' não existe";
                $this->error("  ✗ Tabela '{$table}' não encontrada");
            } else {
                $this->info("  ✓ Tabela '{$table}' existe");
            }
        }

        // Verificar colunas importantes
        if ($this->tableExists('categorias')) {
            if (!$this->columnExists('categorias', 'slug')) {
                $issues[] = "Coluna 'slug' não existe na tabela 'categorias'";
            }
        }

        if ($this->tableExists('livros')) {
            if (!$this->columnExists('livros', 'categoria_id')) {
                $issues[] = "Coluna 'categoria_id' não existe na tabela 'livros'";
            }
        }

        return $issues;
    }

    private function checkDataIntegrity()
    {
        $this->info('📊 Verificando integridade dos dados...');
        $issues = [];

        // Verificar categorias sem slug
        $categoriasSemdSlug = Categoria::whereNull('slug')->orWhere('slug', '')->count();
        if ($categoriasSemdSlug > 0) {
            $issues[] = "{$categoriasSemdSlug} categoria(s) sem slug definido";
            $this->warn("  ⚠️  {$categoriasSemdSlug} categoria(s) sem slug");
        }

        // Verificar livros sem categoria
        $livrosSemCategoria = Livro::whereNull('categoria_id')->count();
        if ($livrosSemCategoria > 0) {
            $issues[] = "{$livrosSemCategoria} livro(s) sem categoria definida";
            $this->warn("  ⚠️  {$livrosSemCategoria} livro(s) sem categoria_id");
        }

        // Verificar livros com dados incompletos
        $livrosComProblemas = Livro::where(function($query) {
            $query->whereNull('estoque_minimo')
                  ->orWhereNull('peso')
                  ->orWhereNull('idioma')
                  ->orWhere('idioma', '');
        })->count();

        if ($livrosComProblemas > 0) {
            $issues[] = "{$livrosComProblemas} livro(s) com dados incompletos";
            $this->warn("  ⚠️  {$livrosComProblemas} livro(s) com dados incompletos");
        }

        // Verificar órfãos
        $livrosOrfaos = Livro::whereNotNull('categoria_id')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('categorias')
                      ->whereRaw('categorias.id = livros.categoria_id');
            })->count();

        if ($livrosOrfaos > 0) {
            $issues[] = "{$livrosOrfaos} livro(s) com categoria_id inválido";
            $this->error("  ✗ {$livrosOrfaos} livro(s) órfãos encontrados");
        }

        if (empty($issues)) {
            $this->info('  ✓ Integridade dos dados OK');
        }

        return $issues;
    }

    private function checkFilesAndPermissions()
    {
        $this->info('📁 Verificando arquivos e permissões...');
        $issues = [];

        // Verificar storage link
        if (!file_exists(public_path('storage'))) {
            $issues[] = 'Storage link não existe';
            $this->warn('  ⚠️  Storage link não encontrado');
            $this->line('     Execute: php artisan storage:link');
        } else {
            $this->info('  ✓ Storage link existe');
        }

        // Verificar pastas de upload
        $uploadFolders = ['livros', 'categorias'];
        foreach ($uploadFolders as $folder) {
            $path = storage_path("app/public/{$folder}");
            if (!file_exists($path)) {
                $issues[] = "Pasta de upload '{$folder}' não existe";
                $this->warn("  ⚠️  Pasta '{$folder}' não encontrada");
            } else {
                $this->info("  ✓ Pasta '{$folder}' existe");
            }
        }

        // Verificar permissões de escrita
        $writablePaths = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($writablePaths as $path) {
            if (!is_writable($path)) {
                $issues[] = "Pasta não possui permissão de escrita: {$path}";
                $this->error("  ✗ Sem permissão de escrita: {$path}");
            }
        }

        return $issues;
    }

    private function checkConfigurations()
    {
        $this->info('⚙️  Verificando configurações...');
        $issues = [];

        // Verificar APP_KEY
        if (empty(config('app.key'))) {
            $issues[] = 'APP_KEY não definida';
            $this->error('  ✗ APP_KEY não configurada');
            $this->line('     Execute: php artisan key:generate');
        } else {
            $this->info('  ✓ APP_KEY configurada');
        }

        // Verificar configuração do banco
        try {
            DB::connection()->getPdo();
            $this->info('  ✓ Conexão com banco de dados OK');
        } catch (\Exception $e) {
            $issues[] = 'Erro na conexão com banco de dados';
            $this->error('  ✗ Erro na conexão com banco: ' . $e->getMessage());
        }

        // Verificar se as migrations foram executadas
        try {
            $pendingMigrations = $this->getPendingMigrations();
            if (count($pendingMigrations) > 0) {
                $issues[] = count($pendingMigrations) . ' migration(s) pendente(s)';
                $this->warn('  ⚠️  ' . count($pendingMigrations) . ' migration(s) pendente(s)');
                $this->line('     Execute: php artisan migrate');
            } else {
                $this->info('  ✓ Todas as migrations executadas');
            }
        } catch (\Exception $e) {
            $issues[] = 'Erro ao verificar migrations';
            $this->error('  ✗ Erro ao verificar migrations: ' . $e->getMessage());
        }

        return $issues;
    }

    private function tableExists($table)
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function columnExists($table, $column)
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getPendingMigrations()
    {
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();
            
            return array_diff(array_keys($files), $ran);
        } catch (\Exception $e) {
            return [];
        }
    }
}