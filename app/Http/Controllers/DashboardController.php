<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->hasAnyRole([
            'President',
            'Treasurer',
        ])) {
            return view('dashboard.admin');
        }

        return view('dashboard.member');
    }
}