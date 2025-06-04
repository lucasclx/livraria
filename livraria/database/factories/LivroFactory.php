<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Livro;

/**
 * @extends Factory<Livro>
 */
class LivroFactory extends Factory
{
    protected $model = Livro::class;

    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'autor' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(),
            'editora' => $this->faker->company(),
            'ano_publicacao' => $this->faker->year(),
            'preco' => $this->faker->randomFloat(2, 1, 100),
            'paginas' => $this->faker->numberBetween(50, 800),
            'sinopse' => $this->faker->paragraph(),
            'categoria' => $this->faker->word(),
            'estoque' => $this->faker->numberBetween(0, 50),
            'imagem' => null,
            'ativo' => true,
        ];
    }
}
