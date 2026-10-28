<?php

namespace Database\Factories;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true) . ' Laptop';
        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'sku' => 'SKU-' . strtoupper(Str::random(8)),
            'condition' => fake()->randomElement([ProductCondition::NEW, ProductCondition::USED]),
            'price' => fake()->numberBetween(5000000, 35000000),
            'stock' => fake()->numberBetween(3, 25),
            'weight_grams' => fake()->numberBetween(1200, 2800),
            'description' => fake()->paragraphs(2, true),
            'status' => ProductStatus::PUBLISHED,
        ];
    }
}

