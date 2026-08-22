<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_mail_messages') || ! Schema::hasTable('sys_mail_accounts')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Null invalid / orphan account_id values (varchar vs uuid mismatch).
            DB::statement(<<<'SQL'
                UPDATE sys_mail_messages
                SET account_id = NULL
                WHERE account_id IS NOT NULL
                  AND (
                    account_id !~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                    OR account_id::uuid NOT IN (SELECT id FROM sys_mail_accounts)
                  )
            SQL);

            $columnType = DB::selectOne(<<<'SQL'
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'sys_mail_messages'
                  AND column_name = 'account_id'
            SQL);

            $dataType = is_object($columnType) && isset($columnType->data_type) && is_string($columnType->data_type)
                ? $columnType->data_type
                : null;

            if ($dataType === 'character varying') {
                DB::statement('ALTER TABLE sys_mail_messages ALTER COLUMN account_id TYPE uuid USING account_id::uuid');
            }
        } else {
            $validIds = DB::table('sys_mail_accounts')
                ->pluck('id')
                ->filter(fn ($id): bool => is_string($id) || is_int($id))
                ->map(fn ($id): string => is_string($id) ? $id : (string) $id)
                ->values()
                ->all();
            $query = DB::table('sys_mail_messages')->whereNotNull('account_id');
            if ($validIds === []) {
                $query->update(['account_id' => null]);
            } else {
                $query->whereNotIn('account_id', $validIds)->update(['account_id' => null]);
            }
        }

        $foreignKeys = Schema::getForeignKeys('sys_mail_messages');
        foreach ($foreignKeys as $fk) {
            if (in_array('account_id', $fk['columns'] ?? [], true)) {
                return;
            }
        }

        Schema::table('sys_mail_messages', function (Blueprint $table): void {
            $table->foreign('account_id')
                ->references('id')
                ->on('sys_mail_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sys_mail_messages')) {
            return;
        }

        Schema::table('sys_mail_messages', function (Blueprint $table): void {
            $table->dropForeign(['account_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sys_mail_messages ALTER COLUMN account_id TYPE varchar(255) USING account_id::text');
        }
    }
};
