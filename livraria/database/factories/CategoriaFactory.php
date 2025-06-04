<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Categoria;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->unique()->word(),
            'descricao' => $this->faker->sentence(),
            'ativo' => true,
            'imagem' => null,
        ];
    }
}
