<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $teamMembers = TeamMember::active()
            ->with('images')
            ->orderBy('sort_order')
            ->get();

        return view('pages.about', compact('teamMembers'));
    }
}
