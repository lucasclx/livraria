<?php

namespace Tests\Feature;

use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LivroCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_livro_with_image(): void
    {
        Storage::fake('public');

        $data = Livro::factory()->make()->toArray();
        $data['imagem'] = UploadedFile::fake()->image('livro.jpg');

        $response = $this->post(route('livros.store'), $data);

        $response->assertRedirect(route('livros.index'));
        $this->assertDatabaseHas('livros', [
            'titulo' => $data['titulo'],
        ]);
        $livro = Livro::first();
        Storage::disk('public')->assertExists('livros/'.$livro->imagem);
    }

    public function test_store_livro_validation(): void
    {
        $response = $this->post(route('livros.store'), []);

        $response->assertSessionHasErrors(['titulo', 'autor', 'preco', 'estoque']);
        $this->assertDatabaseCount('livros', 0);
    }

    public function test_update_livro_replaces_image(): void
    {
        Storage::fake('public');
        $livro = Livro::factory()->create(['imagem' => 'old.jpg']);
        Storage::disk('public')->put('livros/old.jpg', 'old-file');

        $newImage = UploadedFile::fake()->image('new.jpg');
        $response = $this->put(route('livros.update', $livro), [
            'titulo' => 'Novo',
            'autor' => 'Autor',
            'preco' => 10,
            'estoque' => 1,
            'imagem' => $newImage,
        ] + $livro->only('isbn','editora','ano_publicacao','paginas','sinopse','categoria','ativo'));

        $response->assertRedirect(route('livros.index'));
        $livro->refresh();
        $this->assertSame('Novo', $livro->titulo);
        Storage::disk('public')->assertMissing('livros/old.jpg');
        Storage::disk('public')->assertExists('livros/'.$livro->imagem);
    }

    public function test_delete_livro_removes_image(): void
    {
        Storage::fake('public');
        $livro = Livro::factory()->create(['imagem' => 'del.jpg']);
        Storage::disk('public')->put('livros/del.jpg', 'file');

        $response = $this->delete(route('livros.destroy', $livro));

        $response->assertRedirect(route('livros.index'));
        $this->assertDatabaseCount('livros', 0);
        Storage::disk('public')->assertMissing('livros/del.jpg');
    }
}
