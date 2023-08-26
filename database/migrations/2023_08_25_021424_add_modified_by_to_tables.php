<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'access_permissions',
            'accesses',
            'control_process_standards',
            'cpi_order_exits',
            'cpi_order_has_control_process_photos',
            'cpi_order_has_control_processes',
            'cpi_order_has_problems',
            'cpi_order_has_samplings',
            'cpi_order_has_sections',
            'cpi_order_has_standards',
            'cpi_orders',
            'decline_reasons',
            'documents',
            'failed_jobs',
            'form_control_processes',
            'is_cpi_order_correcteds',
            'lines',
            'log_trail_declineds',
            'log_trail_details',
            'log_trails',
            'notifications',
            'password_reset_tokens',
            'permissions',
            'personal_access_tokens',
            'problems',
            'roles',
            'samplings',
            'section_approvals',
            'sections',
            'stream_section_head',
            'stream_verifications',
            'streams',
            'user_has_streams',
            'users',
            'verification_approvals'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('modified_by')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
