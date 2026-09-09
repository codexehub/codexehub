<?php

namespace Tests\Unit;

use App\Models\Customer;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function test_status_constants_are_the_known_set(): void
    {
        $this->assertSame(
            ['prospect', 'active', 'inactive', 'suspended'],
            Customer::STATUSES,
        );
    }

    public function test_email_and_company_name_are_mass_assignable(): void
    {
        $customer = new Customer([
            'name' => 'Acme',
            'company_name' => 'Acme Inc',
            'email' => 'ops@acme.test',
            'phone' => '123',
            'status' => Customer::STATUS_PROSPECT,
            'notes' => 'hi',
        ]);

        $this->assertSame('Acme Inc', $customer->company_name);
        $this->assertSame('ops@acme.test', $customer->email);
        $this->assertSame('prospect', $customer->status);
    }
}
