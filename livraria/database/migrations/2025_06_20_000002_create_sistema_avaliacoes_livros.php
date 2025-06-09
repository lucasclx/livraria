<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Atualizar tabela livros com novos campos
        Schema::table('livros', function (Blueprint $table) {
            $table->decimal('preco_promocional', 8, 2)->nullable()->after('preco');
            $table->integer('estoque_minimo')->default(5)->after('estoque');
            $table->decimal('peso', 8, 3)->nullable()->after('estoque_minimo');
            $table->string('dimensoes')->nullable()->after('peso');
            $table->string('idioma')->default('Português')->after('dimensoes');
            $table->string('edicao')->nullable()->after('idioma');
            $table->string('encadernacao')->nullable()->after('edicao');
            $table->json('galeria_imagens')->nullable()->after('imagem');
            $table->string('sumario', 2000)->nullable()->after('sinopse');
            $table->boolean('destaque')->default(false)->after('ativo');
            $table->integer('vendas_total')->default(0)->after('destaque');
            $table->decimal('avaliacao_media', 3, 2)->default(0)->after('vendas_total');
            $table->integer('total_avaliacoes')->default(0)->after('avaliacao_media');
        });

        // Criar tabela de avaliações
        Schema::create('avaliacoes_livros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livro_id')->constrained('livros')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('nota')->unsigned(); // 1-5
            $table->string('titulo')->nullable();
            $table->text('comentario');
            $table->boolean('recomenda')->default(true);
            $table->integer('util_positivo')->default(0);
            $table->integer('util_negativo')->default(0);
            $table->boolean('verificada')->default(false);
            $table->timestamps();

            // Índices
            $table->unique(['livro_id', 'user_id']); // Um usuário pode avaliar um livro apenas uma vez
            $table->index(['livro_id', 'nota']);
            $table->index('verificada');
        });

        // Criar tabela de cupons
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('descricao');
            $table->enum('tipo', ['percentual', 'valor_fixo']);
            $table->decimal('valor', 8, 2);
            $table->decimal('valor_minimo_pedido', 8, 2)->nullable();
            $table->integer('limite_uso')->nullable();
            $table->integer('vezes_usado')->default(0);
            $table->boolean('primeiro_pedido_apenas')->default(false);
            $table->datetime('valido_de');
            $table->datetime('valido_ate');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Criar tabela de uso de cupons
        Schema::create('cupons_utilizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cupom_id')->constrained('cupons');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pedido_id')->constrained('orders');
            $table->decimal('desconto_aplicado', 8, 2);
            $table->timestamps();
        });


        // Atualizar tabela orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cupom_id')->nullable()->constrained('cupons')->after('user_id');
            $table->decimal('desconto', 8, 2)->default(0)->after('total');
            $table->decimal('shipping_cost', 8, 2)->default(0)->after('desconto');
            $table->string('payment_method')->nullable()->after('shipping_cost');
            $table->text('observacoes')->nullable()->after('payment_method');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cupons_utilizados');
        Schema::dropIfExists('cupons');
        Schema::dropIfExists('avaliacoes_livros');
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cupom_id']);
            $table->dropColumn(['cupom_id', 'desconto', 'shipping_cost', 'payment_method', 'observacoes']);
        });

        Schema::table('livros', function (Blueprint $table) {
            $table->dropColumn([
                'preco_promocional', 'estoque_minimo', 'peso', 'dimensoes', 
                'idioma', 'edicao', 'encadernacao', 'galeria_imagens', 'sumario',
                'destaque', 'vendas_total', 'avaliacao_media', 'total_avaliacoes'
            ]);
        });
    }
};