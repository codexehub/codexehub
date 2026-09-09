<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Customer;
use App\Models\Feature;
use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use App\Models\LicensedAppPackage;
use App\Models\LicenseTransaction;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class AuditLogController extends Controller implements HasMiddleware
{
    /**
     * Entity types that carry the Auditable trait, keyed by the class basename
     * used in the URL / filter dropdown. Add a row here when a new model starts
     * auditing so it becomes filterable.
     *
     * @var array<string, class-string>
     */
    private const ENTITY_TYPES = [
        'Customer' => Customer::class,
        'User' => User::class,
        'Role' => Role::class,
        'Product' => Product::class,
        'ProductCategory' => ProductCategory::class,
        'Feature' => Feature::class,
        'Service' => Service::class,
        'Portfolio' => Portfolio::class,
        'PortfolioCategory' => PortfolioCategory::class,
        'TeamMember' => TeamMember::class,
        'Consultation' => Consultation::class,
        'LicensedApp' => LicensedApp::class,
        'LicensedAppPackage' => LicensedAppPackage::class,
        'License' => License::class,
        'LicenseCustomer' => LicenseCustomer::class,
        'LicenseTransaction' => LicenseTransaction::class,
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('can:audit.view', only: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $entityType = self::ENTITY_TYPES[$request->get('entity_type')] ?? null;

        $logs = AuditLog::query()
            ->with('actor')
            ->when($request->filled('action'), fn (Builder $q) => $q->where('action', $request->get('action')))
            ->when($entityType, fn (Builder $q) => $q->where('entity_type', $entityType))
            ->when($request->filled('actor'), function (Builder $q) use ($request) {
                $actor = $request->get('actor');
                $actor === 'system'
                    ? $q->whereNull('actor_id')
                    : $q->where('actor_type', User::class)->where('actor_id', $actor);
            })
            ->when($request->filled('date_from'), fn (Builder $q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('created_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.pages.audit.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'entityTypes' => array_keys(self::ENTITY_TYPES),
            'actors' => User::query()
                ->whereIn('id', AuditLog::query()->where('actor_type', User::class)->select('actor_id')->distinct())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
