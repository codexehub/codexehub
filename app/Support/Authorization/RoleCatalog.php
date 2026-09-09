<?php

namespace App\Support\Authorization;

use App\Models\Role;

/**
 * The SRS §105 admin roles and their least-privilege permission sets, drawn
 * from PermissionCatalog. `super-admin` is `'*'` — every permission.
 *
 * This is the source of truth for RolePermissionSeeder and (later) the admin
 * Roles & Permission screen (RBAC-003).
 */
class RoleCatalog
{
    /**
     * @return array<string, array{label: string, description: string, permissions: list<string>|'*'}>
     */
    public static function roles(): array
    {
        return [
            Role::SUPER_ADMIN => [
                'label' => 'Super Admin',
                'description' => 'Full, unrestricted access to the control plane.',
                'permissions' => '*',
            ],

            'sales-manager' => [
                'label' => 'Sales Manager',
                'description' => 'Owns the customer relationship — customers, offers and requirement intake.',
                'permissions' => [
                    'customers.view', 'customers.manage',
                    'products.view', 'features.view', 'services.view',
                    'packages.view',
                    'offers.view', 'offers.manage', 'offers.send',
                    'invoices.view',
                    'payments.view',
                    'requirements.view', 'requirements.manage',
                    'reports.view',
                ],
            ],

            'package-manager' => [
                'label' => 'Package Manager',
                'description' => 'Maintains the product / feature / service catalogs and builds customer packages.',
                'permissions' => [
                    'customers.view',
                    'products.view', 'products.manage',
                    'features.view', 'features.manage',
                    'services.view', 'services.manage',
                    'packages.view', 'packages.manage',
                    'offers.view',
                    'requirements.view',
                    'releases.view',
                    'reports.view',
                ],
            ],

            'finance-manager' => [
                'label' => 'Finance Manager',
                'description' => 'Invoicing, payments and ownership completion.',
                'permissions' => [
                    'customers.view',
                    'packages.view',
                    'offers.view',
                    'invoices.view', 'invoices.manage',
                    'payments.view', 'payments.record', 'payments.approve',
                    'licenses.view',
                    'ownership.view', 'ownership.complete',
                    'reports.view',
                ],
            ],

            'developer' => [
                'label' => 'Developer',
                'description' => 'Custom development against customer requirements, up to release creation.',
                'permissions' => [
                    'customers.view',
                    'packages.view',
                    'features.view',
                    'requirements.view', 'requirements.manage',
                    'developments.view', 'developments.manage',
                    'releases.view', 'releases.manage',
                    'installations.view',
                    'deployments.view',
                ],
            ],

            'qa' => [
                'label' => 'QA',
                'description' => 'Verifies developments and release candidates; no publish or deploy rights.',
                'permissions' => [
                    'requirements.view',
                    'developments.view',
                    'releases.view', 'releases.manage',
                    'deployments.view',
                    'installations.view',
                ],
            ],

            'deployment-manager' => [
                'label' => 'Deployment Manager',
                'description' => 'Publishes releases and authorizes deployments to customer installations.',
                'permissions' => [
                    'customers.view',
                    'packages.view',
                    'releases.view', 'releases.publish',
                    'deployments.view', 'deployments.authorize',
                    'installations.view', 'installations.manage',
                    'licenses.view',
                ],
            ],

            'support' => [
                'label' => 'Support',
                'description' => 'Customer support — read access across the account plus requirement intake.',
                'permissions' => [
                    'customers.view',
                    'packages.view',
                    'offers.view',
                    'invoices.view',
                    'payments.view',
                    'requirements.view', 'requirements.manage',
                    'licenses.view',
                    'installations.view',
                ],
            ],
        ];
    }

    /**
     * Resolved permission list for a role ('*' expanded to every catalog key).
     *
     * @return list<string>
     */
    public static function permissionsFor(string $role): array
    {
        $permissions = static::roles()[$role]['permissions'] ?? [];

        return $permissions === '*' ? PermissionCatalog::all() : $permissions;
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(static::roles());
    }
}
