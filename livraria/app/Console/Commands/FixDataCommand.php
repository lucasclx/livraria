<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Categoria;
use App\Models\Livro;
use Illuminate\Support\Str;

class FixDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-data {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige dados inconsistentes no banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando correção de dados...');

        if (!$this->option('force') && !$this->confirm('Deseja continuar com a correção de dados?')) {
            $this->info('Operação cancelada.');
            return 0;
        }

        // 1. Corrigir slugs das categorias
        $this->fixCategorySlugs();

        // 2. Corrigir relacionamentos categoria_id
        $this->fixCategoryRelationships();

        // 3. Corrigir dados dos livros
        $this->fixBookData();

        // 4. Verificar e corrigir storage links
        $this->fixStorageLinks();

        $this->info('✅ Correção de dados concluída!');
        return 0;
    }

    private function fixCategorySlugs()
    {
        $this->info('📝 Corrigindo slugs das categorias...');

        $categorias = Categoria::whereNull('slug')->orWhere('slug', '')->get();
        
        foreach ($categorias as $categoria) {
            $slug = Str::slug($categoria->nome);
            $originalSlug = $slug;
            $counter = 1;

            // Garantir que o slug seja único
            while (Categoria::where('slug', $slug)->where('id', '!=', $categoria->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $categoria->update(['slug' => $slug]);
            $this->line("  ✓ Categoria '{$categoria->nome}' → slug: '{$slug}'");
        }

        $this->info("  Corrigidos {$categorias->count()} slugs de categorias.");
    }

    private function fixCategoryRelationships()
    {
        $this->info('🔗 Verificando relacionamentos categoria_id...');

        // Livros sem categoria_id mas com categoria (string)
        $livrosSemCategoria = Livro::whereNull('categoria_id')->count();
        
        if ($livrosSemCategoria > 0) {
            $this->warn("  ⚠️  Encontrados {$livrosSemCategoria} livros sem categoria_id definida.");
            $this->info("  Execute a migration 'fix_livros_categorias_relationship' se ainda não executou.");
        } else {
            $this->info("  ✓ Todos os livros possuem categoria_id definida.");
        }
    }

    private function fixBookData()
    {
        $this->info('📚 Corrigindo dados dos livros...');

        $livros = Livro::all();
        $fixed = 0;

        foreach ($livros as $livro) {
            $updates = [];

            // Corrigir estoque_minimo nulo
            if (is_null($livro->estoque_minimo)) {
                $updates['estoque_minimo'] = 5;
            }

            // Corrigir peso nulo
            if (is_null($livro->peso)) {
                $updates['peso'] = 0.5;
            }

            // Corrigir idioma nulo
            if (is_null($livro->idioma) || empty($livro->idioma)) {
                $updates['idioma'] = 'Português';
            }

            // Corrigir valores de avaliação nulos
            if (is_null($livro->avaliacao_media)) {
                $updates['avaliacao_media'] = 0;
            }
            if (is_null($livro->total_avaliacoes)) {
                $updates['total_avaliacoes'] = 0;
            }
            if (is_null($livro->vendas_total)) {
                $updates['vendas_total'] = 0;
            }

            if (!empty($updates)) {
                $livro->update($updates);
                $fixed++;
                $this->line("  ✓ Corrigido livro: {$livro->titulo}");
            }
        }

        $this->info("  Corrigidos {$fixed} livros.");
    }

    private function fixStorageLinks()
    {
        $this->info('🔗 Verificando storage links...');

        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

        if (!file_exists($publicPath)) {
            $this->warn("  ⚠️  Storage link não existe. Criando...");
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows
                $result = shell_exec("mklink /D \"{$publicPath}\" \"{$storagePath}\"");
            } else {
                // Unix/Linux/Mac
                $result = symlink($storagePath, $publicPath);
            }

            if ($result !== false) {
                $this->info("  ✓ Storage link criado com sucesso.");
            } else {
                $this->error("  ✗ Erro ao criar storage link. Execute manualmente: php artisan storage:link");
            }
        } else {
            $this->info("  ✓ Storage link já existe.");
        }

        // Verificar se as pastas necessárias existem
        $folders = ['livros', 'categorias'];
        foreach ($folders as $folder) {
            $folderPath = storage_path("app/public/{$folder}");
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
                $this->info("  ✓ Pasta '{$folder}' criada em storage/app/public/");
            }
        }
    }
}