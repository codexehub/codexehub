<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Services\Feature\FeatureDependencyResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin CRUD for the reusable feature catalog (FEAT-002; SRS §7 / §79).
 *
 * Gated by the control-plane `features.*` permissions — `features.view` to read,
 * `features.manage` for every write. Package Manager holds both (SRS §105).
 */
class FeatureController extends Controller implements HasMiddleware
{
    public function __construct(private readonly FeatureDependencyResolver $resolver) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:features.view', only: ['index']),
            new Middleware('can:features.manage', only: ['create', 'store', 'edit', 'update', 'destroy', 'updateStatus']),
        ];
    }

    public function index(Request $request): View
    {
        $sort = in_array($request->get('sort'), ['name', 'key', 'type', 'status', 'default_price', 'created_at'], true)
            ? $request->get('sort')
            : 'name';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        $features = Feature::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            })
            ->when(
                $request->filled('status') && in_array($request->get('status'), Feature::STATUSES, true),
                fn ($query) => $query->where('status', $request->get('status'))
            )
            ->when(
                $request->filled('type') && in_array($request->get('type'), Feature::TYPES, true),
                fn ($query) => $query->where('type', $request->get('type'))
            )
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.features.index', compact('features', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.pages.features.create', [
            'feature' => new Feature(['status' => Feature::STATUS_ACTIVE, 'type' => Feature::TYPE_MODULE, 'version' => '1.0.0']),
            'featureOptions' => Feature::query()->orderBy('name')->get(['name', 'key']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Feature::create($this->validated($request));

        return redirect()->route('admin.features.index')->with('status', 'Feature created.');
    }

    public function edit(Feature $feature): View
    {
        return view('admin.pages.features.edit', [
            'feature' => $feature,
            'featureOptions' => Feature::query()->where('id', '!=', $feature->id)->orderBy('name')->get(['name', 'key']),
            'dependents' => $this->dependentsOf($feature),
        ]);
    }

    public function update(Request $request, Feature $feature): RedirectResponse
    {
        $feature->update($this->validated($request, $feature));

        return redirect()->route('admin.features.index')->with('status', 'Feature updated.');
    }

    public function updateStatus(Request $request, Feature $feature): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in(Feature::STATUSES)]]);

        $feature->update($validated);

        return back()->with('status', "{$feature->name} is now ".ucfirst($validated['status']).'.');
    }

    public function destroy(Feature $feature): RedirectResponse
    {
        $dependents = $this->dependentsOf($feature);

        if ($dependents->isNotEmpty()) {
            return back()->withErrors(['feature' => "\"{$feature->name}\" is a dependency of: ".$dependents->pluck('name')->join(', ').'. Remove those links first.']);
        }

        $feature->delete();

        return back()->with('status', 'Feature deleted.');
    }

    /**
     * Validate the request and return the clean attribute set for create/update.
     * Dependency + config-schema rules run in an `after` hook so their messages
     * land on the right fields.
     */
    private function validated(Request $request, ?Feature $feature = null): array
    {
        $request->merge([
            'key' => filled($request->input('key'))
                ? Str::slug($request->input('key'), '_')
                : ($feature?->key ?? Str::slug((string) $request->input('name'), '_')),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/', Rule::unique('features', 'key')->ignore($feature?->id)],
            'type' => ['required', Rule::in(Feature::TYPES)],
            'status' => ['required', Rule::in(Feature::STATUSES)],
            'description' => ['nullable', 'string'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'version' => ['required', 'string', 'max:20'],
            'documentation' => ['nullable', 'string'],
            'dependencies' => ['nullable', 'array'],
            'dependencies.*' => ['string'],
            'config_schema' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validateConfigSchema($validator, (string) $request->input('config_schema'));
            $this->validateDependencies($validator, $request->input('key'), $request->input('dependencies', []));
        });

        $validator->validate();

        $data = $validator->safe()->only(['name', 'key', 'type', 'status', 'description', 'version', 'documentation']);
        $data['default_price'] = $request->input('default_price') ?: 0;
        $data['dependencies'] = $this->cleanDependencyKeys($request->input('dependencies', [])) ?: null;

        $schema = trim((string) $request->input('config_schema'));
        $data['config_schema'] = $schema === '' ? null : json_decode($schema, true);

        return $data;
    }

    private function validateConfigSchema(\Illuminate\Contracts\Validation\Validator $validator, string $raw): void
    {
        $raw = trim($raw);

        if ($raw === '') {
            return;
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $validator->errors()->add('config_schema', 'Configuration schema must be valid JSON.');

            return;
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            $validator->errors()->add('config_schema', 'Configuration schema must be a JSON object, e.g. {"type":"object","properties":{…}}.');

            return;
        }

        if (! isset($decoded['type']) && ! isset($decoded['properties'])) {
            $validator->errors()->add('config_schema', 'Configuration schema should declare a "type" or "properties" key.');
        }
    }

    private function validateDependencies(\Illuminate\Contracts\Validation\Validator $validator, ?string $key, array $raw): void
    {
        $keys = $this->cleanDependencyKeys($raw);

        if ($keys === []) {
            return;
        }

        if ($key !== null && in_array($key, $keys, true)) {
            $validator->errors()->add('dependencies', 'A feature cannot depend on itself.');

            return;
        }

        if ($missing = $this->resolver->missing($keys)) {
            $validator->errors()->add('dependencies', 'Unknown feature key(s): '.implode(', ', $missing).'.');

            return;
        }

        if ($key !== null && ($cycle = $this->resolver->detectCycle($key, $keys))) {
            $validator->errors()->add('dependencies', 'Circular dependency: '.implode(' → ', $cycle).'.');
        }
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return list<string>
     */
    private function cleanDependencyKeys(array $raw): array
    {
        return collect($raw)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Catalog rows that declare $feature as a direct dependency.
     *
     * @return Collection<int, Feature>
     */
    private function dependentsOf(Feature $feature): Collection
    {
        return Feature::query()
            ->where('id', '!=', $feature->id)
            ->get(['name', 'key', 'dependencies'])
            ->filter(fn (Feature $other) => in_array($feature->key, $other->dependencies ?? [], true))
            ->values();
    }
}
