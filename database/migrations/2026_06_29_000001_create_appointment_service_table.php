<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->decimal('price_at_booking', 10, 2);
            $table->timestamps();
            $table->unique(['appointment_id', 'service_id']);
        });

        DB::table('appointments')->orderBy('id')->each(function ($appointment) {
            DB::table('appointment_service')->insert([
                'appointment_id' => $appointment->id,
                'service_id' => $appointment->service_id,
                'price_at_booking' => $appointment->price_at_booking ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_service');
    }
};
