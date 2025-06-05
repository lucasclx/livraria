<?php
// database/seeders/ProdutoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;
use Carbon\Carbon;

class ProdutoSeeder extends Seeder
{
    public function run()
    {
        // Verificar se já existem produtos para evitar duplicatas
        if (Produto::count() > 0) {
            $this->command->info('Produtos já existem no banco. Pulando seeder...');
            return;
        }

        $produtos = [
            // Eletrônicos
            [
                'nome' => 'Smartphone Galaxy Ultra 256GB',
                'descricao' => 'Smartphone premium com câmera de 108MP, processador octa-core, tela AMOLED de 6.8" e bateria de 5000mAh. Ideal para fotografias profissionais e multitarefas.',
                'preco' => 2499.99,
                'estoque' => 25,
                'categoria' => 'Eletrônicos',
                'marca' => 'Samsung',
                'sku' => 'SMART-GALAXY-256',
                'caracteristicas' => [
                    'Tela' => '6.8" AMOLED',
                    'Memória' => '256GB',
                    'RAM' => '12GB',
                    'Câmera' => '108MP',
                    'Bateria' => '5000mAh',
                    'Sistema' => 'Android 14'
                ],
                'peso' => 0.195,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 15.0,
                'ativo' => true,
                'destaque' => true,
                'data_lancamento' => Carbon::now()->subDays(30),
            ],
            [
                'nome' => 'Notebook Gamer RTX 4060',
                'descricao' => 'Notebook gamer com placa RTX 4060, processador Intel i7, 16GB RAM e SSD 512GB. Perfeito para jogos e trabalho profissional.',
                'preco' => 4299.99,
                'estoque' => 8,
                'categoria' => 'Eletrônicos',
                'marca' => 'ASUS',
                'sku' => 'NOTE-GAMER-RTX',
                'caracteristicas' => [
                    'Processador' => 'Intel i7-12700H',
                    'Placa de Vídeo' => 'RTX 4060 8GB',
                    'RAM' => '16GB DDR4',
                    'Armazenamento' => 'SSD 512GB',
                    'Tela' => '15.6" Full HD 144Hz',
                    'Sistema' => 'Windows 11'
                ],
                'peso' => 2.3,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 10.0,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'nome' => 'Fone Bluetooth Cancelamento de Ruído',
                'descricao' => 'Fone over-ear premium com cancelamento ativo de ruído, até 30h de bateria e qualidade de áudio Hi-Res.',
                'preco' => 899.99,
                'estoque' => 15,
                'categoria' => 'Eletrônicos',
                'marca' => 'Sony',
                'sku' => 'FONE-BT-SONY',
                'caracteristicas' => [
                    'Tipo' => 'Over-ear',
                    'Conectividade' => 'Bluetooth 5.2',
                    'Bateria' => '30 horas',
                    'Cancelamento' => 'ANC Ativo',
                    'Drivers' => '40mm',
                    'Peso' => '254g'
                ],
                'peso' => 0.254,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 20.0,
                'ativo' => true,
                'destaque' => false,
            ],

            // Roupas e Acessórios
            [
                'nome' => 'Camiseta Premium 100% Algodão',
                'descricao' => 'Camiseta básica de alta qualidade, 100% algodão pré-encolhido. Confortável e durável para o dia a dia.',
                'preco' => 89.90,
                'estoque' => 50,
                'categoria' => 'Roupas',
                'marca' => 'Basic Style',
                'sku' => 'CAM-BASIC-ALG',
                'caracteristicas' => [
                    'Material' => '100% Algodão',
                    'Modelagem' => 'Regular',
                    'Gola' => 'Careca',
                    'Manga' => 'Curta',
                    'Tamanhos' => 'P, M, G, GG',
                    'Cor' => 'Diversas'
                ],
                'peso' => 0.2,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 0,
                'ativo' => true,
                'destaque' => false,
            ],
            [
                'nome' => 'Tênis Esportivo Air Max',
                'descricao' => 'Tênis esportivo com tecnologia de amortecimento a ar, ideal para corridas e atividades físicas. Design moderno e confortável.',
                'preco' => 299.99,
                'estoque' => 30,
                'categoria' => 'Calçados',
                'marca' => 'SportMax',
                'sku' => 'TENIS-AIR-MAX',
                'caracteristicas' => [
                    'Tipo' => 'Esportivo',
                    'Tecnologia' => 'Air Max',
                    'Material' => 'Mesh + Couro sintético',
                    'Solado' => 'Borracha',
                    'Tamanhos' => '34 ao 44',
                    'Cores' => 'Preto, Branco, Azul'
                ],
                'peso' => 0.8,
                'unidade_medida' => 'par',
                'desconto_percentual' => 25.0,
                'ativo' => true,
                'destaque' => true,
            ],

            // Casa e Decoração
            [
                'nome' => 'Aspirador de Pó Robô Inteligente',
                'descricao' => 'Aspirador robô com mapeamento inteligente, controle por app, função 2 em 1 (aspirar e passar pano) e bateria de longa duração.',
                'preco' => 1299.99,
                'estoque' => 12,
                'categoria' => 'Casa e Jardim',
                'marca' => 'CleanBot',
                'sku' => 'ASPIR-ROBO-INT',
                'caracteristicas' => [
                    'Tipo' => 'Robô 2 em 1',
                    'Potência' => '2000Pa',
                    'Bateria' => '3200mAh',
                    'Autonomia' => '120 minutos',
                    'Reservatório' => '600ml',
                    'Conectividade' => 'WiFi + App'
                ],
                'peso' => 3.2,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 15.0,
                'ativo' => true,
                'destaque' => true,
            ],
            [
                'nome' => 'Conjunto de Panelas Antiaderentes 5 Peças',
                'descricao' => 'Conjunto completo de panelas antiaderentes com revestimento cerâmico, cabos ergonômicos e fundo triplo para distribuição uniforme do calor.',
                'preco' => 459.99,
                'estoque' => 20,
                'categoria' => 'Casa e Jardim',
                'marca' => 'KitchenPro',
                'sku' => 'PAN-ANTI-5PC',
                'caracteristicas' => [
                    'Peças' => '5 panelas + tampas',
                    'Material' => 'Alumínio + Cerâmica',
                    'Revestimento' => 'Antiaderente',
                    'Cabos' => 'Baquelite',
                    'Compatível' => 'Todos fogões',
                    'Garantia' => '2 anos'
                ],
                'peso' => 4.5,
                'unidade_medida' => 'conjunto',
                'desconto_percentual' => 30.0,
                'ativo' => true,
                'destaque' => false,
            ],

            // Saúde e Beleza
            [
                'nome' => 'Whey Protein Concentrado 1kg',
                'descricao' => 'Suplemento de proteína do soro do leite, ideal para ganho de massa muscular e recuperação pós-treino. Sabor chocolate.',
                'preco' => 89.99,
                'estoque' => 40,
                'categoria' => 'Suplementos',
                'marca' => 'FitPro',
                'sku' => 'WHEY-CHOC-1KG',
                'caracteristicas' => [
                    'Proteína por dose' => '24g',
                    'Sabor' => 'Chocolate',
                    'Peso líquido' => '1kg',
                    'Doses por embalagem' => '33',
                    'Tipo' => 'Concentrado',
                    'Origem' => 'Soro do leite'
                ],
                'peso' => 1.2,
                'unidade_medida' => 'kg',
                'desconto_percentual' => 12.0,
                'ativo' => true,
                'destaque' => false,
            ],
            [
                'nome' => 'Kit Cuidados Faciais Completo',
                'descricao' => 'Kit completo para cuidados com a pele: sabonete, tônico, hidratante e protetor solar. Para todos os tipos de pele.',
                'preco' => 159.99,
                'estoque' => 25,
                'categoria' => 'Beleza',
                'marca' => 'SkinCare Plus',
                'sku' => 'KIT-FACIAL-COMP',
                'caracteristicas' => [
                    'Itens' => '4 produtos',
                    'Sabonete' => '150ml',
                    'Tônico' => '200ml',
                    'Hidratante' => '100ml',
                    'Protetor Solar' => '60ml FPS 60',
                    'Tipo de pele' => 'Todos os tipos'
                ],
                'peso' => 0.8,
                'unidade_medida' => 'kit',
                'desconto_percentual' => 25.0,
                'ativo' => true,
                'destaque' => true,
            ],

            // Livros e Educação
            [
                'nome' => 'Livro: Desenvolvimento Web Moderno',
                'descricao' => 'Guia completo sobre desenvolvimento web com HTML5, CSS3, JavaScript ES6+, React e Node.js. Inclui projetos práticos.',
                'preco' => 79.90,
                'estoque' => 35,
                'categoria' => 'Livros',
                'marca' => 'TechBooks',
                'sku' => 'LIVRO-WEB-MOD',
                'caracteristicas' => [
                    'Páginas' => '580',
                    'Idioma' => 'Português',
                    'Edição' => '2ª Edição',
                    'Ano' => '2024',
                    'Formato' => '16x23cm',
                    'Acabamento' => 'Brochura'
                ],
                'peso' => 0.65,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 15.0,
                'ativo' => true,
                'destaque' => false,
            ],

            // Produtos em baixo estoque (para demonstrar alertas)
            [
                'nome' => 'Produto Estoque Baixo - Demo',
                'descricao' => 'Este é um produto de demonstração para mostrar o alerta de estoque baixo.',
                'preco' => 49.99,
                'estoque' => 3, // Estoque baixo
                'categoria' => 'Demo',
                'marca' => 'Demo Brand',
                'sku' => 'DEMO-BAIXO-EST',
                'caracteristicas' => [
                    'Tipo' => 'Demonstração',
                    'Status' => 'Estoque Baixo'
                ],
                'peso' => 0.1,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 0,
                'ativo' => true,
                'destaque' => false,
            ],

            // Produto sem estoque
            [
                'nome' => 'Produto Sem Estoque - Demo',
                'descricao' => 'Este é um produto de demonstração para mostrar quando não há estoque.',
                'preco' => 99.99,
                'estoque' => 0, // Sem estoque
                'categoria' => 'Demo',
                'marca' => 'Demo Brand',
                'sku' => 'DEMO-SEM-EST',
                'caracteristicas' => [
                    'Tipo' => 'Demonstração',
                    'Status' => 'Sem Estoque'
                ],
                'peso' => 0.2,
                'unidade_medida' => 'unidade',
                'desconto_percentual' => 10.0,
                'ativo' => true,
                'destaque' => false,
            ],
        ];

        foreach ($produtos as $produtoData) {
            Produto::create($produtoData);
        }

        $this->command->info('Produtos inseridos com sucesso!');
        $this->command->info('Total de produtos: ' . count($produtos));
        $this->command->line('');
        $this->command->info('Produtos por categoria:');
        
        $categorias = collect($produtos)->groupBy('categoria');
        foreach ($categorias as $categoria => $itens) {
            $this->command->line("- {$categoria}: " . count($itens) . ' produtos');
        }
    }
}