<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/AuditLogger.php';
require_once __DIR__ . '/lib/RequestManager.php';
require_once __DIR__ . '/lib/KycManager.php';
require_once __DIR__ . '/lib/NotificationManager.php';

use Bisup\PtrPort25\Database;

function bisup_ptr_port25_config()
{
    return [
        'name' => 'Bisup PTR & Port 25 Approval Module',
        'description' => 'Moderates PTR/rDNS and outgoing Port 25 requests with KYC, approvals, staff accountability, and audit logs.',
        'version' => '0.1.0',
        'author' => 'Bisup',
        'language' => 'english',
        'fields' => [
            'eligible_product_ids' => [
                'FriendlyName' => 'Eligible Product IDs',
                'Type' => 'text',
                'Size' => '60',
                'Description' => 'Comma-separated WHMCS product IDs that may request PTR/Port 25.',
                'Default' => '',
            ],
            'max_kyc_file_size_mb' => [
                'FriendlyName' => 'Maximum KYC File Size',
                'Type' => 'text',
                'Size' => '8',
                'Description' => 'Maximum upload size in MB.',
                'Default' => '5',
            ],
            'allowed_file_types' => [
                'FriendlyName' => 'Allowed File Types',
                'Type' => 'text',
                'Size' => '40',
                'Description' => 'Comma-separated extensions.',
                'Default' => 'pdf,jpg,jpeg,png',
            ],
            'notification_email' => [
                'FriendlyName' => 'Legacy Notification Email',
                'Type' => 'text',
                'Size' => '60',
                'Description' => 'Deprecated. Request notices are sent through WHMCS mail so General Settings > Mail > BCC Messages receives a copy.',
                'Default' => '',
            ],
            'require_senior_approval_high_risk' => [
                'FriendlyName' => 'Senior Approval for High Risk',
                'Type' => 'yesno',
                'Description' => 'Require senior admin approval before high-risk requests may be enabled.',
                'Default' => 'on',
            ],
            'anti_spam_policy_text' => [
                'FriendlyName' => 'Anti-Spam Policy Text',
                'Type' => 'textarea',
                'Rows' => '4',
                'Cols' => '80',
                'Default' => 'This server must not be used for spam, phishing, spoofing, malware, bulk unsolicited email, or illegal activity.',
            ],
        ],
    ];
}

function bisup_ptr_port25_activate()
{
    try {
        Database::install();
        return [
            'status' => 'success',
            'description' => 'Bisup PTR & Port 25 approval tables were created.',
        ];
    } catch (Throwable $e) {
        return [
            'status' => 'error',
            'description' => 'Activation failed: ' . $e->getMessage(),
        ];
    }
}

function bisup_ptr_port25_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'Module deactivated. Database tables were preserved for audit retention.',
    ];
}

function bisup_ptr_port25_upgrade($vars)
{
    Database::install();
}

function bisup_ptr_port25_output($vars)
{
    require __DIR__ . '/admin.php';
    echo Bisup\PtrPort25\AdminController::handle($vars);
}

function bisup_ptr_port25_clientarea($vars)
{
    require_once __DIR__ . '/clientarea.php';
    return Bisup\PtrPort25\ClientAreaController::handle($vars);
}
