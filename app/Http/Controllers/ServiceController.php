<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services', [
            'title' => 'Services — CodexeHub | Web, Mobile, AI & Cloud Solutions',
            'description' => 'Explore CodexeHub\'s full range of software, web, mobile, AI and cloud services designed to help businesses grow.',
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->status === 'active', 404);

        $service->load('features');

        $relatedServices = Service::query()
            ->active()
            ->where('id', '!=', $service->id)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('pages.service-show', [
            'title' => "{$service->title} — CodexeHub Services",
            'description' => $service->short_description,
            'service' => $service,
            'relatedServices' => $relatedServices,
        ]);
    }
}
