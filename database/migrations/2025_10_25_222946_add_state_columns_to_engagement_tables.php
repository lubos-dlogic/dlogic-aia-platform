<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip this migration for SQLite (used in tests)
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->string('state')->default('App\\States\\ClientDraft')->after('description');
        });

        Schema::table('engagements', function (Blueprint $table) {
            $table->string('state')->default('App\\States\\EngagementPlanning')->after('data');
        });

        Schema::table('engagement_audits', function (Blueprint $table) {
            $table->string('state')->default('App\\States\\EngagementAuditScheduled')->after('description');
        });

        Schema::table('engagement_processes', function (Blueprint $table) {
            $table->string('state')->default('App\\States\\EngagementProcessPending')->after('data');
        });

        Schema::table('engagement_processes_versions', function (Blueprint $table) {
            $table->string('state')->default('App\\States\\EngagementProcessVersionDraft')->after('data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('state');
        });

        Schema::table('engagements', function (Blueprint $table) {
            $table->dropColumn('state');
        });

        Schema::table('engagement_audits', function (Blueprint $table) {
            $table->dropColumn('state');
        });

        Schema::table('engagement_processes', function (Blueprint $table) {
            $table->dropColumn('state');
        });

        Schema::table('engagement_processes_versions', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
