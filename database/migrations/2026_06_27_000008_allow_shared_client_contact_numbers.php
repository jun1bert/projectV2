<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasUniqueContact = collect(Schema::getIndexes('clients'))
            ->contains(fn (array $index) => $index['unique'] && $index['columns'] === ['contact_number']);

        if (! $hasUniqueContact) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['contact_number']);
            $table->index('contact_number');
        });
    }

    public function down(): void
    {
        $hasUniqueContact = collect(Schema::getIndexes('clients'))
            ->contains(fn (array $index) => $index['unique'] && $index['columns'] === ['contact_number']);

        if ($hasUniqueContact) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['contact_number']);
            $table->unique('contact_number');
        });
    }
};
