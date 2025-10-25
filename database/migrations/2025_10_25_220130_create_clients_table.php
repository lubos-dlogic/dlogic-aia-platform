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
    CREATE TABLE `clients` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `clinet_key` varchar(30) NOT NULL,
      `country` varchar(2) NOT NULL,
      `website` varchar(250) DEFAULT NULL,
      `company_gid` varchar(40) DEFAULT NULL,
      `company_vat_gid` varchar(40) DEFAULT NULL,
      `description` text,
      `created_by_user` bigint unsigned DEFAULT NULL,
      `created_by_process` varchar(100) NOT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `clinet_key_UNIQUE` (`clinet_key`),
      KEY `client_user_created_fk_idx` (`created_by_user`),
      CONSTRAINT `client_user_created_fk` FOREIGN KEY (`created_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
