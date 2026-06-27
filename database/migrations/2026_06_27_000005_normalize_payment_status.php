<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'payment_status')) {
            return;
        }

        $paidAppointments = DB::table('appointments')
            ->join('services', 'services.id', '=', 'appointments.service_id')
            ->leftJoin('invoices', 'invoices.appointment_id', '=', 'appointments.id')
            ->where('appointments.payment_status', 'paid')
            ->whereNull('invoices.id')
            ->select('appointments.id', 'services.price')
            ->get();

        foreach ($paidAppointments as $appointment) {
            DB::table('invoices')->insert([
                'appointment_id' => $appointment->id,
                'service_total' => $appointment->price,
                'grand_total' => $appointment->price,
                'payment_method' => null,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'payment_status')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('status');
        });

        DB::table('appointments')
            ->join('invoices', 'invoices.appointment_id', '=', 'appointments.id')
            ->where('invoices.status', 'paid')
            ->update(['appointments.payment_status' => 'paid']);
    }
};
