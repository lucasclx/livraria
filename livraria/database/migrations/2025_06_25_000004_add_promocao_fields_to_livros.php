<?php
// database/migrations/2025_06_25_000004_add_promocao_fields_to_livros.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('livros', function (Blueprint $table) {
            $table->datetime('promocao_inicio')->nullable()->after('preco_promocional');
            $table->datetime('promocao_fim')->nullable()->after('promocao_inicio');
        });
    }

    public function down()
    {
        Schema::table('livros', function (Blueprint $table) {
            $table->dropColumn(['promocao_inicio', 'promocao_fim']);
        });
    }
};