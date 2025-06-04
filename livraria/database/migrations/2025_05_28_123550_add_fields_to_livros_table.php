<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('livros', function (Blueprint $table) {
            // Adicione apenas os campos que não existem ainda na sua tabela
            if (!Schema::hasColumn('livros', 'ativo')) {
                $table->boolean('ativo')->default(true);
            }
            if (!Schema::hasColumn('livros', 'isbn')) {
                $table->string('isbn')->nullable();
            }
            if (!Schema::hasColumn('livros', 'editora')) {
                $table->string('editora')->nullable();
            }
            if (!Schema::hasColumn('livros', 'ano_publicacao')) {
                $table->year('ano_publicacao')->nullable();
            }
            if (!Schema::hasColumn('livros', 'paginas')) {
                $table->integer('paginas')->nullable();
            }
            if (!Schema::hasColumn('livros', 'sinopse')) {
                $table->text('sinopse')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('livros', function (Blueprint $table) {
            $table->dropColumn(['ativo', 'isbn', 'editora', 'ano_publicacao', 'paginas', 'sinopse']);
        });
    }
};