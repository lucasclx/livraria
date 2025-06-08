<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->string('status');
            $table->string('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_status_histories');
    }
};
