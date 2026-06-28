<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clients')->orderBy('id')->each(function ($client) {
            $email = DB::table('appointments')
                ->where('client_id', $client->id)
                ->where('full_name', $client->full_name)
                ->where('contact_number', $client->contact_number)
                ->whereNotNull('email')
                ->where('email', '<>', '')
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->value('email');

            DB::table('clients')->where('id', $client->id)->update([
                'email' => $email,
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Correct email ownership cannot be safely reverted.
    }
};
