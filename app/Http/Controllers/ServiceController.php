<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST SERVICES (ADMIN / PUBLIC VIEW)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // PUT HERE: sorting + clean fetch
        $services = Service::orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE SERVICE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $this->authorizeAdmin(); // KEEP THIS LINE (security gate)

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // 🔥 CHANGE HERE: DO NOT USE $request->all()
        Service::create([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Service created.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SERVICE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin(); // KEEP THIS LINE

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $service = Service::findOrFail($id);

        // 🔥 CHANGE HERE: explicit fields only
        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Service updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE SERVICE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $this->authorizeAdmin(); // KEEP THIS LINE

        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | SECURITY CHECK (DO NOT REMOVE)
    |--------------------------------------------------------------------------
    */
    private function authorizeAdmin()
    {
        // 🔥 CHANGE HERE: safer Laravel style
        abort_unless(
            in_array(Auth::user()->role, ['admin', 'management']),
            403
        );
    }
}