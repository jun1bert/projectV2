<?php
// app/Http/Controllers/WelcomeController.php
namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\Appointment;
use App\Models\Service;

class WelcomeController extends Controller
{
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

        return view('welcome', compact('galleryImages', 'services', 'bookedSlots'));
    }
}
