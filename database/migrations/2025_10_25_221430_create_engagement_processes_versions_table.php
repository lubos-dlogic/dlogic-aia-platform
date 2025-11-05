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
    CREATE TABLE `engagement_processes_versions` (
      `id` bigint unsigned NOT NULL AUTO_INCREMENT,
      `process_fk` bigint unsigned NOT NULL,
      `name` varchar(100) DEFAULT NULL,
      `version_number` smallint unsigned DEFAULT NULL,
      `description` varchar(1000) DEFAULT NULL,
      `data` json DEFAULT NULL,
      `created_by_user` bigint unsigned DEFAULT NULL,
      `created_by_process` varchar(100) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `pversion_process_number_uq` (`process_fk`,`version_number`),
      KEY `pversion_user_fk_idx` (`created_by_user`),
      KEY `pversion_process_fk_idx` (`process_fk`),
      CONSTRAINT `pversion_process_fk` FOREIGN KEY (`process_fk`) REFERENCES `engagement_processes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
      CONSTRAINT `pversion_user_fk` FOREIGN KEY (`created_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_processes_versions');
    }
};
