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
        Schema::table('order_purchases', function (Blueprint $table) {
            $table->string('estado')->default('borrador')->after('id'); // borrador, solicitado, recibido, cancelado
            $table->json('ajustes_costos')->nullable()->after('nota_al_distribuidor'); // [{tipo: 'aranceles', importe: 100}, ...]
            $table->decimal('subtotal', 10, 2)->default(0)->after('ajustes_costos');
            $table->decimal('impuestos', 10, 2)->default(0)->after('subtotal');
            $table->decimal('envio', 10, 2)->default(0)->after('impuestos');
            $table->decimal('total', 10, 2)->default(0)->after('envio');
            $table->json('productos')->nullable()->after('total'); // Guardar productos con sus detalles
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_purchases', function (Blueprint $table) {
            $table->dropColumn(['estado', 'ajustes_costos', 'subtotal', 'impuestos', 'envio', 'total', 'productos']);
        });
    }
};
