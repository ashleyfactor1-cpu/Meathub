<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_marketplace_shows_product_details_and_category_filters(): void
    {
        Product::create([
            'name' => 'Pork Belly',
            'producer_name' => 'San Isidro Farm',
            'category' => 'Pork',
            'price' => 320,
            'stock_kg' => 4,
            'description' => 'Fresh pork belly.',
            'is_available' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pork Belly')
            ->assertSee('San Isidro Farm')
            ->assertSee('Price per KG')
            ->assertSee('Available')
            ->assertSee('Add to Cart')
            ->assertSee('Organ Meat');
    }
}
