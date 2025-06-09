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
        // Removi a verificação que impedia a execução se já existissem livros
        // if (Livro::count() > 0) {
        //     $this->command->info('Livros já existem. Pulando...');
        //     return;
        // }

        $this->command->info('Iniciando criação dos livros...');

        $livros = [
            // Literatura Brasileira
            [
                'titulo' => 'Dom Casmurro',
                'autor' => 'Machado de Assis',
                'isbn' => '9788525406958',
                'editora' => 'Globo Livros',
                'ano_publicacao' => 1899,
                'preco' => 29.90,
                'paginas' => 256,
                'sinopse' => 'Romance clássico da literatura brasileira que narra a história de Bentinho e sua paixão por Capitu, explorando temas como ciúme, traição e a complexidade das relações humanas.',
                'categoria_nome' => 'Ficção',
                'estoque' => 15,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'O Cortiço',
                'autor' => 'Aluísio Azevedo',
                'isbn' => '9788520925478',
                'editora' => 'Scipione',
                'ano_publicacao' => 1890,
                'preco' => 24.90,
                'paginas' => 312,
                'sinopse' => 'Obra naturalista que retrata a vida em um cortiço do Rio de Janeiro, mostrando as condições sociais e humanas da época.',
                'categoria_nome' => 'Ficção',
                'estoque' => 8,
                'ativo' => true,
                'destaque' => false,
            ],
            [
                'titulo' => 'Grande Sertão: Veredas',
                'autor' => 'João Guimarães Rosa',
                'isbn' => '9788535910663',
                'editora' => 'Companhia das Letras',
                'ano_publicacao' => 1956,
                'preco' => 45.90,
                'paginas' => 624,
                'sinopse' => 'Obra-prima da literatura brasileira que narra a saga de Riobaldo pelo sertão mineiro, explorando temas universais através da linguagem única de Guimarães Rosa.',
                'categoria_nome' => 'Ficção',
                'estoque' => 12,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'Quincas Borba',
                'autor' => 'Machado de Assis',
                'isbn' => '9788594318268',
                'editora' => 'Penguin Classics',
                'ano_publicacao' => 1891,
                'preco' => 32.90,
                'paginas' => 288,
                'sinopse' => 'Romance que acompanha a trajetória de Rubião, um professor que herda uma fortuna e se muda para o Rio de Janeiro, retratando a sociedade da época.',
                'categoria_nome' => 'Ficção',
                'estoque' => 6,
                'ativo' => true,
                'destaque' => false,
            ],

            // Tecnologia
            [
                'titulo' => 'Clean Code',
                'autor' => 'Robert C. Martin',
                'isbn' => '9780132350884',
                'editora' => 'Prentice Hall',
                'ano_publicacao' => 2008,
                'preco' => 89.90,
                'paginas' => 464,
                'sinopse' => 'Manual completo sobre práticas de código limpo, ensinando como escrever código legível, manutenível e profissional.',
                'categoria_nome' => 'Técnico',
                'estoque' => 25,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'Design Patterns',
                'autor' => 'Gang of Four',
                'isbn' => '9780201633610',
                'editora' => 'Addison-Wesley',
                'ano_publicacao' => 1994,
                'preco' => 95.50,
                'paginas' => 395,
                'sinopse' => 'Livro fundamental sobre padrões de design em programação orientada a objetos, essencial para desenvolvedores.',
                'categoria_nome' => 'Técnico',
                'estoque' => 18,
                'ativo' => true,
                'destaque' => false,
            ],
            [
                'titulo' => 'JavaScript: The Good Parts',
                'autor' => 'Douglas Crockford',
                'isbn' => '9780596517748',
                'editora' => "O'Reilly Media",
                'ano_publicacao' => 2008,
                'preco' => 67.90,
                'paginas' => 176,
                'sinopse' => 'Guia conciso sobre as melhores práticas em JavaScript, focando nas partes mais úteis e elegantes da linguagem.',
                'categoria_nome' => 'Técnico',
                'estoque' => 22,
                'ativo' => true,
                'destaque' => false,
            ],

            // Romance
            [
                'titulo' => 'Orgulho e Preconceito',
                'autor' => 'Jane Austen',
                'isbn' => '9788544001646',
                'editora' => 'Suma',
                'ano_publicacao' => 1813,
                'preco' => 39.90,
                'paginas' => 424,
                'sinopse' => 'Romance clássico que narra a história de Elizabeth Bennet e Mr. Darcy, explorando temas como amor, orgulho e preconceito na sociedade inglesa.',
                'categoria_nome' => 'Romance',
                'estoque' => 20,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'Jane Eyre',
                'autor' => 'Charlotte Brontë',
                'isbn' => '9788543105093',
                'editora' => 'Saraiva',
                'ano_publicacao' => 1847,
                'preco' => 42.50,
                'paginas' => 512,
                'sinopse' => 'Romance que acompanha a vida de Jane Eyre desde a infância até a idade adulta, retratando sua luta por independência e amor.',
                'categoria_nome' => 'Romance',
                'estoque' => 14,
                'ativo' => true,
                'destaque' => false,
            ],
            [
                'titulo' => 'Como Eu Era Antes de Você',
                'autor' => 'Jojo Moyes',
                'isbn' => '9788580578041',
                'editora' => 'Intrinseca',
                'ano_publicacao' => 2012,
                'preco' => 34.90,
                'paginas' => 368,
                'sinopse' => 'Romance contemporâneo que conta a história de Lou e Will, explorando temas como amor, vida e as escolhas que fazemos.',
                'categoria_nome' => 'Romance',
                'estoque' => 16,
                'ativo' => true,
                'destaque' => true,
            ],

            // Biografia
            [
                'titulo' => 'Steve Jobs',
                'autor' => 'Walter Isaacson',
                'isbn' => '9788535918588',
                'editora' => 'Companhia das Letras',
                'ano_publicacao' => 2011,
                'preco' => 54.90,
                'paginas' => 656,
                'sinopse' => 'Biografia autorizada do cofundador da Apple, baseada em mais de quarenta entrevistas com Jobs e centenas de entrevistas com familiares e colegas.',
                'categoria_nome' => 'Biografia',
                'estoque' => 11,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'Leonardo da Vinci',
                'autor' => 'Walter Isaacson',
                'isbn' => '9788542209457',
                'editora' => 'Intrínseca',
                'ano_publicacao' => 2017,
                'preco' => 49.90,
                'paginas' => 624,
                'sinopse' => 'Biografia que revela a vida e obra de Leonardo da Vinci, explorando sua genialidade e curiosidade insaciável.',
                'categoria_nome' => 'Biografia',
                'estoque' => 9,
                'ativo' => true,
                'destaque' => false,
            ],

            // Não-ficção
            [
                'titulo' => 'Sapiens',
                'autor' => 'Yuval Noah Harari',
                'isbn' => '9788525432186',
                'editora' => 'L&PM',
                'ano_publicacao' => 2014,
                'preco' => 44.90,
                'paginas' => 464,
                'sinopse' => 'Uma breve história da humanidade que explora como o Homo sapiens se tornou a espécie dominante do planeta.',
                'categoria_nome' => 'Não-ficção',
                'estoque' => 28,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'Homo Deus',
                'autor' => 'Yuval Noah Harari',
                'isbn' => '9788535929201',
                'editora' => 'Companhia das Letras',
                'ano_publicacao' => 2016,
                'preco' => 47.90,
                'paginas' => 448,
                'sinopse' => 'Uma breve história do amanhã que explora o futuro da humanidade na era da inteligência artificial e biotecnologia.',
                'categoria_nome' => 'Não-ficção',
                'estoque' => 23,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'O Poder do Hábito',
                'autor' => 'Charles Duhigg',
                'isbn' => '9788539004119',
                'editora' => 'Objetiva',
                'ano_publicacao' => 2012,
                'preco' => 39.90,
                'paginas' => 408,
                'sinopse' => 'Livro que explora a ciência por trás dos hábitos e como podemos transformá-los para melhorar nossas vidas.',
                'categoria_nome' => 'Não-ficção',
                'estoque' => 19,
                'ativo' => true,
                'destaque' => false,
            ],

            // Ficção Internacional
            [
                'titulo' => '1984',
                'autor' => 'George Orwell',
                'isbn' => '9788535914849',
                'editora' => 'Companhia das Letras',
                'ano_publicacao' => 1949,
                'preco' => 36.90,
                'paginas' => 416,
                'sinopse' => 'Distopia clássica que retrata uma sociedade totalitária onde o Grande Irmão observa tudo e controla a mente dos cidadãos.',
                'categoria_nome' => 'Ficção',
                'estoque' => 21,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'titulo' => 'O Apanhador no Campo de Centeio',
                'autor' => 'J.D. Salinger',
                'isbn' => '9788574062686',
                'editora' => 'Editora do Autor',
                'ano_publicacao' => 1951,
                'preco' => 33.90,
                'paginas' => 272,
                'sinopse' => 'Romance de formação que acompanha Holden Caulfield em sua jornada pelas ruas de Nova York após ser expulso da escola.',
                'categoria_nome' => 'Ficção',
                'estoque' => 13,
                'ativo' => true,
                'destaque' => false,
            ],
            [
                'titulo' => 'Cem Anos de Solidão',
                'autor' => 'Gabriel García Márquez',
                'isbn' => '9788501082145',
                'editora' => 'Record',
                'ano_publicacao' => 1967,
                'preco' => 41.90,
                'paginas' => 432,
                'sinopse' => 'Obra-prima do realismo mágico que narra a saga da família Buendía na cidade fictícia de Macondo.',
                'categoria_nome' => 'Ficção',
                'estoque' => 17,
                'ativo' => true,
                'destaque' => true,
            ],

            // Livros com promoção
            [
                'titulo' => 'O Pequeno Príncipe',
                'autor' => 'Antoine de Saint-Exupéry',
                'isbn' => '9788595081482',
                'editora' => 'HarperCollins',
                'ano_publicacao' => 1943,
                'preco' => 19.90,
                'preco_promocional' => 14.90,
                'paginas' => 96,
                'sinopse' => 'Fábula poética que conta a história de um pequeno príncipe que viaja de planeta em planeta, ensinando sobre amor e amizade.',
                'categoria_nome' => 'Ficção',
                'estoque' => 35,
                'ativo' => true,
                'destaque' => true,
                'promocao_inicio' => now()->subDays(5),
                'promocao_fim' => now()->addDays(25),
            ],
            [
                'titulo' => 'A Arte da Guerra',
                'autor' => 'Sun Tzu',
                'isbn' => '9788525432179',
                'editora' => 'L&PM',
                'ano_publicacao' => -500, // Aproximadamente 500 a.C.
                'preco' => 24.90,
                'preco_promocional' => 18.90,
                'paginas' => 160,
                'sinopse' => 'Tratado militar chinês que oferece estratégias e táticas aplicáveis não apenas à guerra, mas também aos negócios e à vida.',
                'categoria_nome' => 'Não-ficção',
                'estoque' => 27,
                'ativo' => true,
                'destaque' => false,
                'promocao_inicio' => now()->subDays(10),
                'promocao_fim' => now()->addDays(20),
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($livros as $dadosLivro) {
            try {
                // 1. Garantir que a categoria existe
                $categoria = Categoria::firstOrCreate(
                    ['nome' => $dadosLivro['categoria_nome']],
                    [
                        'slug' => Str::slug($dadosLivro['categoria_nome']),
                        'ativo' => true,
                        'descricao' => 'Categoria criada automaticamente pelo seeder'
                    ]
                );

                // 2. Preparar dados do livro
                $dadosParaCriar = [
                    'titulo' => $dadosLivro['titulo'],
                    'autor' => $dadosLivro['autor'],
                    'editora' => $dadosLivro['editora'] ?? null,
                    'ano_publicacao' => $dadosLivro['ano_publicacao'],
                    'preco' => $dadosLivro['preco'],
                    'preco_promocional' => $dadosLivro['preco_promocional'] ?? null,
                    'paginas' => $dadosLivro['paginas'] ?? null,
                    'sinopse' => $dadosLivro['sinopse'],
                    'categoria_id' => $categoria->id,
                    'estoque' => $dadosLivro['estoque'],
                    'estoque_minimo' => 5, // Valor padrão
                    'peso' => 0.5, // Valor padrão em kg
                    'idioma' => 'Português',
                    'ativo' => $dadosLivro['ativo'] ?? true,
                    'destaque' => $dadosLivro['destaque'] ?? false,
                    'promocao_inicio' => $dadosLivro['promocao_inicio'] ?? null,
                    'promocao_fim' => $dadosLivro['promocao_fim'] ?? null,
                ];

                // 3. Criar ou atualizar o livro usando ISBN como chave única
                $livro = Livro::updateOrCreate(
                    ['isbn' => $dadosLivro['isbn']],
                    $dadosParaCriar
                );

                if ($livro->wasRecentlyCreated) {
                    $created++;
                    $this->command->info("✓ Livro criado: {$livro->titulo}");
                } else {
                    $updated++;
                    $this->command->info("↻ Livro atualizado: {$livro->titulo}");
                }

            } catch (\Exception $e) {
                $this->command->error("✗ Erro ao processar livro '{$dadosLivro['titulo']}': " . $e->getMessage());
            }
        }

        $this->command->info("\n=== RESUMO ===");
        $this->command->info("Livros criados: {$created}");
        $this->command->info("Livros atualizados: {$updated}");
        $this->command->info("Total de livros no banco: " . Livro::count());
        $this->command->info("Total de categorias no banco: " . Categoria::count());
    }
}