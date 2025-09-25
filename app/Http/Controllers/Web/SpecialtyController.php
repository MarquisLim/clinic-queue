<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialtyController extends Controller
{
    public function index()
    {
        $items = Specialty::query()
            ->orderBy('name')
            ->get(['id','name']);

        return Inertia::render('Specialties/Index', [
            'items' => $items,
        ]);
    }
}
