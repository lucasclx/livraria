<?php
// database/migrations/2024_XX_XX_XXXXXX_alter_produtos_table_for_lojinha.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Verificar se colunas já existem antes de adicionar
            if (!Schema::hasColumn('produtos', 'nome')) {
                $table->string('nome')->after('id');
            }
            
            if (!Schema::hasColumn('produtos', 'descricao')) {
                $table->text('descricao')->nullable()->after('nome');
            }
            
            if (!Schema::hasColumn('produtos', 'preco')) {
                $table->decimal('preco', 10, 2)->after('descricao');
            }
            
            if (!Schema::hasColumn('produtos', 'estoque')) {
                $table->integer('estoque')->default(0)->after('preco');
            }
            
            if (!Schema::hasColumn('produtos', 'categoria')) {
                $table->string('categoria')->nullable()->after('estoque');
            }
            
            if (!Schema::hasColumn('produtos', 'marca')) {
                $table->string('marca')->nullable()->after('categoria');
            }
            
            if (!Schema::hasColumn('produtos', 'sku')) {
                $table->string('sku')->unique()->nullable()->after('marca');
            }
            
            if (!Schema::hasColumn('produtos', 'caracteristicas')) {
                $table->json('caracteristicas')->nullable()->after('sku');
            }
            
            if (!Schema::hasColumn('produtos', 'imagem')) {
                $table->string('imagem')->nullable()->after('caracteristicas');
            }
            
            if (!Schema::hasColumn('produtos', 'galeria_imagens')) {
                $table->json('galeria_imagens')->nullable()->after('imagem');
            }
            
            if (!Schema::hasColumn('produtos', 'peso')) {
                $table->decimal('peso', 8, 3)->nullable()->after('galeria_imagens');
            }
            
            if (!Schema::hasColumn('produtos', 'unidade_medida')) {
                $table->string('unidade_medida')->default('unidade')->after('peso');
            }
            
            if (!Schema::hasColumn('produtos', 'desconto_percentual')) {
                $table->decimal('desconto_percentual', 5, 2)->default(0)->after('unidade_medida');
            }
            
            if (!Schema::hasColumn('produtos', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('desconto_percentual');
            }
            
            if (!Schema::hasColumn('produtos', 'destaque')) {
                $table->boolean('destaque')->default(false)->after('ativo');
            }
            
            if (!Schema::hasColumn('produtos', 'visualizacoes')) {
                $table->integer('visualizacoes')->default(0)->after('destaque');
            }
            
            if (!Schema::hasColumn('produtos', 'avaliacao_media')) {
                $table->decimal('avaliacao_media', 3, 2)->default(0)->after('visualizacoes');
            }
            
            if (!Schema::hasColumn('produtos', 'total_vendas')) {
                $table->integer('total_vendas')->default(0)->after('avaliacao_media');
            }
            
            if (!Schema::hasColumn('produtos', 'data_lancamento')) {
                $table->timestamp('data_lancamento')->nullable()->after('total_vendas');
            }
        });

        // Adicionar índices se não existirem
        try {
            Schema::table('produtos', function (Blueprint $table) {
                $table->index(['categoria', 'ativo'], 'produtos_categoria_ativo_index');
                $table->index(['destaque', 'ativo'], 'produtos_destaque_ativo_index');
                $table->index('preco', 'produtos_preco_index');
                $table->index('estoque', 'produtos_estoque_index');
            });
        } catch (\Exception $e) {
            // Índices já existem, ignorar erro
        }
    }

    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Remove as colunas adicionadas (apenas se existirem)
            $columnsToRemove = [
                'galeria_imagens', 'caracteristicas', 'marca', 'sku', 'peso', 
                'unidade_medida', 'desconto_percentual', 'destaque', 
                'visualizacoes', 'avaliacao_media', 'total_vendas', 'data_lancamento'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('produtos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Remove índices
        try {
            Schema::table('produtos', function (Blueprint $table) {
                $table->dropIndex('produtos_categoria_ativo_index');
                $table->dropIndex('produtos_destaque_ativo_index');
                $table->dropIndex('produtos_preco_index');
                $table->dropIndex('produtos_estoque_index');
            });
        } catch (\Exception $e) {
            // Índices não existem, ignorar erro
        }
    }
};