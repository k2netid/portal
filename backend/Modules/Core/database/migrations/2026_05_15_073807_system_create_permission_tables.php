<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = (bool) config('permission.teams');
        $testing = (bool) config('permission.testing');

        $tableNames = $this->requireStringKeyedStringMap(
            config('permission.table_names'),
            'permission.table_names',
            allowEmptyStringValues: false,
        );

        $columnNames = $this->requireStringKeyedStringMap(
            config('permission.column_names'),
            'permission.column_names',
            allowEmptyStringValues: true,
        );

        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (! is_string($pivotRole) || $pivotRole === '') {
            $pivotRole = 'role_id';
        }
        if (! is_string($pivotPermission) || $pivotPermission === '') {
            $pivotPermission = 'permission_id';
        }

        throw_if($tableNames === [], 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        $needsTeamColumn = $teams || $testing;
        throw_if($needsTeamColumn && (($columnNames['team_foreign_key'] ?? null) === null || $columnNames['team_foreign_key'] === ''), 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        /**
         * See `docs/prerequisites.md` for suggested lengths on 'name' and 'guard_name' if "1071 Specified key was too long" errors are encountered.
         */
        Schema::create($tableNames['permissions'], static function (Blueprint $table): void {
            $table->uuid('id')->primary(); // permission id
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        /**
         * See `docs/prerequisites.md` for suggested lengths on 'name' and 'guard_name' if "1071 Specified key was too long" errors are encountered.
         */
        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($columnNames, $needsTeamColumn): void {
            $table->uuid('id')->primary(); // role id
            if ($needsTeamColumn) { // permission.testing is a fix for sqlite testing
                $teamFk = $columnNames['team_foreign_key'] ?? null;
                if (! is_string($teamFk) || $teamFk === '') {
                    throw new RuntimeException('team_foreign_key must be a non-empty string when teams or testing mode is enabled.');
                }
                $table->uuid($teamFk)->nullable();
                $table->index($teamFk, 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            if ($needsTeamColumn) {
                $teamFk = $columnNames['team_foreign_key'] ?? null;
                if (! is_string($teamFk) || $teamFk === '') {
                    throw new RuntimeException('team_foreign_key must be a non-empty string when teams or testing mode is enabled.');
                }
                $table->unique(['$teamFk', 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        $modelMorphKey = $columnNames['model_morph_key'] ?? null;
        if (! is_string($modelMorphKey) || $modelMorphKey === '') {
            throw new RuntimeException('model_morph_key must be configured in permission.column_names.');
        }

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $modelMorphKey, $pivotPermission, $teams, $columnNames): void {
            $table->uuid($pivotPermission);

            $table->string('model_type');
            $table->uuid($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();
            if ($teams) {
                $teamFk = $columnNames['team_foreign_key'] ?? null;
                if (! is_string($teamFk) || $teamFk === '') {
                    throw new RuntimeException('team_foreign_key must be a non-empty string when teams is enabled.');
                }
                $table->uuid($teamFk);
                $table->index($teamFk, 'model_has_permissions_team_foreign_key_index');

                $table->primary([$teamFk, $pivotPermission, $modelMorphKey, 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $modelMorphKey, 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $modelMorphKey, $pivotRole, $teams, $columnNames): void {
            $table->uuid($pivotRole);

            $table->string('model_type');
            $table->uuid($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->cascadeOnDelete();
            if ($teams) {
                $teamFk = $columnNames['team_foreign_key'] ?? null;
                if (! is_string($teamFk) || $teamFk === '') {
                    throw new RuntimeException('team_foreign_key must be a non-empty string when teams is enabled.');
                }
                $table->uuid($teamFk);
                $table->index($teamFk, 'model_has_roles_team_foreign_key_index');

                $table->primary([$teamFk, $pivotRole, $modelMorphKey, 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $modelMorphKey, 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission): void {
            $table->uuid($pivotPermission);
            $table->uuid($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        $cacheStoreRaw = config('permission.cache.store');
        $cacheKeyRaw = config('permission.cache.key');

        $cacheStore = null;
        if (is_string($cacheStoreRaw) && $cacheStoreRaw !== '' && $cacheStoreRaw !== 'default') {
            $cacheStore = $cacheStoreRaw;
        }

        if (! is_string($cacheKeyRaw) || $cacheKeyRaw === '') {
            return;
        }

        app('cache')
            ->store($cacheStore)
            ->forget($cacheKeyRaw);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = $this->requireStringKeyedStringMap(
            config('permission.table_names'),
            'permission.table_names',
            allowEmptyStringValues: false,
        );

        throw_if($tableNames === [], 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }

    /**
     * @return array<string, string>
     */
    private function requireStringKeyedStringMap(mixed $value, string $label, bool $allowEmptyStringValues): array
    {
        if (! is_array($value)) {
            throw new RuntimeException("{$label} must be an array of string keys to string values.");
        }

        $out = [];
        foreach ($value as $key => $item) {
            if (! is_string($key)) {
                continue;
            }
            if (! is_string($item)) {
                continue;
            }
            if (! $allowEmptyStringValues && $item === '') {
                continue;
            }
            $out[$key] = $item;
        }

        return $out;
    }
};
