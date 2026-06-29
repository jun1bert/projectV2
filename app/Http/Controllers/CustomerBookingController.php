<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class CustomerBookingController extends Controller
{
    private const ONLINE_TIME_SLOTS = [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
        '15:00', '15:30', '16:00', '16:30', '17:00',
    ];

    public function index()
    {
        $user = Auth::user();
        $contact = str_starts_with((string) $user->phone, '+63')
            ? '0'.substr($user->phone, 3)
            : $user->phone;

        $client = $user->client;
        if (! $client) {
            $client = Client::whereNull('user_id')
                ->where('contact_number', $contact)
                ->where('full_name', $user->name)
                ->first();

            if ($client) {
                $client->update([
                    'user_id' => $user->id,
                    'email' => $client->email ?: $user->email,
                ]);
            }
        }

        $appointments = $client
            ? $client->appointments()
                ->with(['service', 'services', 'participants.services', 'servicePackage', 'assignedStaffMembers', 'invoice'])
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->get()
            : collect();

        $upcoming = $appointments->filter(fn ($appointment) => $appointment->date >= now()->toDateString()
            && ! in_array($appointment->status, ['completed', 'cancelled'], true));
        $history = $appointments->diff($upcoming);
        $services = Service::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $bookingClient = $client;
        $onlineTimeSlots = self::ONLINE_TIME_SLOTS;

        return view('customer.bookings.index', compact('upcoming', 'history', 'services', 'bookingClient', 'onlineTimeSlots'));
    }
}
