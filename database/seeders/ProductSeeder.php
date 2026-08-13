<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['slug' => 'erp-solutions', 'name' => 'ERP Solutions'],
            ['slug' => 'pos-retail', 'name' => 'POS & Retail'],
            ['slug' => 'saas-tools', 'name' => 'SaaS Tools'],
            ['slug' => 'hr-payroll', 'name' => 'HR & Payroll'],
        ];

        foreach ($categories as $index => $category) {
            ProductCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'type' => Product::TYPE_SOFTWARE,
                    'sort_order' => $index,
                ]
            );
        }

        $categoryIds = ProductCategory::query()->pluck('id', 'slug');

        $products = [
            [
                'slug' => 'codexe-erp',
                'category' => 'erp-solutions',
                'icon' => 'bi-diagram-3',
                'title' => 'CodexeERP',
                'tagline' => 'One system for accounts, inventory and operations',
                'short_description' => 'A modular ERP that connects accounting, inventory, purchasing and reporting in a single dashboard.',
                'description' => "CodexeERP gives growing businesses a single source of truth. Accounting, inventory, purchase, sales and HR modules all share one database, so your reports are always current and your team stops re-keying the same data into three systems.\n\nEvery module can be switched on independently, so you start with what you need today and expand as you grow. The platform ships with role-based access, a full audit trail and an API for connecting the tools you already use.",
                'price' => 1200,
                'price_unit' => 'one-time',
                'pricing_note' => 'Includes 12 months of updates and priority support.',
                'demo_url' => 'https://demo.codexehub.com/erp',
                'version' => '4.2',
                'featured' => true,
                'features' => [
                    ['bi-calculator', 'Double-entry accounting with automated journals'],
                    ['bi-boxes', 'Multi-warehouse inventory and stock transfers'],
                    ['bi-receipt', 'Purchase, sales and quotation workflows'],
                    ['bi-bar-chart', 'Real-time dashboards and exportable reports'],
                    ['bi-shield-lock', 'Role-based permissions and full audit trail'],
                    ['bi-plug', 'REST API for third-party integrations'],
                ],
            ],
            [
                'slug' => 'codexe-pos',
                'category' => 'pos-retail',
                'icon' => 'bi-shop',
                'title' => 'CodexePOS',
                'tagline' => 'Fast retail checkout that works offline',
                'short_description' => 'A retail point-of-sale system with barcode checkout, offline mode and live sales reporting.',
                'description' => "CodexePOS keeps the counter moving even when the internet does not. Sales are recorded locally and sync automatically the moment the connection returns, so a dropped line never costs you a transaction.\n\nBuilt for shops with one till or fifty, it handles barcode scanning, split payments, returns, discounts and customer loyalty out of the box, with daily sales and stock reports for every branch.",
                'price' => 650,
                'price_unit' => 'one-time',
                'pricing_note' => 'Per outlet. Volume pricing available for chains.',
                'demo_url' => 'https://demo.codexehub.com/pos',
                'version' => '3.0',
                'featured' => true,
                'features' => [
                    ['bi-upc-scan', 'Barcode scanning and quick-key product grid'],
                    ['bi-wifi-off', 'Offline mode with automatic sync'],
                    ['bi-credit-card', 'Split payments, returns and discounts'],
                    ['bi-people', 'Customer profiles and loyalty points'],
                    ['bi-shop-window', 'Multi-outlet stock and sales reporting'],
                ],
            ],
            [
                'slug' => 'codexe-hrm',
                'category' => 'hr-payroll',
                'icon' => 'bi-person-badge',
                'title' => 'CodexeHRM',
                'tagline' => 'Attendance, leave and payroll in one place',
                'short_description' => 'HR and payroll software covering attendance, leave, payroll runs and employee self-service.',
                'description' => "CodexeHRM replaces the spreadsheet stack most HR teams still run on. Attendance flows in from biometric devices or the mobile app, leave requests route through your approval chain, and payroll is calculated from the same records — no manual reconciliation at month end.\n\nEmployees get a self-service portal for payslips, leave balances and document requests, which removes most of the routine questions from the HR inbox.",
                'price' => 900,
                'price_unit' => 'one-time',
                'pricing_note' => 'Unlimited employees. Optional annual support plan.',
                'version' => '2.5',
                'featured' => true,
                'features' => [
                    ['bi-fingerprint', 'Biometric and mobile attendance capture'],
                    ['bi-calendar-check', 'Configurable leave types and approval chains'],
                    ['bi-cash-stack', 'Automated payroll runs with payslip generation'],
                    ['bi-person-workspace', 'Employee self-service portal'],
                    ['bi-file-earmark-text', 'Statutory and custom HR reports'],
                ],
            ],
            [
                'slug' => 'codexe-desk',
                'category' => 'saas-tools',
                'icon' => 'bi-headset',
                'title' => 'CodexeDesk',
                'tagline' => 'Support ticketing your customers actually like',
                'short_description' => 'A helpdesk platform with email-to-ticket, SLA tracking and a customer knowledge base.',
                'description' => "CodexeDesk turns scattered support email into a queue your team can actually manage. Tickets are created from email, the web widget or the API, routed by rules you define, and tracked against SLA targets so nothing quietly ages out.\n\nA built-in knowledge base deflects the repeat questions, and the reporting view shows first-response time, resolution time and agent workload at a glance.",
                'price' => 39,
                'price_unit' => 'per month',
                'pricing_note' => 'Per agent, billed annually. 14-day free trial.',
                'demo_url' => 'https://demo.codexehub.com/desk',
                'version' => '1.8',
                'featured' => false,
                'features' => [
                    ['bi-envelope-open', 'Email-to-ticket with threaded replies'],
                    ['bi-stopwatch', 'SLA policies and breach alerts'],
                    ['bi-book', 'Self-service knowledge base'],
                    ['bi-diagram-2', 'Automation rules and smart routing'],
                    ['bi-graph-up', 'Agent performance and response-time reports'],
                ],
            ],
        ];

        foreach ($products as $index => $data) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'type' => Product::TYPE_SOFTWARE,
                    'product_category_id' => $categoryIds[$data['category']] ?? null,
                    'icon' => $data['icon'],
                    'title' => $data['title'],
                    'tagline' => $data['tagline'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'price_unit' => $data['price_unit'],
                    'pricing_note' => $data['pricing_note'] ?? null,
                    'demo_url' => $data['demo_url'] ?? null,
                    'version' => $data['version'] ?? null,
                    'status' => 'active',
                    'featured' => $data['featured'],
                    'sort_order' => $index,
                ]
            );

            $product->features()->delete();

            foreach ($data['features'] as $position => [$icon, $text]) {
                $product->features()->create([
                    'icon' => $icon,
                    'text' => $text,
                    'sort_order' => $position,
                ]);
            }
        }
    }
}
