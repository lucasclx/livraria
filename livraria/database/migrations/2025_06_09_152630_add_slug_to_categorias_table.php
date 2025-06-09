<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            // acrescenta a coluna APÓS o campo nome
            if (!Schema::hasColumn('categorias', 'slug')) {
                $table->string('slug')
                      ->unique()
                      ->after('nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            if (Schema::hasColumn('categorias', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
