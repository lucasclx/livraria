<?php
// database/migrations/2025_06_25_000003_fix_order_address_fields.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remover campos individuais de endereço
            $table->dropColumn(['street', 'city', 'state', 'zip', 'country']);
            
            // Adicionar campo JSON para endereço completo
            $table->json('shipping_address')->nullable()->after('shipping_cost');
            
            // Adicionar campos de controle de pedido
            $table->string('order_number')->unique()->after('id');
            $table->string('tracking_code')->nullable()->after('shipping_address');
            $table->timestamp('shipped_at')->nullable()->after('tracking_code');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->text('notes')->nullable()->after('delivered_at');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Restaurar campos individuais
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            
            // Remover novos campos
            $table->dropColumn([
                'shipping_address', 
                'order_number', 
                'tracking_code', 
                'shipped_at', 
                'delivered_at', 
                'notes'
            ]);
        });
    }
};