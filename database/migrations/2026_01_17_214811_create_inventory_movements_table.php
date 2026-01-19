<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->unsignedBigInteger('id_variant')->nullable();
            $table->unsignedBigInteger('id_inventory')->nullable();
            $table->enum('type', ['entrada', 'salida', 'ajuste', 'devolucion'])->default('ajuste');
            $table->integer('quantity')->default(0);
            $table->integer('cantidad_anterior')->default(0);
            $table->integer('cantidad_nueva')->default(0);
            $table->string('reason')->nullable(); // Motivo del movimiento
            $table->string('reference_type')->nullable(); // Orders, Transfers, etc
            $table->unsignedBigInteger('reference_id')->nullable(); // ID de la referencia
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['id_product', 'created_at']);
            $table->index(['id_inventory', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
