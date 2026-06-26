<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('invoices', 'items_total')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('items_total');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('invoices', 'items_total')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->decimal('items_total', 10, 2)->default(0)->after('service_total');
            });
        }
    }
};
