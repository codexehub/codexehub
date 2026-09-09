<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PlaceholderController extends Controller
{
    public function blog(): View
    {
        return $this->render('Blog Management', 'Publish and manage blog articles.');
    }

    public function settings(): View
    {
        return $this->render('Settings', 'Configure site-wide and account settings.');
    }

    public function reports(): View
    {
        return $this->render('Reports', 'View activity and analytics reports.');
    }

    private function render(string $title, string $description): View
    {
        return view('admin.pages.placeholder', compact('title', 'description'));
    }
}
