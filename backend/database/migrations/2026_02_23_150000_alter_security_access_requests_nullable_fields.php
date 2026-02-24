<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE security_access_requests MODIFY user_id_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY password_action ENUM('change','no_change') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY email_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY internet_access ENUM('control_manager','control_staff') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY file_sharing ENUM('full_access','modify','read_only') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY vpn_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY reason TEXT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY accpac_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY ifs_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY administrator_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY fingerprint_action ENUM('new','change','delete') NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY change_data_action ENUM('new','change','delete') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE security_access_requests MODIFY user_id_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY password_action ENUM('change','no_change') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY email_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY internet_access ENUM('control_manager','control_staff') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY file_sharing ENUM('full_access','modify','read_only') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY vpn_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY reason TEXT NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY accpac_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY ifs_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY administrator_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY fingerprint_action ENUM('new','change','delete') NOT NULL");
        DB::statement("ALTER TABLE security_access_requests MODIFY change_data_action ENUM('new','change','delete') NOT NULL");
    }
};
