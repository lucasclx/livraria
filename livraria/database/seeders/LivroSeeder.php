<?php
// arquivo: database/seeders/LivroSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Livro;

class LivroSeeder extends Seeder
{
    public function run()
    {
        // Verificar se já existem livros para evitar duplicatas
        if (Livro::count() > 0) {
            $this->command->info('Livros já existem no banco. Pulando seeder...');
            return;
        }

        $livros = [
            [
                'titulo' => 'Dom Casmurro',
                'autor' => 'Machado de Assis',
                'isbn' => '9788525406958',
                'editora' => 'Globo Livros',
                'ano_publicacao' => 1899,
                'preco' => 29.90,
                'paginas' => 256,
                'sinopse' => 'Romance clássico da literatura brasileira que narra a história de Bentinho e Capitu, explorando temas como ciúme, traição e a dúvida que corrói o coração humano.',
                'categoria' => 'Literatura Brasileira',
                'estoque' => 15,
                'ativo' => true,
            ],
            [
                'titulo' => 'O Cortiço',
                'autor' => 'Aluísio Azevedo',
                'isbn' => '9788520925478',
                'editora' => 'Scipione',
                'ano_publicacao' => 1890,
                'preco' => 24.90,
                'paginas' => 312,
                'sinopse' => 'Obra naturalista que retrata a vida em um cortiço no Rio de Janeiro do século XIX, mostrando as condições sociais da época.',
                'categoria' => 'Literatura Brasileira',
                'estoque' => 8,
                'ativo' => true,
            ],
            [
                'titulo' => 'Clean Code',
                'autor' => 'Robert C. Martin',
                'isbn' => '9780132350884',
                'editora' => 'Prentice Hall',
                'ano_publicacao' => 2008,
                'preco' => 89.90,
                'paginas' => 464,
                'sinopse' => 'Manual de desenvolvimento ágil de software com práticas para escrever código limpo, legível e manutenível.',
                'categoria' => 'Tecnologia',
                'estoque' => 25,
                'ativo' => true,
            ],
            [
                'titulo' => '1984',
                'autor' => 'George Orwell',
                'isbn' => '9788535914849',
                'editora' => 'Companhia das Letras',
                'ano_publicacao' => 1949,
                'preco' => 34.90,
                'paginas' => 416,
                'sinopse' => 'Distopia clássica sobre um regime totalitário que controla todos os aspectos da vida humana através da vigilância e manipulação.',
                'categoria' => 'Ficção Científica',
                'estoque' => 12,
                'ativo' => true,
            ],
            [
                'titulo' => 'O Pequeno Príncipe',
                'autor' => 'Antoine de Saint-Exupéry',
                'isbn' => '9788535909827',
                'editora' => 'Companhia das Letras',
                'ano_publicacao' => 1943,
                'preco' => 19.90,
                'paginas' => 96,
                'sinopse' => 'Fábula poética sobre amizade, amor e a natureza humana, contada através dos olhos de uma criança.',
                'categoria' => 'Literatura Infantil',
                'estoque' => 30,
                'ativo' => true,
            ],
            // Adicione mais livros conforme necessário...
        ];

        foreach ($livros as $livroData) {
            // Usar updateOrCreate para evitar duplicatas
            Livro::updateOrCreate(
                ['isbn' => $livroData['isbn']], // Buscar por ISBN
                $livroData // Dados para criar/atualizar
            );
        }

        $this->command->info('Livros inseridos com sucesso!');
    }
}