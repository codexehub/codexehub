<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['slug' => 'web-development', 'icon' => 'bi-code-slash', 'title' => 'Web Development', 'short_description' => 'Custom web applications using modern technologies like Laravel, React and Next.js and more.', 'featured' => true],
            ['slug' => 'mobile-apps', 'icon' => 'bi-phone', 'title' => 'Mobile App Development', 'short_description' => 'Cross-platform mobile apps for iOS and Android that engage users and drive growth.', 'featured' => true],
            ['slug' => 'ai-automation', 'icon' => 'bi-cpu', 'title' => 'AI & Automation', 'short_description' => 'AI solutions, chatbots, workflow automation and intelligent systems to scale your business.', 'featured' => true],
            ['slug' => 'cloud-devops', 'icon' => 'bi-cloud-arrow-up', 'title' => 'Cloud & DevOps', 'short_description' => 'Scalable cloud infrastructure, CI/CD pipelines and DevOps to ensure reliability and flexibility.', 'featured' => true],
            ['slug' => 'ui-ux-design', 'icon' => 'bi-palette', 'title' => 'UI/UX Design', 'short_description' => 'Beautiful, user-centric designs that provide great experiences and drive engagement.', 'featured' => true],
            ['slug' => 'ecommerce-solutions', 'icon' => 'bi-cart3', 'title' => 'E-Commerce Solutions', 'short_description' => 'Feature-rich e-commerce platforms with secure payments and powerful admin systems.', 'featured' => false],
            ['slug' => 'erp-crm-solutions', 'icon' => 'bi-diagram-3', 'title' => 'ERP & CRM Solutions', 'short_description' => 'Custom ERP and CRM systems to streamline operations and improve business efficiency.', 'featured' => false],
            ['slug' => 'maintenance-support', 'icon' => 'bi-headset', 'title' => 'Maintenance & Support', 'short_description' => 'Ongoing support, updates and optimization to keep your systems secure and fast.', 'featured' => true],
        ];

        foreach ($services as $index => $service) {
            Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'icon' => $service['icon'],
                    'title' => $service['title'],
                    'short_description' => $service['short_description'],
                    'status' => 'active',
                    'featured' => $service['featured'],
                    'sort_order' => $index,
                ]
            );
        }
    }
}
