<?php

namespace App\Support;

final class NavigationTree
{
    /** Preserve necessary ancestors without turning them into authorized links. */
    public static function filter(array $rows, callable $allows): array
    {
        $prune = function (array $node) use (&$prune, $allows): ?array {
            $node['children'] = array_values(array_filter(array_map($prune, $node['children'] ?? [])));
            if (! $allows($node)) {
                $node['route'] = '#';
                $node['href'] = null;
                return $node['children'] ? $node : null;
            }

            return $node;
        };

        return array_values(array_filter(array_map($prune, MenuTree::build($rows, true))));
    }

    public static function groups(array $tree, array $labels, array $roles): array
    {
        $groups = [];
        // Cross-area descendants are extracted only for presentation; DB parents stay intact.
        $split = function (array $node, ?string $parentGroup = null) use (&$split, &$groups, $labels): ?array {
            $group = isset($labels[$node['group_code'] ?? '']) ? $node['group_code'] : ($parentGroup ?? 'administration');
            $node['group_code'] = $group;
            $node['children'] = array_values(array_filter(array_map(fn ($child) => $split($child, $group), $node['children'] ?? [])));
            if (! $node['children'] && ($node['route'] ?? '#') === '#' && empty($node['href'])) {
                return null;
            }
            if ($parentGroup === null || $parentGroup !== $group) {
                $node['parent_id'] = null;
                $groups[$group]['items'][] = $node;
                $groups[$group]['label'] = $labels[$group];
                return null;
            }

            return $node;
        };
        foreach ($tree as $node) {
            $split($node);
        }
        // Every valid role contributes; ties follow the catalogue, not assignment order.
        $base = array_keys($labels);
        $priorities = config('navigation.priorities', []);
        $score = fn ($group) => $roles
            ? min(array_map(fn ($role) => array_search($group, $priorities[$role] ?? $base), $roles))
            : array_search($group, $base);
        uksort($groups, fn ($a, $b) => [$score($a), array_search($a, $base)] <=> [$score($b), array_search($b, $base)]);

        return $groups;
    }
}
