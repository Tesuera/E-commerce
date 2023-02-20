<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->name();
        return [
            "name" => $name,
            "unique_id" => uniqid() . "_product_" . uniqid(),
            "slug" => Str::slug($name),
            "price" => rand(1,500),
            "stock" => rand(1, 100),
            "description" => $this->faker->paragraph(),
            "category_id" => Category::inRandomOrder()->first()->id,
            "user_id" => rand(1, 2)
        ];
    }
}
