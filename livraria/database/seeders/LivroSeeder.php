<?php
// database/seeders/LivroSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Livro;
use App\Models\Categoria;

class LivroSeeder extends Seeder
{
    public function run(): void
    {
        if (Livro::count() > 0) {
            $this->command->info('Livros já existem. Pulando...');
            return;
        }

        $livros = [
            [
                'titulo' => 'Dom Casmurro',
                'autor'  => 'Machado de Assis',
                'isbn'   => '9788525406958',
                'editora'=> 'Globo Livros',
                'ano_publicacao' => 1899,
                'preco'  => 29.90,
                'paginas'=> 256,
                'sinopse'=> 'Romance clássico da literatura brasileira...',
                'categoria_nome' => 'Literatura Brasileira',
                'estoque'=> 15,
            ],
            [
                'titulo' => 'O Cortiço',
                'autor'  => 'Aluísio Azevedo',
                'isbn'   => '9788520925478',
                'editora'=> 'Scipione',
                'ano_publicacao' => 1890,
                'preco'  => 24.90,
                'paginas'=> 312,
                'sinopse'=> 'Obra naturalista que retrata a vida em um cortiço...',
                'categoria_nome' => 'Literatura Brasileira',
                'estoque'=> 8,
            ],
            [
                'titulo' => 'Clean Code',
                'autor'  => 'Robert C. Martin',
                'isbn'   => '9780132350884',
                'editora'=> 'Prentice Hall',
                'ano_publicacao' => 2008,
                'preco'  => 89.90,
                'paginas'=> 464,
                'sinopse'=> 'Manual sobre práticas de código limpo.',
                'categoria_nome' => 'Tecnologia',
                'estoque'=> 25,
            ],
            // …
        ];

        foreach ($livros as $dados) {
            /* 1. Garante que a categoria exista e obtém o ID */
            $categoria = Categoria::firstOrCreate(
                ['nome' => $dados['categoria_nome']],
                ['slug' => Str::slug($dados['categoria_nome'])]
            );

            /* 2. Cria ou atualiza o livro usando categoria_id */
            Livro::updateOrCreate(
                ['isbn' => $dados['isbn']],
                [
                    'titulo'        => $dados['titulo'],
                    'autor'         => $dados['autor'],
                    'editora'       => $dados['editora'],
                    'ano_publicacao'=> $dados['ano_publicacao'],
                    'preco'         => $dados['preco'],
                    'paginas'       => $dados['paginas'],
                    'sinopse'       => $dados['sinopse'],
                    'categoria_id'  => $categoria->id,
                    'estoque'       => $dados['estoque'],
                    'ativo'         => true,
                ]
            );
        }

        $this->command->info('Livros inseridos/atualizados com sucesso!');
    }
}
