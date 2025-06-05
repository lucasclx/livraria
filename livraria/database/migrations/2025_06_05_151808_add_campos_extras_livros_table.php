<?php
// database/migrations/2024_XX_XX_XXXXXX_add_campos_extras_livros_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('livros', function (Blueprint $table) {
            // Campos novos para livraria moderna
            if (!Schema::hasColumn('livros', 'destaque')) {
                $table->boolean('destaque')->default(false)->after('ativo');
            }
            
            if (!Schema::hasColumn('livros', 'idioma')) {
                $table->string('idioma', 10)->default('pt')->after('categoria');
            }
            
            if (!Schema::hasColumn('livros', 'edicao')) {
                $table->string('edicao', 50)->nullable()->after('idioma');
            }
            
            if (!Schema::hasColumn('livros', 'genero')) {
                $table->string('genero', 50)->nullable()->after('edicao');
            }
            
            if (!Schema::hasColumn('livros', 'tags')) {
                $table->json('tags')->nullable()->after('genero');
            }
            
            if (!Schema::hasColumn('livros', 'desconto_percentual')) {
                $table->decimal('desconto_percentual', 5, 2)->default(0)->after('preco');
            }
            
            if (!Schema::hasColumn('livros', 'peso')) {
                $table->decimal('peso', 6, 3)->nullable()->after('paginas'); // em gramas
            }
            
            if (!Schema::hasColumn('livros', 'dimensoes')) {
                $table->string('dimensoes', 50)->nullable()->after('peso'); // ex: "14x21x2cm"
            }
            
            if (!Schema::hasColumn('livros', 'visualizacoes')) {
                $table->integer('visualizacoes')->default(0)->after('estoque');
            }
            
            if (!Schema::hasColumn('livros', 'total_vendas')) {
                $table->integer('total_vendas')->default(0)->after('visualizacoes');
            }
            
            if (!Schema::hasColumn('livros', 'avaliacao_media')) {
                $table->decimal('avaliacao_media', 3, 2)->default(0)->after('total_vendas'); // 0.00 a 5.00
            }
            
            if (!Schema::hasColumn('livros', 'total_avaliacoes')) {
                $table->integer('total_avaliacoes')->default(0)->after('avaliacao_media');
            }
        });

        // Adicionar índices para performance
        try {
            Schema::table('livros', function (Blueprint $table) {
                $table->index(['destaque', 'ativo'], 'livros_destaque_ativo_index');
                $table->index(['genero', 'ativo'], 'livros_genero_ativo_index');
                $table->index(['idioma'], 'livros_idioma_index');
                $table->index(['desconto_percentual'], 'livros_desconto_index');
                $table->index(['avaliacao_media'], 'livros_avaliacao_index');
                $table->index(['total_vendas'], 'livros_vendas_index');
                $table->index(['visualizacoes'], 'livros_views_index');
            });
        } catch (\Exception $e) {
            // Índices já existem, ignorar
        }
    }

    public function down()
    {
        Schema::table('livros', function (Blueprint $table) {
            // Remove campos extras (apenas se existirem)
            $columnsToRemove = [
                'destaque', 'idioma', 'edicao', 'genero', 'tags', 
                'desconto_percentual', 'peso', 'dimensoes', 
                'visualizacoes', 'total_vendas', 'avaliacao_media', 'total_avaliacoes'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('livros', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Remove índices
        try {
            Schema::table('livros', function (Blueprint $table) {
                $table->dropIndex('livros_destaque_ativo_index');
                $table->dropIndex('livros_genero_ativo_index');
                $table->dropIndex('livros_idioma_index');
                $table->dropIndex('livros_desconto_index');
                $table->dropIndex('livros_avaliacao_index');
                $table->dropIndex('livros_vendas_index');
                $table->dropIndex('livros_views_index');
            });
        } catch (\Exception $e) {
            // Índices não existem, ignorar
        }
    }
};