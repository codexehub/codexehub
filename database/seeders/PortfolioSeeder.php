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
                'industry' => 'SaaS, Fintech',
                'location' => 'United States',
                'duration' => '4 Months',
                'team_size' => '5 Members',
                'completed_date' => '2026-03-15',
                'short_description' => 'Subscription billing platform processing 40k+ invoices monthly.',
                'description' => 'A full-featured subscription billing platform built to handle recurring invoicing, dunning management, and usage-based pricing for a fast-growing SaaS client.',
                'challenge' => 'Nimbus was losing revenue to failed card payments and manual invoice reconciliation as it scaled past 40,000 monthly subscribers, with no visibility into churn risk.',
                'solution' => 'We built an event-driven billing engine with automated dunning workflows, usage-based pricing rules, and real-time revenue dashboards, integrated directly with Stripe.',
                'technologies' => ['Laravel', 'Vue.js', 'MySQL', 'Stripe'],
                'highlights' => [
                    ['icon' => 'bi-arrow-repeat', 'text' => 'Automated recurring invoicing and dunning management'],
                    ['icon' => 'bi-credit-card', 'text' => 'Usage-based and tiered pricing engine'],
                    ['icon' => 'bi-shield-lock', 'text' => 'PCI-compliant Stripe payment integration'],
                    ['icon' => 'bi-graph-up', 'text' => 'Real-time revenue and churn analytics dashboard'],
                ],
                'results' => [
                    ['icon' => 'bi-graph-up-arrow', 'value' => '35%', 'label' => 'Reduction in Failed Payments'],
                    ['icon' => 'bi-lightning-charge', 'value' => '60%', 'label' => 'Faster Invoice Reconciliation'],
                    ['icon' => 'bi-arrow-up-right-circle', 'value' => '22%', 'label' => 'Increase in Recovered Revenue'],
                    ['icon' => 'bi-emoji-smile', 'value' => '98%', 'label' => 'Client Satisfaction'],
                ],
                'testimonial_quote' => 'CodexeHub rebuilt our billing platform in under four months with zero downtime during migration. The team communicates like an in-house squad.',
                'testimonial_name' => 'Sarah Malik',
                'testimonial_role' => 'CTO, Nimbus Technologies',
                'testimonial_rating' => 5,
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Grovemark Storefront',
                'slug' => 'grovemark-storefront',
                'category' => 'e-commerce',
                'client_name' => 'Grovemark',
                'industry' => 'E-Commerce, Retail',
                'location' => 'United Kingdom',
                'duration' => '3 Months',
                'team_size' => '4 Members',
                'completed_date' => '2026-01-20',
                'short_description' => 'Headless commerce storefront handling seasonal traffic spikes.',
                'description' => 'A headless storefront decoupled from the commerce backend, built to absorb seasonal traffic spikes without over-provisioning infrastructure.',
                'challenge' => 'Grovemark\'s monolithic storefront repeatedly crashed during Black Friday and holiday sales, and their marketing team couldn\'t update campaign pages without a developer.',
                'solution' => 'We decoupled the storefront from the commerce backend with a headless architecture, added Redis-backed caching and queue-based order processing, and gave marketing a page-builder.',
                'technologies' => ['Laravel', 'React', 'MySQL', 'Redis'],
                'highlights' => [
                    ['icon' => 'bi-lightning-charge', 'text' => 'Headless storefront decoupled from the commerce backend'],
                    ['icon' => 'bi-graph-up', 'text' => 'Auto-scaling checkout for seasonal traffic spikes'],
                    ['icon' => 'bi-palette', 'text' => 'Marketing-editable campaign pages with no developer needed'],
                    ['icon' => 'bi-search', 'text' => 'Faceted product search and filtering'],
                ],
                'results' => [
                    ['icon' => 'bi-graph-up-arrow', 'value' => '150%', 'label' => 'Increase in Online Sales'],
                    ['icon' => 'bi-lightning-charge', 'value' => '80%', 'label' => 'Increase in Traffic'],
                    ['icon' => 'bi-arrow-up-right-circle', 'value' => '45%', 'label' => 'Improvement in Conversion'],
                    ['icon' => 'bi-emoji-smile', 'value' => '95%', 'label' => 'Client Satisfaction'],
                ],
                'testimonial_quote' => 'CodexeHub delivered an exceptional e-commerce platform that exceeded our expectations. Their team was professional, responsive and highly skilled.',
                'testimonial_name' => 'Rashed Hossain',
                'testimonial_role' => 'CEO, Grovemark',
                'testimonial_rating' => 5,
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Meridian Health Portal',
                'slug' => 'meridian-health-portal',
                'category' => 'web-application',
                'client_name' => 'Meridian Health',
                'location' => 'Canada',
                'duration' => '5 Months',
                'team_size' => '6 Members',
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
                'location' => 'Australia',
                'duration' => '6 Months',
                'team_size' => '7 Members',
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
                'location' => 'Singapore',
                'duration' => '4 Months',
                'team_size' => '5 Members',
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
                'location' => 'Bangladesh',
                'duration' => '3 Months',
                'team_size' => '4 Members',
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
                    'industry' => $data['industry'] ?? null,
                    'location' => $data['location'],
                    'duration' => $data['duration'],
                    'team_size' => $data['team_size'],
                    'completed_date' => $data['completed_date'] ?? null,
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'challenge' => $data['challenge'] ?? null,
                    'solution' => $data['solution'] ?? null,
                    'testimonial_quote' => $data['testimonial_quote'] ?? null,
                    'testimonial_name' => $data['testimonial_name'] ?? null,
                    'testimonial_role' => $data['testimonial_role'] ?? null,
                    'testimonial_rating' => $data['testimonial_rating'] ?? null,
                    'featured' => $data['featured'],
                    'status' => 'active',
                    'sort_order' => $data['sort_order'],
                ]
            );

            $portfolio->technologies()->delete();
            foreach ($data['technologies'] as $technologyName) {
                $portfolio->technologies()->create(['technology_name' => $technologyName]);
            }

            $portfolio->highlights()->delete();
            foreach ($data['highlights'] ?? [] as $index => $highlight) {
                $portfolio->highlights()->create([...$highlight, 'sort_order' => $index]);
            }

            $portfolio->results()->delete();
            foreach ($data['results'] ?? [] as $index => $result) {
                $portfolio->results()->create([...$result, 'sort_order' => $index]);
            }
        }

        PortfolioSetting::current();

        $this->command?->info('Portfolio categories, projects and homepage settings seeded.');
    }
}
