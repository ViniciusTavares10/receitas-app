<?php

namespace Database\Factories;

use App\Models\Receita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receita>
 */
class ReceitaFactory extends Factory
{
    protected $model = Receita::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->words(2, true),
            'descricao' => fake()->sentence(),
            'data_registro' => fake()->date(),
            'custo' => fake()->randomFloat(2, 10, 999),
            'tipo_receita' => fake()->randomElement(['doce', 'salgada']),
            'status' => fake()->randomElement(array_keys(Receita::statusOptions())),
        ];
    }
}
