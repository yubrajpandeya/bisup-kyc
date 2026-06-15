<?php

namespace Bisup\PtrPort25;

use WHMCS\Database\Capsule;

class Database
{
    public static function install(): void
    {
        $schema = Capsule::schema();

        if (!$schema->hasTable('mod_bisup_ptr25_requests')) {
            $schema->create('mod_bisup_ptr25_requests', function ($table) {
                $table->increments('id');
                $table->integer('client_id')->unsigned();
                $table->integer('service_id')->unsigned();
                $table->integer('product_id')->unsigned()->nullable();
                $table->string('request_type', 32);
                $table->string('ip_address', 64);
                $table->string('ptr_hostname', 255)->nullable();
                $table->string('mail_domain', 255)->nullable();
                $table->string('mail_usage_type', 80);
                $table->text('usage_reason');
                $table->integer('estimated_daily_volume')->unsigned()->default(0);
                $table->string('mail_server_software', 120)->nullable();
                $table->string('business_name', 255)->nullable();
                $table->string('website_url', 255)->nullable();
                $table->string('contact_person_name', 160)->nullable();
                $table->string('contact_number', 80)->nullable();
                $table->string('spf_status', 40)->nullable();
                $table->string('dkim_status', 40)->nullable();
                $table->string('dmarc_status', 40)->nullable();
                $table->string('risk_level', 20)->default('medium');
                $table->string('status', 40)->default('submitted');
                $table->boolean('client_declaration')->default(false);
                $table->integer('reviewed_by')->unsigned()->nullable();
                $table->integer('approved_by')->unsigned()->nullable();
                $table->integer('rejected_by')->unsigned()->nullable();
                $table->text('rejection_reason')->nullable();
                $table->integer('enabled_by')->unsigned()->nullable();
                $table->timestamp('enabled_at')->nullable();
                $table->timestamps();
                $table->index(['client_id', 'service_id']);
                $table->index(['status', 'risk_level']);
                $table->index('ip_address');
            });
        }

        if (!$schema->hasTable('mod_bisup_ptr25_kyc_documents')) {
            $schema->create('mod_bisup_ptr25_kyc_documents', function ($table) {
                $table->increments('id');
                $table->integer('request_id')->unsigned();
                $table->integer('client_id')->unsigned();
                $table->string('document_type', 80);
                $table->string('original_filename', 255);
                $table->string('stored_filename', 255);
                $table->string('file_path', 500);
                $table->string('mime_type', 120);
                $table->integer('file_size')->unsigned();
                $table->string('verification_status', 40)->default('pending');
                $table->timestamp('uploaded_at')->useCurrent();
                $table->index(['request_id', 'client_id']);
            });
        }

        if (!$schema->hasTable('mod_bisup_ptr25_audit_logs')) {
            $schema->create('mod_bisup_ptr25_audit_logs', function ($table) {
                $table->increments('id');
                $table->integer('request_id')->unsigned()->nullable();
                $table->integer('admin_id')->unsigned()->nullable();
                $table->integer('client_id')->unsigned()->nullable();
                $table->string('action', 80);
                $table->string('old_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->text('note')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['request_id', 'created_at']);
                $table->index('admin_id');
            });
        }

        if (!$schema->hasTable('mod_bisup_ptr25_settings')) {
            $schema->create('mod_bisup_ptr25_settings', function ($table) {
                $table->increments('id');
                $table->string('setting_key', 120)->unique();
                $table->text('setting_value')->nullable();
                $table->timestamps();
            });
        }
    }
}

