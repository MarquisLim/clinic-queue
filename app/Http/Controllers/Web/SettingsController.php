<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        return Inertia::render('Settings/Index', [
            'user' => $user,
        ]);
    }
}