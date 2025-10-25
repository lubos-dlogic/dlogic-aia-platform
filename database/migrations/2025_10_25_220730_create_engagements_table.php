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
    CREATE TABLE `engagements` (
      `id` bigint unsigned NOT NULL AUTO_INCREMENT,
      `key` varchar(12) NOT NULL,
      `name` varchar(100) NOT NULL,
      `client_fk` int unsigned NOT NULL,
      `version` smallint unsigned DEFAULT '1',
      `description` varchar(1000) DEFAULT NULL,
      `data` json DEFAULT NULL,
      `created_by_user` bigint unsigned DEFAULT NULL,
      `created_by_process` varchar(100) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `key_UNIQUE` (`key`),
      KEY `engagement_client_fk_idx` (`client_fk`),
      KEY `engagement_user_fk_idx` (`created_by_user`),
      CONSTRAINT `engagement_client_fk` FOREIGN KEY (`client_fk`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
      CONSTRAINT `engagement_user_fk` FOREIGN KEY (`created_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};
