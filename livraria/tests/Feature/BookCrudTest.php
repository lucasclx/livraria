<?php

namespace Tests\Feature;

use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_page_is_accessible(): void
    {
        $response = $this->get('/livros');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_creates_a_book(): void
    {
        $data = [
            'titulo' => 'Book Title',
            'autor' => 'Author Name',
            'preco' => 19.99,
            'estoque' => 5,
            'ano_publicacao' => 2020,
        ];

        $response = $this->post('/livros', $data);

        $response->assertRedirect('/livros');
        $this->assertDatabaseHas('livros', ['titulo' => 'Book Title']);
    }

    /** @test */
    public function it_validates_book_creation(): void
    {
        $response = $this->post('/livros', []);

        $response->assertSessionHasErrors(['titulo', 'autor', 'preco', 'estoque']);
    }

    /** @test */
    public function it_updates_a_book(): void
    {
        $book = Livro::create([
            'titulo' => 'Old',
            'autor' => 'Auth',
            'preco' => 10,
            'estoque' => 1,
            'ano_publicacao' => 2000,
        ]);

        $response = $this->put("/livros/{$book->id}", [
            'titulo' => 'New',
            'autor' => 'Author',
            'preco' => 12,
            'estoque' => 2,
        ]);

        $response->assertRedirect('/livros');
        $this->assertDatabaseHas('livros', ['id' => $book->id, 'titulo' => 'New']);
    }

    /** @test */
    public function it_deletes_a_book(): void
    {
        $book = Livro::create([
            'titulo' => 'Delete',
            'autor' => 'Auth',
            'preco' => 10,
            'estoque' => 1,
            'ano_publicacao' => 2000,
        ]);

        $response = $this->delete("/livros/{$book->id}");

        $response->assertRedirect('/livros');
        $this->assertDatabaseMissing('livros', ['id' => $book->id]);
    }
}
