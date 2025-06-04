<?php

namespace Tests\Feature;

use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoriaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_categoria_with_image(): void
    {
        Storage::fake('local');

        $data = Categoria::factory()->make()->toArray();
        $data['imagem'] = UploadedFile::fake()->image('cat.jpg');

        $response = $this->post(route('categorias.store'), $data);

        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', [
            'nome' => $data['nome'],
        ]);
        $categoria = Categoria::first();
        Storage::disk('local')->assertExists('public/categorias/'.$categoria->imagem);
    }

    public function test_store_categoria_validation(): void
    {
        $response = $this->post(route('categorias.store'), []);

        $response->assertSessionHasErrors('nome');
        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_update_categoria_replaces_image(): void
    {
        Storage::fake('local');
        $categoria = Categoria::factory()->create(['imagem' => 'old.jpg']);
        Storage::disk('local')->put('public/categorias/old.jpg', 'old-file');

        $newImage = UploadedFile::fake()->image('new.jpg');
        $response = $this->put(route('categorias.update', $categoria), [
            'nome' => 'Updated',
            'descricao' => 'desc',
            'ativo' => true,
            'imagem' => $newImage,
        ]);

        $response->assertRedirect(route('categorias.index'));
        $categoria->refresh();
        $this->assertSame('Updated', $categoria->nome);
        Storage::disk('local')->assertMissing('public/categorias/old.jpg');
        Storage::disk('local')->assertExists('public/categorias/'.$categoria->imagem);
    }

    public function test_delete_categoria_removes_image(): void
    {
        Storage::fake('local');
        $categoria = Categoria::factory()->create(['imagem' => 'del.jpg']);
        Storage::disk('local')->put('public/categorias/del.jpg', 'file');

        $response = $this->delete(route('categorias.destroy', $categoria));

        $response->assertRedirect(route('categorias.index'));
        $response->assertSessionHasNoErrors();
        $this->assertEquals(0, Categoria::count(), json_encode(session()->all()));
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
        Storage::disk('local')->assertMissing('public/categorias/del.jpg');
    }
}
