<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]); 

        $request->user()->update([
            'password' => $validated['password'],
        ]); 

        // Read the original landing URL from the hidden input field
        if ($request->has('redirect_to')) {
            return redirect()->to($request->input('redirect_to'))->with('status', 'password-updated');
        }   

        return back()->with('status', 'password-updated');
    }

    // public function update(Request $request): RedirectResponse
    // {
    //     $validated = $request->validateWithBag('updatePassword', [
    //         'current_password' => ['required', 'current_password'],
    //         'password' => ['required', Password::defaults(), 'confirmed'],
    //     ]);

    //     $request->user()->update([
    //         'password' => $validated['password'],
    //     ]);

    //     return back()->with('status', 'password-updated');
    // }

    public function create()
    {
        return view('auth.change-password');
    }
}
