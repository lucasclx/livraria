<?php
// database/migrations/2024_XX_XX_XXXXXX_convert_livros_to_produtos.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Verificar se a tabela livros existe e tem dados
        if (Schema::hasTable('livros')) {
            $livros = DB::table('livros')->get();
            
            if ($livros->count() > 0) {
                echo "Convertendo {$livros->count()} livros para produtos...\n";
                
                foreach ($livros as $livro) {
                    // Converter características do livro para JSON
                    $caracteristicas = [];
                    
                    if ($livro->autor) {
                        $caracteristicas['Autor'] = $livro->autor;
                    }
                    if ($livro->isbn) {
                        $caracteristicas['ISBN'] = $livro->isbn;
                    }
                    if ($livro->editora) {
                        $caracteristicas['Editora'] = $livro->editora;
                    }
                    if ($livro->ano_publicacao) {
                        $caracteristicas['Ano de Publicação'] = $livro->ano_publicacao;
                    }
                    if ($livro->paginas) {
                        $caracteristicas['Páginas'] = $livro->paginas . ' páginas';
                    }
                    
                    // Verificar se o produto já existe (evitar duplicatas)
                    $existingProduct = DB::table('produtos')
                        ->where('nome', $livro->titulo)
                        ->orWhere('sku', 'LIVRO-' . $livro->id)
                        ->first();
                    
                    if (!$existingProduct) {
                        // Inserir o livro como produto
                        DB::table('produtos')->insert([
                            'nome' => $livro->titulo,
                            'descricao' => $livro->sinopse,
                            'preco' => $livro->preco,
                            'estoque' => $livro->estoque ?? 0,
                            'categoria' => $livro->categoria ?? 'Livros',
                            'marca' => $livro->editora,
                            'sku' => 'LIVRO-' . $livro->id,
                            'caracteristicas' => json_encode($caracteristicas),
                            'imagem' => $livro->imagem,
                            'galeria_imagens' => null,
                            'peso' => 0.3, // Peso médio de um livro
                            'unidade_medida' => 'unidade',
                            'desconto_percentual' => 0,
                            'ativo' => $livro->ativo ?? true,
                            'destaque' => false,
                            'visualizacoes' => 0,
                            'avaliacao_media' => 0,
                            'total_vendas' => 0,
                            'data_lancamento' => $livro->created_at,
                            'created_at' => $livro->created_at,
                            'updated_at' => $livro->updated_at,
                        ]);
                        
                        echo "✓ Livro '{$livro->titulo}' convertido para produto\n";
                    } else {
                        echo "⚠ Produto '{$livro->titulo}' já existe, pulando...\n";
                    }
                }
                
                echo "✅ Conversão concluída!\n";
            }
        } else {
            echo "ℹ Tabela 'livros' não encontrada, pulando conversão.\n";
        }
    }

    public function down()
    {
        // Remove produtos que foram criados a partir de livros
        DB::table('produtos')->where('sku', 'like', 'LIVRO-%')->delete();
        echo "Produtos convertidos de livros foram removidos.\n";
    }
};