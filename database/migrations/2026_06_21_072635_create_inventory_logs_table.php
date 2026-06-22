<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();

            // linked product
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');

            // stock movement type
            $table->enum('type', ['in', 'out', 'adjustment']);

            // quantity changed
            $table->integer('quantity');

            // optional note (reason, supplier, etc.)
            $table->text('note')->nullable();

            // stock before and after change (critical for audit)
            $table->integer('before_qty');
            $table->integer('after_qty');

            $table->timestamps();

            // index for performance (important for ERP scaling)
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};