<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Migrar dados de categoria (string) para categoria_id
        $categorias = DB::table('livros')
            ->whereNotNull('categoria')
            ->distinct()
            ->pluck('categoria');

        foreach ($categorias as $categoriaNome) {
            // Criar categoria se não existir
            $categoriaId = DB::table('categorias')
                ->where('nome', $categoriaNome)
                ->value('id');

            if (!$categoriaId) {
                $categoriaId = DB::table('categorias')->insertGetId([
                    'nome' => $categoriaNome,
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Atualizar livros
            DB::table('livros')
                ->where('categoria', $categoriaNome)
                ->update(['categoria_id' => $categoriaId]);
        }

        // Remover coluna categoria (string)
        Schema::table('livros', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }

    public function down()
    {
        Schema::table('livros', function (Blueprint $table) {
            $table->string('categoria')->nullable()->after('categoria_id');
        });

        // Restaurar dados
        $livros = DB::table('livros')
            ->join('categorias', 'livros.categoria_id', '=', 'categorias.id')
            ->select('livros.id', 'categorias.nome')
            ->get();

        foreach ($livros as $livro) {
            DB::table('livros')
                ->where('id', $livro->id)
                ->update(['categoria' => $livro->nome]);
        }
    }
};