<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connectionRaw = config('activitylog.database_connection');
        $connectionName = is_string($connectionRaw) ? $connectionRaw : null;
        $tableRaw = config('activitylog.table_name');
        $tableName = is_string($tableRaw) ? $tableRaw : 'activity_log';

        Schema::connection($connectionName)->create($tableName, function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down(): void
    {
        $connectionRaw = config('activitylog.database_connection');
        $connectionName = is_string($connectionRaw) ? $connectionRaw : null;
        $tableRaw = config('activitylog.table_name');
        $tableName = is_string($tableRaw) ? $tableRaw : 'activity_log';

        Schema::connection($connectionName)->dropIfExists($tableName);
    }
};
