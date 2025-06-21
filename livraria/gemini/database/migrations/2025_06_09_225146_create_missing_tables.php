<?php
// database/migrations/XXXX_XX_XX_XXXXXX_create_missing_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Criar tabela cupons se não existir
        if (!Schema::hasTable('cupons')) {
            Schema::create('cupons', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 20)->unique();
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
                
                // Índices
                $table->index(['ativo', 'valido_de', 'valido_ate']);
                $table->index('codigo');
            });
            
            echo "✅ Tabela 'cupons' criada\n";
        } else {
            echo "ℹ️  Tabela 'cupons' já existe\n";
        }

        // 2. Criar tabela user_addresses se não existir
        if (!Schema::hasTable('user_addresses')) {
            Schema::create('user_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('label', 50)->default('Casa');
                $table->string('recipient_name', 100);
                $table->string('street');
                $table->string('number', 20);
                $table->string('complement', 100)->nullable();
                $table->string('neighborhood', 100);
                $table->string('city', 100);
                $table->string('state', 2);
                $table->string('postal_code', 10);
                $table->string('reference')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                
                // Índices
                $table->index(['user_id', 'is_default']);
            });
            
            echo "✅ Tabela 'user_addresses' criada\n";
        } else {
            echo "ℹ️  Tabela 'user_addresses' já existe\n";
        }

        // 3. Criar tabela cupons_utilizados se não existir
        if (!Schema::hasTable('cupons_utilizados')) {
            Schema::create('cupons_utilizados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cupom_id')->constrained('cupons')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->decimal('desconto_aplicado', 8, 2);
                $table->timestamps();
                
                // Evitar uso duplicado do mesmo cupom no mesmo pedido
                $table->unique(['cupom_id', 'order_id']);
                $table->index('user_id');
            });
            
            echo "✅ Tabela 'cupons_utilizados' criada\n";
        } else {
            echo "ℹ️  Tabela 'cupons_utilizados' já existe\n";
        }

        // 4. Adicionar campos nas tabelas existentes se necessário
        
        // Adicionar campos ao users se não existirem
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 20)->nullable()->after('email');
                echo "✅ Campo 'telefone' adicionado à tabela users\n";
            }
            if (!Schema::hasColumn('users', 'data_nascimento')) {
                $table->date('data_nascimento')->nullable()->after('telefone');
                echo "✅ Campo 'data_nascimento' adicionado à tabela users\n";
            }
            if (!Schema::hasColumn('users', 'genero')) {
                $table->enum('genero', ['masculino', 'feminino', 'outro', 'prefiro_nao_informar'])
                      ->nullable()->after('data_nascimento');
                echo "✅ Campo 'genero' adicionado à tabela users\n";
            }
        });

        // Adicionar campos ao orders se não existirem
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'cupom_id')) {
                $table->foreignId('cupom_id')->nullable()->constrained('cupons')->after('user_id');
                echo "✅ Campo 'cupom_id' adicionado à tabela orders\n";
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total');
                echo "✅ Campo 'subtotal' adicionado à tabela orders\n";
            }
            if (!Schema::hasColumn('orders', 'desconto')) {
                $table->decimal('desconto', 8, 2)->default(0)->after('subtotal');
                echo "✅ Campo 'desconto' adicionado à tabela orders\n";
            }
        });

        // Adicionar campos ao carts se não existirem
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'cupom_aplicado')) {
                $table->string('cupom_aplicado', 20)->nullable()->after('status');
                echo "✅ Campo 'cupom_aplicado' adicionado à tabela carts\n";
            }
            if (!Schema::hasColumn('carts', 'desconto_cupom')) {
                $table->decimal('desconto_cupom', 8, 2)->default(0)->after('cupom_aplicado');
                echo "✅ Campo 'desconto_cupom' adicionado à tabela carts\n";
            }
        });
    }

    public function down()
    {
        // Remover campos adicionados
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['cupom_aplicado', 'desconto_cupom']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cupom_id']);
            $table->dropColumn(['cupom_id', 'subtotal', 'desconto']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'data_nascimento', 'genero']);
        });

        // Remover tabelas
        Schema::dropIfExists('cupons_utilizados');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('cupons');
    }
};