<?php
// database/migrations/2025_06_25_000002_remove_duplicate_wishlist.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Migrar dados da wishlist para favorites se existir
        if (Schema::hasTable('wishlist')) {
            $wishlistItems = DB::table('wishlist')->get();
            
            foreach ($wishlistItems as $item) {
                // Verificar se já existe em favorites
                $exists = DB::table('favorites')
                    ->where('user_id', $item->user_id)
                    ->where('livro_id', $item->livro_id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('favorites')->insert([
                        'user_id' => $item->user_id,
                        'livro_id' => $item->livro_id,
                        'created_at' => $item->created_at ?? now(),
                        'updated_at' => $item->updated_at ?? now(),
                    ]);
                }
            }
            
            // Remover tabela wishlist
            Schema::dropIfExists('wishlist');
        }
    }

    public function down()
    {
        // Recriar tabela wishlist se necessário
        Schema::create('wishlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('livro_id')->constrained('livros')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'livro_id']);
        });
    }
};