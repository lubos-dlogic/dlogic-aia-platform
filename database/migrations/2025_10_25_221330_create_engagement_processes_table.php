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
        DB::statement(<<<SQL
    CREATE TABLE `engagement_processes` (
      `id` bigint unsigned NOT NULL AUTO_INCREMENT,
      `engagement_fk` bigint unsigned NOT NULL,
      `key` varchar(4) NOT NULL,
      `name` varchar(100) NOT NULL,
      `description` varchar(1000) DEFAULT NULL,
      `data` json DEFAULT NULL,
      `created_by_user` bigint unsigned DEFAULT NULL,
      `created_by_process` varchar(100) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `engprocess_composite_uq` (`engagement_fk`,`key`),
      KEY `engprocess_user_fk_idx` (`created_by_user`),
      KEY `engprocess_engagement_fk_idx` (`engagement_fk`),
      CONSTRAINT `engprocess_engagement_fk` FOREIGN KEY (`engagement_fk`) REFERENCES `engagements` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
      CONSTRAINT `engprocess_user_fk` FOREIGN KEY (`created_by_user`) REFERENCES `users` (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=100001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_processes');
    }
};
