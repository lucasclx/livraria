<?php
// database/migrations/2025_06_09_200000_create_cupons_complete_system.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar se a tabela já existe (pode ter sido criada em migration anterior)
        if (!Schema::hasTable('cupons')) {
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
        }

        // Criar tabela de uso de cupons se não existir
        if (!Schema::hasTable('cupons_utilizados')) {
            Schema::create('cupons_utilizados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cupom_id')->constrained('cupons');
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('order_id')->constrained('orders');
                $table->decimal('desconto_aplicado', 8, 2);
                $table->timestamps();
            });
        }

        // Adicionar campos à tabela orders se não existirem
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'cupom_id')) {
                $table->foreignId('cupom_id')->nullable()->constrained('cupons')->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'desconto')) {
                $table->decimal('desconto', 8, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 8, 2)->default(0)->after('desconto');
            }
        });

        // Adicionar campos à tabela carts para cupons aplicados
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'cupom_aplicado')) {
                $table->string('cupom_aplicado')->nullable()->after('status');
            }
            if (!Schema::hasColumn('carts', 'desconto_cupom')) {
                $table->decimal('desconto_cupom', 8, 2)->default(0)->after('cupom_aplicado');
            }
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['cupom_aplicado', 'desconto_cupom']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cupom_id']);
            $table->dropColumn(['cupom_id', 'desconto', 'subtotal']);
        });

        Schema::dropIfExists('cupons_utilizados');
        Schema::dropIfExists('cupons');
    }
};