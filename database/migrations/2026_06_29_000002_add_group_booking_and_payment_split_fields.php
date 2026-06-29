<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedSmallInteger('party_size')->default(1)->after('email');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->string('payment_scope')->default('whole')->after('amount');
            $table->unsignedSmallInteger('client_count')->nullable()->after('payment_scope');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_scope', 'client_count']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('party_size');
        });
    }
};
