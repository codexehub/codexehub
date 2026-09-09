<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_is_admin_is_true_only_for_the_admin_role(): void
    {
        $this->assertTrue((new User(['role' => 'admin']))->isAdmin());
        $this->assertFalse((new User(['role' => 'user']))->isAdmin());
        $this->assertFalse((new User)->isAdmin());
    }

    public function test_is_staff_is_true_for_the_legacy_admin_string_role(): void
    {
        // The role-relation branch of isStaff() is covered in
        // Feature/Admin/RolesPermissionScreenTest (needs the DB).
        $this->assertTrue((new User(['role' => 'admin']))->isStaff());
    }
}
