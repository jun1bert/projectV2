<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'requires_consent' => 'nullable|boolean',
        ]);

        Service::create([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
            'requires_consent' => $request->boolean('requires_consent'),
        ]);

        return back()->with('success', 'Service created.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'requires_consent' => 'nullable|boolean',
        ]);

        $service = Service::findOrFail($id);

        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
            'requires_consent' => $request->boolean('requires_consent'),
        ]);

        return back()->with('success', 'Service updated.');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();

        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service deleted.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'management']), 403);
    }
}
