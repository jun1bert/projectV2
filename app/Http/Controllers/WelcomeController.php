<?php
// app/Http/Controllers/WelcomeController.php
namespace App\Http\Controllers;

use App\Models\GalleryImage;
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

        return view('welcome', compact('galleryImages', 'services'));
    }
}
