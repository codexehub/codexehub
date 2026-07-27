<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\PortfolioSetting;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Web Application', 'slug' => 'web-application'],
            ['name' => 'Mobile Application', 'slug' => 'mobile-application'],
            ['name' => 'E-Commerce', 'slug' => 'e-commerce'],
            ['name' => 'ERP System', 'slug' => 'erp-system'],
            ['name' => 'SaaS Platform', 'slug' => 'saas-platform'],
            ['name' => 'API Development', 'slug' => 'api-development'],
        ])->mapWithKeys(function (array $data) {
            $category = PortfolioCategory::updateOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'status' => 'active']
            );

            return [$data['slug'] => $category];
        });

        $projects = [
            [
                'title' => 'Nimbus Billing Platform',
                'slug' => 'nimbus-billing-platform',
                'category' => 'saas-platform',
                'client_name' => 'Nimbus Technologies',
                'short_description' => 'Subscription billing platform processing 40k+ invoices monthly.',
                'description' => 'A full-featured subscription billing platform built to handle recurring invoicing, dunning management, and usage-based pricing for a fast-growing SaaS client.',
                'technologies' => ['Laravel', 'Vue.js', 'MySQL', 'Stripe'],
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Grovemark Storefront',
                'slug' => 'grovemark-storefront',
                'category' => 'e-commerce',
                'client_name' => 'Grovemark',
                'short_description' => 'Headless commerce storefront handling seasonal traffic spikes.',
                'description' => 'A headless storefront decoupled from the commerce backend, built to absorb seasonal traffic spikes without over-provisioning infrastructure.',
                'technologies' => ['Laravel', 'React', 'MySQL', 'Redis'],
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Meridian Health Portal',
                'slug' => 'meridian-health-portal',
                'category' => 'web-application',
                'client_name' => 'Meridian Health',
                'short_description' => 'Patient portal for appointment booking and secure records access.',
                'description' => 'A patient-facing portal offering appointment booking, secure records access, and provider messaging, built with strict healthcare data handling practices.',
                'technologies' => ['Laravel', 'Livewire', 'PostgreSQL'],
                'featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Orbitel Field App',
                'slug' => 'orbitel-field-app',
                'category' => 'mobile-application',
                'client_name' => 'Orbitel',
                'short_description' => 'Offline-first field service app for 300+ technicians.',
                'description' => 'An offline-first mobile app enabling field technicians to complete work orders, capture signatures, and sync data once back in coverage.',
                'technologies' => ['React Native', 'Node.js', 'MongoDB'],
                'featured' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Fintra Risk Engine',
                'slug' => 'fintra-risk-engine',
                'category' => 'api-development',
                'client_name' => 'Fintra',
                'short_description' => 'Real-time credit risk scoring with an ML inference pipeline.',
                'description' => 'A real-time credit risk scoring API backed by an ML inference pipeline, exposed to downstream lending partners over a versioned REST API.',
                'technologies' => ['Laravel', 'Python', 'Docker', 'AWS'],
                'featured' => false,
                'sort_order' => 5,
            ],
            [
                'title' => 'Vantage Analytics',
                'slug' => 'vantage-analytics',
                'category' => 'erp-system',
                'client_name' => 'Vantage Retail',
                'short_description' => 'Predictive analytics dashboard for retail inventory planning.',
                'description' => 'An internal ERP module giving retail planners predictive analytics on inventory levels, reorder timing, and seasonal demand shifts.',
                'technologies' => ['Laravel', 'Chart.js', 'MySQL'],
                'featured' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($projects as $data) {
            $portfolio = Portfolio::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'client_name' => $data['client_name'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'featured' => $data['featured'],
                    'status' => 'active',
                    'sort_order' => $data['sort_order'],
                ]
            );

            $portfolio->technologies()->delete();
            foreach ($data['technologies'] as $technologyName) {
                $portfolio->technologies()->create(['technology_name' => $technologyName]);
            }
        }

        PortfolioSetting::current();

        $this->command?->info('Portfolio categories, projects and homepage settings seeded.');
    }
}
