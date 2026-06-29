<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => preg_replace('/\s+/', '', (string) $request->input('phone')),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => preg_replace('/\s+/', '', $request->phone),
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        $contact = str_starts_with($user->phone, '+63') ? '0'.substr($user->phone, 3) : $user->phone;
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

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
