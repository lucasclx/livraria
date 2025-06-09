<?php
// database/migrations/2025_06_09_210000_create_user_addresses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label')->default('Casa'); // Casa, Trabalho, etc.
            $table->string('recipient_name');
            $table->string('street');
            $table->string('number');
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state', 2);
            $table->string('postal_code', 10);
            $table->string('reference')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Adicionar campos de endereço à tabela users se necessário
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'data_nascimento')) {
                $table->date('data_nascimento')->nullable()->after('telefone');
            }
            if (!Schema::hasColumn('users', 'genero')) {
                $table->enum('genero', ['masculino', 'feminino', 'outro', 'prefiro_nao_informar'])
                      ->nullable()->after('data_nascimento');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_addresses');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'data_nascimento', 'genero']);
        });
    }
};