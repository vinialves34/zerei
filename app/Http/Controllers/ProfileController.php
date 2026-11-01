<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyProfileRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\LevelService;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profiles, private LevelService $levels) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'level' => $this->levels->progress($user),
            'year' => now()->year,
            'yearPoints' => $this->levels->points($user, now()->year),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->profiles->update($request->user(), $request->validated());

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DestroyProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->profiles->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
