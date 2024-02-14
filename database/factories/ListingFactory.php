<?php

namespace Database\Factories;

use App\Models\CustomField;
use App\Models\Label;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'service_id' => $this->faker->unique()->slug(4),
            'company' => $this->faker->company,
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->sentence,
            'location' => $this->faker->city,
            'pay_range_start' => $this->faker->randomNumber(9),
            'pay_range_end' => $this->faker->randomNumber(9),
            'currency' => $this->faker->currencyCode,
            'working_hours' => $this->faker->randomNumber(2)
        ];
    }


}
