<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\EmiratesId;

class EmiratesIdFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmiratesId::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'document_type' => $this->faker->regexify('[A-Za-z0-9]{1}'),
            'country_code' => $this->faker->regexify('[A-Za-z0-9]{3}'),
            'card_number' => $this->faker->regexify('[A-Za-z0-9]{10}'),
            'id_number' => $this->faker->regexify('[A-Za-z0-9]{18}'),
            'date_of_birth' => $this->faker->date(),
            'gender' => $this->faker->regexify('[A-Za-z0-9]{6}'),
            'expiry_date' => $this->faker->date(),
            'nationality' => $this->faker->regexify('[A-Za-z0-9]{3}'),
            'surname' => $this->faker->word(),
            'given_names' => $this->faker->word(),
        ];
    }
}
