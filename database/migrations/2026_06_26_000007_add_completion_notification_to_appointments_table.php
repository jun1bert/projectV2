<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('appointments', 'completion_notified_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->timestamp('completion_notified_at')->nullable()->after('payment_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'completion_notified_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('completion_notified_at');
            });
        }
    }
};
