<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            [
                'nome' => 'Ficção',
                'descricao' => 'Livros de ficção e literatura imaginativa',
                'ativo' => true,
            ],
            [
                'nome' => 'Não-ficção',
                'descricao' => 'Livros baseados em fatos reais',
                'ativo' => true,
            ],
            [
                'nome' => 'Técnico',
                'descricao' => 'Livros técnicos e educacionais',
                'ativo' => true,
            ],
            [
                'nome' => 'Romance',
                'descricao' => 'Livros de romance e relacionamentos',
                'ativo' => true,
            ],
            [
                'nome' => 'Biografia',
                'descricao' => 'Biografias e memórias',
                'ativo' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}