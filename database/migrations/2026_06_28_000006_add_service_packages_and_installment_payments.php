<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('session_count')->default(1)->after('duration');
        });

        DB::table('services')->orderBy('id')->each(function ($service) {
            $sessions = preg_match('/(\d+) Sessions?/i', $service->name, $matches)
                ? max(1, (int) $matches[1])
                : 1;
            DB::table('services')->where('id', $service->id)->update(['session_count' => $sessions]);
        });

        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('total_sessions');
            $table->unsignedInteger('used_sessions')->default(0);
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('service_package_id')->nullable()->after('service_id')->constrained()->restrictOnDelete();
            $table->boolean('package_session_consumed')->default(false)->after('service_package_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->default(0)->after('grand_total');
        });

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('invoices')->where('status', 'paid')->update([
            'amount_paid' => DB::raw('grand_total'),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn('amount_paid'));
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_package_id');
            $table->dropColumn('package_session_consumed');
        });
        Schema::dropIfExists('service_packages');
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn('session_count'));
    }
};
