<?php

namespace Tests\Feature;

use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_page_is_accessible(): void
    {
        $response = $this->get('/categorias');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_creates_a_category(): void
    {
        $response = $this->post('/categorias', [
            'nome' => 'Ficção',
            'descricao' => 'Livros de ficção',
        ]);

        $response->assertRedirect('/categorias');
        $this->assertDatabaseHas('categorias', ['nome' => 'Ficção']);
    }

    /** @test */
    public function it_validates_category_creation(): void
    {
        $response = $this->post('/categorias', []);

        $response->assertSessionHasErrors(['nome']);
    }

    /** @test */
    public function it_updates_a_category(): void
    {
        $cat = Categoria::create([
            'nome' => 'Tech',
            'descricao' => 'Original',
        ]);

        $response = $this->put("/categorias/{$cat->id}", [
            'nome' => 'Tecnologia',
            'descricao' => 'Atualizado',
        ]);

        $response->assertRedirect('/categorias');
        $this->assertDatabaseHas('categorias', ['id' => $cat->id, 'nome' => 'Tecnologia']);
    }

    /** @test */
    public function it_deletes_a_category(): void
    {
        $cat = Categoria::create([
            'nome' => 'ToDelete',
        ]);

        $response = $this->delete("/categorias/{$cat->id}");

        $response->assertRedirect('/categorias');
        $this->assertDatabaseMissing('categorias', ['id' => $cat->id]);
    }
}
