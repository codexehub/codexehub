<?php

namespace App\Support\Authorization;

/**
 * The single source of truth for every permission the Codexehub control plane
 * recognises. Grouped by module; the group is stored on the row and drives the
 * admin Roles & Permission screen (RBAC-003).
 *
 * Control-plane keys use a plural-noun group (`customers`, `packages`, …).
 * The two singular groups at the end (`team` / `licensing`) gate the pre-SRS
 * marketing + licensing admin modules — folded in here by RBAC-003 so every
 * admin module is permission-backed, not `isAdmin()`-backed. (The `product`
 * group was retired by PROD-002 — `Admin\Product*Controller` now uses the
 * control-plane `products.view` / `products.manage` keys, since the marketing
 * `products` table is also the SRS §52 control-plane product.)
 *
 * Gates are wired for every key in AppServiceProvider::boot(); a key may exist
 * before the module that checks it does.
 */
class PermissionCatalog
{
    /**
     * @return array<string, array<string, string>> group => [ name => label ]
     */
    public static function groups(): array
    {
        return [
            'roles' => [
                'roles.view' => 'View roles & permissions',
                'roles.manage' => 'Create, edit and assign roles',
            ],
            'customers' => [
                'customers.view' => 'View customers',
                'customers.manage' => 'Create and edit customers',
            ],
            'products' => [
                'products.view' => 'View products',
                'products.manage' => 'Create and edit products',
            ],
            'features' => [
                'features.view' => 'View the feature catalog',
                'features.manage' => 'Create and edit features',
            ],
            'services' => [
                'services.view' => 'View the service catalog',
                'services.manage' => 'Create and edit services',
            ],
            'packages' => [
                'packages.view' => 'View customer packages',
                'packages.manage' => 'Build and amend customer packages',
            ],
            'offers' => [
                'offers.view' => 'View package offers',
                'offers.manage' => 'Create and edit offers',
                'offers.send' => 'Send offers to customers',
            ],
            'invoices' => [
                'invoices.view' => 'View invoices',
                'invoices.manage' => 'Create and edit invoices',
            ],
            'payments' => [
                'payments.view' => 'View payments',
                'payments.record' => 'Record payments',
                'payments.approve' => 'Approve / reject payments',
            ],
            'requirements' => [
                'requirements.view' => 'View customer requirements',
                'requirements.manage' => 'Create and edit requirements',
            ],
            'developments' => [
                'developments.view' => 'View custom developments',
                'developments.manage' => 'Create and edit custom developments',
            ],
            'releases' => [
                'releases.view' => 'View releases',
                'releases.manage' => 'Create and edit releases',
                'releases.publish' => 'Publish a release to production',
            ],
            'deployments' => [
                'deployments.view' => 'View deployments',
                'deployments.authorize' => 'Authorize a deployment',
            ],
            'licenses' => [
                'licenses.view' => 'View licenses',
                'licenses.manage' => 'Issue, suspend, reactivate and revoke licenses',
            ],
            'installations' => [
                'installations.view' => 'View installations',
                'installations.manage' => 'Manage installation bindings',
            ],
            'ownership' => [
                'ownership.view' => 'View ownership records',
                'ownership.complete' => 'Complete an ownership transfer',
            ],
            'audit' => [
                'audit.view' => 'View the audit log',
            ],
            'settings' => [
                'settings.view' => 'View settings',
                'settings.manage' => 'Change settings',
            ],
            'reports' => [
                'reports.view' => 'View reports',
            ],

            // Pre-SRS admin modules (RBAC-003). Singular keys — the controllers
            // (`Admin\TeamController`, `Admin\Licensing\*`) already check these
            // ability names.
            'team' => [
                'team.view' => 'View team members',
                'team.create' => 'Create team members',
                'team.edit' => 'Edit team members',
                'team.delete' => 'Delete team members',
            ],
            'licensing' => [
                'licensing.view' => 'View the licensing module',
                'licensing.create' => 'Create licensing records',
                'licensing.edit' => 'Edit licensing records',
                'licensing.delete' => 'Delete licensing records',
            ],
        ];
    }

    /**
     * Every permission key, flat.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_merge(...array_map('array_keys', array_values(static::groups())));
    }

    /**
     * @return array<string, string> name => label, flat
     */
    public static function labels(): array
    {
        return array_merge(...array_values(static::groups()));
    }

    public static function groupFor(string $permission): ?string
    {
        foreach (static::groups() as $group => $permissions) {
            if (array_key_exists($permission, $permissions)) {
                return $group;
            }
        }

        return null;
    }
}
