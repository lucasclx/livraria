<?php

namespace Database\Factories;

use App\Models\Livro;
use Illuminate\Database\Eloquent\Factories\Factory;

class LivroFactory extends Factory
{
    protected $model = Livro::class;

    public function definition()
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'autor' => $this->faker->name(),
            'preco' => $this->faker->randomFloat(2, 10, 100),
            'estoque' => $this->faker->numberBetween(1, 10),
            'ano_publicacao' => $this->faker->year(),
        ];
    }
}
