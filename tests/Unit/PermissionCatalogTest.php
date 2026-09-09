<?php

namespace Tests\Unit;

use App\Support\Authorization\PermissionCatalog;
use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    public function test_every_permission_key_is_unique(): void
    {
        $all = PermissionCatalog::all();

        $this->assertSame($all, array_values(array_unique($all)));
    }

    public function test_each_key_is_prefixed_with_its_group(): void
    {
        foreach (PermissionCatalog::groups() as $group => $permissions) {
            foreach (array_keys($permissions) as $name) {
                $this->assertStringStartsWith("{$group}.", $name);
                $this->assertSame($group, PermissionCatalog::groupFor($name));
            }
        }
    }

    public function test_labels_has_a_label_for_every_key(): void
    {
        $keys = PermissionCatalog::all();
        $labelled = array_keys(PermissionCatalog::labels());

        sort($keys);
        sort($labelled);

        $this->assertSame($keys, $labelled);
    }

    public function test_group_for_returns_null_for_an_unknown_permission(): void
    {
        $this->assertNull(PermissionCatalog::groupFor('nope.nothing'));
    }
}
