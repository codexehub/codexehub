<?php

namespace Tests\Feature\Foundation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The public marketing pages render against an empty database — every
     * data-backed section must degrade to an empty state, never error.
     */
    #[DataProvider('publicRoutes')]
    public function test_public_page_renders(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public static function publicRoutes(): array
    {
        return [
            'home' => ['/'],
            'services' => ['/services'],
            'products (software)' => ['/products/software'],
            'products (hardware)' => ['/products/hardware'],
            'about' => ['/about-us'],
            'portfolio' => ['/portfolio'],
            'contact' => ['/contact'],
        ];
    }
}
