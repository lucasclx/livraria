<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_items_and_checkout(): void
    {
        $user = User::factory()->create();
        $book = Livro::factory()->create();

        // Add to cart
        $response = $this->post("/carrinho/adicionar/{$book->id}", ['quantity' => 2]);
        $response->assertRedirect();

        $cart = Cart::first();
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items);

        // authenticate
        $this->actingAs($user);

        // View checkout page
        $response = $this->get('/checkout');
        $response->assertStatus(200);

        // Process checkout
        $checkoutData = [
            'street' => '123 Test St',
            'city' => 'Testville',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'Testland',
            'payment_method' => 'pix',
        ];
        $response = $this->post('/checkout', $checkoutData);
        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'cart_id' => $cart->id,
        ]);
    }
}
