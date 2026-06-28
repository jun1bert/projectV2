<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('price_at_booking', 10, 2)->nullable()->after('service_id');
        });

        DB::table('appointments')->orderBy('id')->each(function ($appointment) {
            $price = DB::table('invoices')
                ->where('appointment_id', $appointment->id)
                ->value('service_total');

            $price ??= DB::table('services')
                ->where('id', $appointment->service_id)
                ->value('price');

            DB::table('appointments')->where('id', $appointment->id)->update([
                'price_at_booking' => $price ?? 0,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('price_at_booking');
        });
    }
};
