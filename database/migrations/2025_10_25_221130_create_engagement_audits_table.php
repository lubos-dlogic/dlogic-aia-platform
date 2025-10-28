<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
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

        DB::statement(<<<SQL
    CREATE TABLE `engagement_audits` (
      `id` bigint unsigned NOT NULL AUTO_INCREMENT,
      `engagement_fk` bigint unsigned DEFAULT NULL,
      `name` varchar(100) NOT NULL,
      `type` enum('SINGLE','COMPREHENSIVE','ENTERPRISE','SOFT1','SOFT2','SOFT3') NOT NULL,
      `data` json DEFAULT NULL,
      `description` varchar(1000) DEFAULT NULL,
      `created_by_user` bigint unsigned DEFAULT NULL,
      `created_by_process` varchar(100) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `audit_created_user_fk_idx` (`created_by_user`),
      KEY `audit_engagement_fk_idx` (`engagement_fk`),
      CONSTRAINT `audit_created_user_fk` FOREIGN KEY (`created_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
      CONSTRAINT `audit_engagement_fk` FOREIGN KEY (`engagement_fk`) REFERENCES `engagements` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=100001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_audits');
    }
};
