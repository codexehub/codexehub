<?php

namespace App\Services\Feature;

use App\Models\Feature;
use Illuminate\Support\Collection;

/**
 * Resolves the dependency graph of the feature catalog (FEAT-002, SRS §7).
 *
 * Every {@see Feature} stores a plain list of feature *keys* it requires. This
 * service answers the three questions the admin CRUD needs before it can save a
 * feature: are the referenced keys real, would the new edge set introduce a
 * cycle, and what is the full transitive set a package must also enable.
 */
class FeatureDependencyResolver
{
    /**
     * Keys in $keys that have no matching catalog row.
     *
     * @param  list<string>  $keys
     * @return list<string>
     */
    public function missing(array $keys): array
    {
        $keys = array_values(array_unique(array_filter($keys)));

        if ($keys === []) {
            return [];
        }

        $known = Feature::query()->whereIn('key', $keys)->pluck('key')->all();

        return array_values(array_diff($keys, $known));
    }

    /**
     * Would making $featureKey depend on $dependencyKeys create a cycle anywhere
     * in the catalog graph? Returns the offending path (e.g. `['a','b','a']`) or
     * null when the graph stays acyclic. The proposed edges replace whatever
     * $featureKey currently declares.
     *
     * @param  list<string>  $dependencyKeys
     * @return list<string>|null
     */
    public function detectCycle(string $featureKey, array $dependencyKeys): ?array
    {
        $graph = $this->graph();
        $graph[$featureKey] = array_values(array_unique(array_filter($dependencyKeys)));

        $inStack = [];
        $done = [];
        $path = [];

        $visit = function (string $node) use (&$visit, &$graph, &$inStack, &$done, &$path): ?array {
            $inStack[$node] = true;
            $path[] = $node;

            foreach ($graph[$node] ?? [] as $next) {
                if (! empty($inStack[$next])) {
                    return [...$path, $next];
                }

                if (empty($done[$next]) && array_key_exists($next, $graph)) {
                    if ($cycle = $visit($next)) {
                        return $cycle;
                    }
                }
            }

            array_pop($path);
            unset($inStack[$node]);
            $done[$node] = true;

            return null;
        };

        return $visit($featureKey);
    }

    /**
     * The full transitive set of catalog rows $feature depends on, directly or
     * indirectly (the feature itself is never included). A missing / dangling
     * key is silently skipped — {@see missing()} is the guard for that.
     *
     * @return Collection<int, Feature>
     */
    public function resolve(Feature $feature): Collection
    {
        $catalog = Feature::query()->get()->keyBy('key');
        $resolved = collect();
        $seen = [];
        $stack = $feature->dependencies ?? [];

        while ($stack !== []) {
            $key = array_pop($stack);

            if (isset($seen[$key]) || $key === $feature->key) {
                continue;
            }

            $seen[$key] = true;

            if ($dependency = $catalog->get($key)) {
                $resolved->push($dependency);

                foreach ($dependency->dependencies ?? [] as $next) {
                    $stack[] = $next;
                }
            }
        }

        return $resolved;
    }

    /**
     * Adjacency list of the whole catalog: key => list<dependency key>.
     *
     * @return array<string, list<string>>
     */
    private function graph(): array
    {
        return Feature::query()
            ->get(['key', 'dependencies'])
            ->mapWithKeys(fn (Feature $f) => [$f->key => array_values($f->dependencies ?? [])])
            ->all();
    }
}
