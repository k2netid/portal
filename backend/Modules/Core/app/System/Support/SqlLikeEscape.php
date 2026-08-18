<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Escape user input for SQL LIKE / ILIKE so % and _ are literal, across drivers.
 *
 * Use with {@see self::LIKE_ESCAPE_SQL} (escape character backslash).
 *
 * Prefer {@see self::whereContainsAny()} on PostgreSQL — positional bindings in
 * {@code whereRaw('... LIKE ?')} break when the pattern contains {@code %}.
 */
final class SqlLikeEscape
{
    /**
     * SQL fragment: {@code ESCAPE '\'} — single backslash as LIKE escape (MySQL, PostgreSQL, SQLite).
     */
    public const LIKE_ESCAPE_SQL = "ESCAPE '\\'";

    public static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    public static function contains(string $value): string
    {
        return '%'.self::escape($value).'%';
    }

    /**
     * Case-insensitive substring match on one or more columns (PostgreSQL-safe bindings).
     *
     * @param  EloquentBuilder<covariant Model>  $query
     * @param  array<int, string>  $columns
     */
    public static function whereContainsAny(EloquentBuilder $query, array $columns, string $term): void
    {
        $term = trim($term);
        if ($term === '' || $columns === []) {
            return;
        }

        $connection = $query->getConnection();
        $driver = $connection instanceof Connection
            ? $connection->getDriverName()
            : 'mysql';
        $termLower = mb_strtolower($term, 'UTF-8');
        $pattern = self::contains($termLower);

        $query->where(function (EloquentBuilder $nested) use ($columns, $pattern, $driver): void {
            foreach ($columns as $index => $column) {
                if ($driver === 'pgsql') {
                    if ($index === 0) {
                        $nested->where($column, 'ilike', $pattern);
                    } else {
                        $nested->orWhere($column, 'ilike', $pattern);
                    }

                    continue;
                }

                if ($index === 0) {
                    $nested->where($column, 'like', $pattern);
                } else {
                    $nested->orWhere($column, 'like', $pattern);
                }
            }
        });
    }
}
