<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    private array $allowedRoles = ['admin', 'management', 'reception', 'staff'];

    public function index()
    {
        $this->authorizeRole();

        $images = GalleryImage::with('uploader')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('gallery.index', compact('images'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole();

        $request->validate([
            'images' => 'required|array|max:20',
            'images.*' => 'required|image|mimes:jpeg,png,webp,gif|max:5120',
            'title' => 'nullable|string|max:120',
            'caption' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        foreach ($request->file('images') as $file) {
            $path = $file->store('gallery', 'public');

            GalleryImage::create([
                'title' => $request->title,
                'path' => $path,
                'caption' => $request->caption,
                'uploaded_by' => auth()->id(),
                'is_published' => $request->boolean('is_published', true),
                'sort_order' => GalleryImage::max('sort_order') + 1,
            ]);
        }

        return back()->with('success', count($request->file('images')).' image(s) uploaded.');
    }

    public function togglePublish(GalleryImage $image)
    {
        $this->authorizeRole();
        $image->update(['is_published' => ! $image->is_published]);

        return back()->with('success', 'Visibility updated.');
    }

    public function destroy(GalleryImage $image)
    {
        $this->authorizeRole();
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image deleted.');
    }

    private function authorizeRole(): void
    {
        abort_unless(
            in_array(auth()->user()->role, $this->allowedRoles),
            403,
            'Access denied.'
        );
    }
}
