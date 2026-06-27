<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->restrictOnDelete();

            $table->foreignId('service_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('date');
            $table->time('time');
            $table->text('notes')->nullable();

            $table->string('status')->default('pending');
            $table->timestamp('completion_notified_at')->nullable();
            $table->string('booking_type')->default('online');

            $table->timestamps();
            $table->index(['date', 'status']);
            $table->index('contact_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
