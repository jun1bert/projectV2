<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('contact_number');
            $table->string('email')->nullable();

            // FK SAFE ORDERED
            $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('date');
            $table->time('time');
            $table->text('notes')->nullable();

            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('booking_type')->default('online');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
