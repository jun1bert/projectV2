<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('staff_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('appointment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('service_amount', 10, 2)->default(0);
            $table->decimal('commission_rate', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);

            $table->enum('status', ['pending', 'paid'])->default('pending');

            $table->timestamp('earned_at')->nullable();

            $table->timestamps();

            // CRITICAL: prevent duplicates
            $table->unique('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_commissions');
    }
};