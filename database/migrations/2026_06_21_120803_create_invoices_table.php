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
        Schema::create('invoices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();

    $table->decimal('service_total', 10, 2)->default(0);
    $table->decimal('items_total', 10, 2)->default(0);
    $table->decimal('grand_total', 10, 2)->default(0);

    $table->string('payment_method')->nullable(); // cash/gcash/bank
    $table->string('status')->default('unpaid');   // unpaid/paid

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
