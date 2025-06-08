<?php
// database/migrations/2025_06_20_000001_fix_livros_categorias_relationship.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('livros', function (Blueprint $table) {
            // Adicionar nova coluna para FK
            $table->unsignedBigInteger('categoria_id')->nullable()->after('categoria');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            
            // Manter categoria como string temporariamente para migração
            // Será removida após migração dos dados
        });
    }

    public function down()
    {
        Schema::table('livros', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
    }
};