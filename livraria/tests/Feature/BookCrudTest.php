<?php

namespace Tests\Feature;

use App\Models\Livro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/livros');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_creates_a_book(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'titulo' => 'Book Title',
            'autor' => 'Author Name',
            'preco' => 19.99,
            'estoque' => 5,
            'ano_publicacao' => 2020,
        ];

        $response = $this->post('/admin/livros', $data);

        $response->assertRedirect('/admin/livros');
        $this->assertDatabaseHas('livros', ['titulo' => 'Book Title']);
    }

    /** @test */
    public function it_validates_book_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/admin/livros', []);

        $response->assertSessionHasErrors(['titulo', 'autor', 'preco', 'estoque']);
    }

    /** @test */
    public function it_updates_a_book(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Livro::create([
            'titulo' => 'Old',
            'autor' => 'Auth',
            'preco' => 10,
            'estoque' => 1,
            'ano_publicacao' => 2000,
        ]);

        $response = $this->put("/admin/livros/{$book->id}", [
            'titulo' => 'New',
            'autor' => 'Author',
            'preco' => 12,
            'estoque' => 2,
        ]);

        $response->assertRedirect('/admin/livros');
        $this->assertDatabaseHas('livros', ['id' => $book->id, 'titulo' => 'New']);
    }

    /** @test */
    public function it_deletes_a_book(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Livro::create([
            'titulo' => 'Delete',
            'autor' => 'Auth',
            'preco' => 10,
            'estoque' => 1,
            'ano_publicacao' => 2000,
        ]);

        $response = $this->delete("/admin/livros/{$book->id}");

        $response->assertRedirect('/admin/livros');
        $this->assertDatabaseMissing('livros', ['id' => $book->id]);
    }
}
