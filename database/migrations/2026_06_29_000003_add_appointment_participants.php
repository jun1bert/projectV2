<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('invoice_payment_participant');
        Schema::dropIfExists('appointment_participant_service');
        Schema::dropIfExists('appointment_participants');

        Schema::create('appointment_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->unsignedSmallInteger('position');
            $table->timestamps();
            $table->unique(['appointment_id', 'position']);
        });

        Schema::create('appointment_participant_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_participant_id');
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->decimal('price_at_booking', 10, 2);
            $table->timestamps();
            $table->unique(['appointment_participant_id', 'service_id'], 'participant_service_unique');
            $table->foreign('appointment_participant_id', 'participant_service_participant_fk')->references('id')->on('appointment_participants')->cascadeOnDelete();
        });

        Schema::create('invoice_payment_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_payment_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('appointment_participant_id');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['invoice_payment_id', 'appointment_participant_id'], 'payment_participant_unique');
            $table->foreign('appointment_participant_id', 'payment_participant_participant_fk')->references('id')->on('appointment_participants')->restrictOnDelete();
        });

        DB::table('appointments')->orderBy('id')->each(function ($appointment) {
            $services = DB::table('appointment_service')->where('appointment_id', $appointment->id)->get();
            for ($position = 1; $position <= max(1, (int) $appointment->party_size); $position++) {
                $participantId = DB::table('appointment_participants')->insertGetId([
                    'appointment_id' => $appointment->id,
                    'name' => $position === 1 ? $appointment->full_name : null,
                    'position' => $position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                foreach ($services as $service) {
                    DB::table('appointment_participant_service')->insert([
                        'appointment_participant_id' => $participantId,
                        'service_id' => $service->service_id,
                        'price_at_booking' => $service->price_at_booking,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_participant');
        Schema::dropIfExists('appointment_participant_service');
        Schema::dropIfExists('appointment_participants');
    }
};
