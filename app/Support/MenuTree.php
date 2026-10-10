<?php

namespace App\Support;

use Illuminate\Support\Str;

final class MenuTree
{
    public static function active(array $node, ?string $routeName): bool
    {
        if ($routeName && ! empty($node['route']) && Str::is($node['route'], $routeName)) {
            return true;
        }
        foreach ($node['children'] ?? [] as $child) {
            if (self::active($child, $routeName)) {
                return true;
            }
        }

        return false;
    }

    public static function build(array $rows, bool $promoteOrphans = false): array
    {
        $nodes = array_column($rows, null, 'id');
        $children = [];
        foreach ($nodes as $id => $node) {
            $parent = $node['parent_id'] ?? null;
            if ($parent !== null && ! isset($nodes[$parent])) {
                if (! $promoteOrphans) {
                    continue;
                }
                $parent = null;
            }
            $children[$parent ?? 0][] = $id;
        }
        $walk = function ($parent, array $path = []) use (&$walk, $nodes, $children): array {
            $result = [];
            foreach ($children[$parent] ?? [] as $id) {
                if (isset($path[$id])) {
                    continue;
                }
                $node = $nodes[$id];
                $node['children'] = $walk($id, $path + [$id => true]);
                $result[] = $node;
            }

            return $result;
        };

        return $walk(0);
    }
}
