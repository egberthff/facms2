<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Campus;
use App\Services\MemberService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $campuses = Campus::query()
            ->where('is_active', true)
            ->orderBy('campus_name')
            ->get(['id', 'campus_name', 'acronym']);

        return view('auth.register', compact('campuses'));
    }

    /**
     * Handle an incoming self-service registration request.
     */
    public function store(RegisterRequest $request, MemberService $memberService): RedirectResponse
    {
        $user = $memberService->registerSelfServiceMember($request->validated());

        // Sends the email verification link (User implements MustVerifyEmail)
        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
