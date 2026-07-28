<?php

namespace Database\Seeders;

use App\Models\Consultation;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consultations = [
            [
                'full_name' => 'Rafiul Islam', 'company_name' => 'Nimbus Retail', 'email' => 'rafiul@nimbusretail.com',
                'phone' => '+8801711000111', 'country' => 'Bangladesh', 'city' => 'Dhaka', 'language' => 'English',
                'service' => 'Web Development', 'project_type' => 'Small Business', 'budget' => '$5,000–$10,000', 'timeline' => 'Within 1 Month',
                'description' => 'We need a new e-commerce storefront with inventory sync to our POS system.',
                'contact_method' => 'Email', 'status' => 'new', 'priority' => 'high',
            ],
            [
                'full_name' => 'Sarah Thompson', 'company_name' => 'Grovemark Logistics', 'email' => 'sarah.t@grovemark.io',
                'phone' => '+14155550110', 'country' => 'United States', 'city' => 'San Francisco', 'language' => 'English',
                'service' => 'AI Automation', 'project_type' => 'Enterprise', 'budget' => 'Above $25,000', 'timeline' => 'Immediately',
                'description' => 'Looking to automate our freight-quoting workflow with an AI-assisted pipeline.',
                'contact_method' => 'Google Meet', 'status' => 'contacted', 'priority' => 'urgent',
            ],
            [
                'full_name' => 'Tanvir Ahmed', 'company_name' => null, 'email' => 'tanvir.ahmed@gmail.com',
                'phone' => '+8801912345678', 'country' => 'Bangladesh', 'city' => 'Chattogram', 'language' => 'Bangla',
                'service' => 'Mobile App Development', 'project_type' => 'Startup', 'budget' => '$1,000–$5,000', 'timeline' => '1–3 Months',
                'description' => 'MVP for a ride-sharing app, Android first, iOS to follow.',
                'contact_method' => 'WhatsApp', 'status' => 'meeting_scheduled', 'priority' => 'medium',
            ],
            [
                'full_name' => 'Elena Novak', 'company_name' => 'BrightPath NGO', 'email' => 'elena@brightpath.org',
                'phone' => '+491701234567', 'country' => 'Germany', 'city' => 'Berlin', 'language' => 'English',
                'service' => 'UI/UX Design', 'project_type' => 'NGO', 'budget' => 'Under $1,000', 'timeline' => 'Flexible',
                'description' => 'Redesign of our donor portal to improve conversion and accessibility.',
                'contact_method' => 'Zoom', 'status' => 'won', 'priority' => 'low',
            ],
            [
                'full_name' => 'Michael Chen', 'company_name' => 'Verity Cloud', 'email' => 'mchen@veritycloud.dev',
                'phone' => '+16045550199', 'country' => 'Canada', 'city' => 'Vancouver', 'language' => 'English',
                'service' => 'Cloud & DevOps', 'project_type' => 'Enterprise', 'budget' => '$10,000–$25,000', 'timeline' => '3–6 Months',
                'description' => 'Migrating our monolith to a containerized microservices architecture on AWS.',
                'contact_method' => 'Phone Call', 'status' => 'proposal_sent', 'priority' => 'high',
            ],
        ];

        foreach ($consultations as $index => $data) {
            $consultation = Consultation::query()->firstOrNew(['email' => $data['email']]);

            if ($consultation->exists) {
                continue;
            }

            $nextId = (Consultation::max('id') ?? 0) + 1;

            $consultation->fill($data);
            $consultation->source = 'website';
            $consultation->reference_no = 'CH-'.str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
            $consultation->save();
        }
    }
}
