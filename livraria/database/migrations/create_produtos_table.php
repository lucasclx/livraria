<?php
// database/migrations/create_produtos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2);
            $table->integer('estoque')->default(0);
            $table->string('categoria')->nullable();
            $table->string('marca')->nullable();
            $table->string('sku')->unique()->nullable(); // Código do produto
            $table->json('caracteristicas')->nullable(); // Para armazenar características específicas
            $table->string('imagem')->nullable();
            $table->json('galeria_imagens')->nullable(); // Para múltiplas imagens
            $table->decimal('peso', 8, 3)->nullable(); // em kg
            $table->string('unidade_medida')->default('unidade'); // unidade, kg, metro, etc
            $table->decimal('desconto_percentual', 5, 2)->default(0); // desconto em %
            $table->boolean('ativo')->default(true);
            $table->boolean('destaque')->default(false); // produto em destaque
            $table->integer('visualizacoes')->default(0);
            $table->decimal('avaliacao_media', 3, 2)->default(0); // 0 a 5
            $table->integer('total_vendas')->default(0);
            $table->timestamp('data_lancamento')->nullable();
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['categoria', 'ativo']);
            $table->index(['destaque', 'ativo']);
            $table->index('preco');
            $table->index('estoque');
        });
    }

    public function down()
    {
        Schema::dropIfExists('produtos');
    }
};