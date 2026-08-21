<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEY_MAP = [
        'organization_name' => 'site_public_name',
        'organization_address' => 'site_public_address',
        'organization_phone' => 'site_public_phone',
    ];

    public function up(): void
    {
        if (! $this->tableExists('sys_settings')) {
            return;
        }

        foreach (self::KEY_MAP as $from => $to) {
            DB::table('sys_settings')
                ->where('key', $from)
                ->update(['key' => $to]);
        }

        DB::table('sys_settings')
            ->where('group', 'organization')
            ->update(['group' => 'site']);
    }

    public function down(): void
    {
        if (! $this->tableExists('sys_settings')) {
            return;
        }

        foreach (self::KEY_MAP as $from => $to) {
            DB::table('sys_settings')
                ->where('key', $to)
                ->update(['key' => $from]);
        }

        DB::table('sys_settings')
            ->where('group', 'site')
            ->update(['group' => 'organization']);
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
};
