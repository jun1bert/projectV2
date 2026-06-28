<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('appointments')
            ->select('client_id', 'full_name', 'contact_number')
            ->whereNotNull('client_id')
            ->distinct()
            ->orderBy('client_id')
            ->get()
            ->groupBy('client_id');

        foreach ($groups as $clientId => $identities) {
            if ($identities->count() < 2) {
                continue;
            }

            $client = DB::table('clients')->where('id', $clientId)->first();
            if (! $client) {
                continue;
            }

            $retained = $identities->first(
                fn ($identity) => mb_strtolower(trim($identity->full_name)) === mb_strtolower(trim($client->full_name))
            ) ?? $identities->first();

            DB::table('clients')->where('id', $clientId)->update([
                'full_name' => $retained->full_name,
                'contact_number' => $retained->contact_number,
                'updated_at' => now(),
            ]);

            foreach ($identities as $identity) {
                if ($identity->full_name === $retained->full_name
                    && $identity->contact_number === $retained->contact_number) {
                    continue;
                }

                $appointmentEmail = DB::table('appointments')
                    ->where('client_id', $clientId)
                    ->where('full_name', $identity->full_name)
                    ->where('contact_number', $identity->contact_number)
                    ->whereNotNull('email')
                    ->value('email');

                $newClientId = DB::table('clients')->insertGetId([
                    'user_id' => null,
                    'full_name' => $identity->full_name,
                    'contact_number' => $identity->contact_number,
                    'email' => $appointmentEmail,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('appointments')
                    ->where('client_id', $clientId)
                    ->where('full_name', $identity->full_name)
                    ->where('contact_number', $identity->contact_number)
                    ->update(['client_id' => $newClientId]);
            }
        }
    }

    public function down(): void
    {
        // Data repair cannot be safely reversed without merging distinct clients again.
    }
};
