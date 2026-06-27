<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'client_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->after('email')->constrained()->restrictOnDelete();
            });
        }

        DB::table('appointments')->orderBy('id')->each(function ($appointment) {
            if ($appointment->client_id) {
                return;
            }

            $contact = $this->normalizeContact($appointment->contact_number);
            $userId = DB::table('users')
                ->whereIn('phone', array_unique([$contact, $appointment->contact_number]))
                ->value('id');

            $clientId = DB::table('clients')->where('contact_number', $contact)->value('id');

            if (! $clientId) {
                $clientId = DB::table('clients')->insertGetId([
                    'user_id' => $userId,
                    'full_name' => $appointment->full_name,
                    'contact_number' => $contact,
                    'email' => $appointment->email,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('appointments')->where('id', $appointment->id)->update(['client_id' => $clientId]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'client_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('client_id');
            });
        }

        DB::table('clients')->truncate();
    }

    private function normalizeContact(string $contact): string
    {
        $contact = preg_replace('/\s+/', '', $contact);

        return str_starts_with($contact, '+63') ? '0'.substr($contact, 3) : $contact;
    }
};
