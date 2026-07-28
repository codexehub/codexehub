<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalServices = Service::count();
        $totalConsultations = Consultation::count();

        $stats = [
            [
                'label' => 'Total Users',
                'value' => number_format($totalUsers),
                'icon' => 'bi-people-fill',
                'change' => '+8.2%',
                'trend' => 'up',
            ],
            [
                'label' => 'Total Projects',
                'value' => '24',
                'icon' => 'bi-kanban-fill',
                'change' => '+4.6%',
                'trend' => 'up',
            ],
            [
                'label' => 'Total Services',
                'value' => number_format($totalServices),
                'icon' => 'bi-grid-fill',
                'change' => '0%',
                'trend' => 'flat',
            ],
            [
                'label' => 'Revenue',
                'value' => '$48,200',
                'icon' => 'bi-cash-coin',
                'change' => '+12.4%',
                'trend' => 'up',
            ],
            [
                'label' => 'Consultation Requests',
                'value' => number_format($totalConsultations),
                'icon' => 'bi-calendar2-check-fill',
                'change' => '0%',
                'trend' => 'flat',
            ],
            [
                'label' => 'Website Visits',
                'value' => '32.4k',
                'icon' => 'bi-graph-up-arrow',
                'change' => '+18.9%',
                'trend' => 'up',
            ],
        ];

        $months = collect(range(0, 5))->map(fn ($i) => now()->subMonths(5 - $i)->format('M'));

        $registrations = User::selectRaw("strftime('%Y-%m', created_at) as ym, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $registrationSeries = collect(range(0, 5))->map(function ($i) use ($registrations) {
            $key = now()->subMonths(5 - $i)->format('Y-m');

            return $registrations->get($key, 0);
        });

        $charts = [
            'sales' => [
                'labels' => $months,
                'data' => [12000, 15500, 14200, 18900, 21400, 24800],
            ],
            'registrations' => [
                'labels' => $months,
                'data' => $registrationSeries,
            ],
            'traffic' => [
                'labels' => ['Direct', 'Organic Search', 'Social', 'Referral', 'Email'],
                'data' => [38, 30, 16, 10, 6],
            ],
            'projects' => [
                'labels' => $months,
                'data' => [3, 5, 4, 7, 6, 9],
            ],
        ];

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.pages.dashboard', compact('stats', 'charts', 'recentUsers'));
    }
}
