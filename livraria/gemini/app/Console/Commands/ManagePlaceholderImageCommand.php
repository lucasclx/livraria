<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ManagePlaceholderImageCommand extends Command
{
    protected $signature = 'livro:placeholder 
                           {action : set, check, or remove}
                           {--image= : Path to the image file}
                           {--location=storage : Where to store (storage or public)}';

    protected $description = 'Gerencia a imagem padrão para livros sem capa';

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'set':
                return $this->setPlaceholder();
            case 'check':
                return $this->checkPlaceholder();
            case 'remove':
                return $this->removePlaceholder();
            default:
                $this->error('Ação inválida. Use: set, check ou remove');
                $this->showHelp();
                return 1;
        }
    }

    private function setPlaceholder()
    {
        $imagePath = $this->option('image');
        $location = $this->option('location');

        if (!$imagePath) {
            $imagePath = $this->ask('Caminho para a imagem:');
        }

        // Corrigir o caminho se começar com --
        if (str_starts_with($imagePath, '--')) {
            $imagePath = substr($imagePath, 2);
        }

        // Se for caminho relativo, converter para absoluto
        if (!str_starts_with($imagePath, '/')) {
            $imagePath = base_path($imagePath);
        }

        if (!file_exists($imagePath)) {
            $this->error("Arquivo não encontrado: {$imagePath}");
            return 1;
        }

        // Verificar se é uma imagem válida
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            $this->error('O arquivo não é uma imagem válida.');
            return 1;
        }

        $filename = 'capa-padrao.' . pathinfo($imagePath, PATHINFO_EXTENSION);

        if ($location === 'storage') {
            // Salvar em storage/app/public/defaults/
            $destinationPath = 'defaults/' . $filename;
            
            // Criar pasta se não existir
            if (!Storage::disk('public')->exists('defaults')) {
                Storage::disk('public')->makeDirectory('defaults');
                $this->info('📁 Pasta "defaults" criada em storage/app/public/');
            }
            
            // Copiar arquivo
            if (Storage::disk('public')->put($destinationPath, file_get_contents($imagePath))) {
                $this->info("✅ Imagem padrão definida!");
                $this->line("📁 Local: storage/app/public/{$destinationPath}");
                $this->line("🔗 URL: " . url('storage/' . $destinationPath));
            } else {
                $this->error('Erro ao salvar a imagem.');
                return 1;
            }
        } else {
            // Salvar em public/images/
            $destinationDir = public_path('images');
            $destinationPath = $destinationDir . '/' . $filename;
            
            // Criar pasta se não existir
            if (!File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
                $this->info('📁 Pasta "images" criada em public/');
            }
            
            // Copiar arquivo
            if (File::copy($imagePath, $destinationPath)) {
                $this->info("✅ Imagem padrão definida!");
                $this->line("📁 Local: public/images/{$filename}");
                $this->line("🔗 URL: " . asset('images/' . $filename));
            } else {
                $this->error('Erro ao salvar a imagem.');
                return 1;
            }
        }

        // Mostrar informações da imagem
        $this->newLine();
        $this->info("📊 Informações da imagem:");
        $this->line("   Dimensões: {$imageInfo[0]}x{$imageInfo[1]} pixels");
        $this->line("   Tipo: " . image_type_to_mime_type($imageInfo[2]));
        $this->line("   Tamanho: " . $this->formatBytes(filesize($imagePath)));

        return 0;
    }

    private function checkPlaceholder()
    {
        $this->info('🔍 Verificando imagens padrão disponíveis:');
        $this->newLine();

        $found = false;

        // Verificar public/images/
        $publicFiles = ['capa-padrao.jpg', 'capa-padrao.jpeg', 'capa-padrao.png', 'capa-padrao.webp'];
        foreach ($publicFiles as $filename) {
            $publicPath = "images/{$filename}";
            if (file_exists(public_path($publicPath))) {
                $this->info("✅ Encontrada em: public/{$publicPath}");
                $this->line("   URL: " . asset($publicPath));
                $size = filesize(public_path($publicPath));
                $this->line("   Tamanho: " . $this->formatBytes($size));
                $found = true;
            }
        }

        // Verificar storage/app/public/defaults/
        foreach ($publicFiles as $filename) {
            $storagePath = "defaults/{$filename}";
            if (Storage::disk('public')->exists($storagePath)) {
                $this->info("✅ Encontrada em: storage/app/public/{$storagePath}");
                $this->line("   URL: " . url('storage/' . $storagePath));
                $size = Storage::disk('public')->size($storagePath);
                $this->line("   Tamanho: " . $this->formatBytes($size));
                $found = true;
            }
        }

        // Verificar storage/app/public/livros/
        foreach (['placeholder.jpg', 'placeholder.png', 'placeholder.jpeg'] as $filename) {
            $booksPath = "livros/{$filename}";
            if (Storage::disk('public')->exists($booksPath)) {
                $this->info("✅ Encontrada em: storage/app/public/{$booksPath}");
                $this->line("   URL: " . url('storage/' . $booksPath));
                $size = Storage::disk('public')->size($booksPath);
                $this->line("   Tamanho: " . $this->formatBytes($size));
                $found = true;
            }
        }

        if (!$found) {
            $this->warn('⚠️  Nenhuma imagem padrão encontrada.');
            $this->info('💡 Use: php artisan livro:placeholder set --image=/caminho/para/imagem.jpg');
        }

        return 0;
    }

    private function removePlaceholder()
    {
        if (!$this->confirm('⚠️  Deseja realmente remover todas as imagens padrão?')) {
            $this->info('Operação cancelada.');
            return 0;
        }

        $removed = false;

        // Remover de public/images/
        $patterns = ['capa-padrao.*', 'placeholder.*'];
        foreach ($patterns as $pattern) {
            $files = glob(public_path("images/{$pattern}"));
            foreach ($files as $file) {
                if (unlink($file)) {
                    $this->info("🗑️  Removido: " . basename($file) . " (public/images)");
                    $removed = true;
                }
            }
        }

        // Remover de storage
        $storagePaths = ['defaults/', 'livros/'];
        foreach ($storagePaths as $path) {
            $files = Storage::disk('public')->files($path);
            foreach ($files as $file) {
                $basename = basename($file);
                if (str_contains($basename, 'capa-padrao') || str_contains($basename, 'placeholder')) {
                    if (Storage::disk('public')->delete($file)) {
                        $this->info("🗑️  Removido: {$file} (storage)");
                        $removed = true;
                    }
                }
            }
        }

        if (!$removed) {
            $this->warn('⚠️  Nenhuma imagem padrão encontrada para remover.');
        }

        return 0;
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    private function showHelp()
    {
        $this->newLine();
        $this->line('<comment>📖 Como usar:</comment>');
        $this->line('  php artisan livro:placeholder set --image=public/images/milpag.jpeg');
        $this->line('  php artisan livro:placeholder set --image=/caminho/absoluto/imagem.jpg --location=storage');
        $this->line('  php artisan livro:placeholder check');
        $this->line('  php artisan livro:placeholder remove');
        $this->newLine();
        $this->line('<comment>🗂️  Locais onde a imagem será salva:</comment>');
        $this->line('  --location=public  → public/images/capa-padrao.extensao');
        $this->line('  --location=storage → storage/app/public/defaults/capa-padrao.extensao');
    }
}