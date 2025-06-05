<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('livros', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('autor');
            $table->string('isbn')->nullable()->unique();
            $table->string('editora')->nullable();
            $table->integer('ano_publicacao')->nullable(); // ← MUDANÇA AQUI
            $table->decimal('preco', 8, 2);
            $table->integer('paginas')->nullable();
            $table->text('sinopse')->nullable();
            $table->string('categoria')->nullable();
            $table->integer('estoque')->default(0);
            $table->string('imagem')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('livros');
    }
};