<?php
// app/Http/Controllers/WelcomeController.php
namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\Appointment;
use App\Models\Service;

class WelcomeController extends Controller
{
    private const ONLINE_TIME_SLOTS = [
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
    ];

    public function index()
    {
        $galleryImages = GalleryImage::where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $services = Service::where('is_active', true)->get();

        $bookedSlots = Appointment::query()
            ->where('status', 'confirmed')
            ->where(function ($query) {
                $query->where('booking_type', 'online')
                    ->orWhereNull('booking_type');
            })
            ->selectRaw('date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $onlineTimeSlots = self::ONLINE_TIME_SLOTS;

        return view('welcome', compact('galleryImages', 'services', 'bookedSlots', 'onlineTimeSlots'));
    }
}
