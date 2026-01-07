<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $client = Client::query()->where('user_id', $user->id)->first();
        $rentals = collect();
        $testDrives = collect();

        if ($client) {
            $rentals = $client->rentals()
                ->with('car')
                ->latest('starts_at')
                ->limit(10)
                ->get();

            $testDrives = $client->testDrives()
                ->with(['car', 'manager'])
                ->latest('scheduled_at')
                ->limit(10)
                ->get();
        }

        return view('profile.edit', [
            'user' => $user,
            'client' => $client,
            'rentals' => $rentals,
            'testDrives' => $testDrives,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the client's profile information.
     */
    public function updateClient(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'middle_name' => ['nullable', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:120'],
            'driver_license_number' => ['required', 'string', 'max:50'],
            'driver_license_issued_at' => ['required', 'date'],
            'driver_license_expires_at' => ['required', 'date', 'after_or_equal:driver_license_issued_at'],
            'birth_date' => ['required', 'date'],
            'trusted_person_name' => ['nullable', 'string', 'max:120'],
            'trusted_person_phone' => ['nullable', 'string', 'max:30'],
            'trusted_person_license_number' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $client = Client::query()->firstOrNew(['user_id' => $user->id]);

        $client->fill($data);
        $client->user_id = $user->id;
        $client->save();

        return Redirect::route('profile.edit')->with('status', 'client-profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
