<?php

namespace Bisup\PtrPort25;

use WHMCS\Database\Capsule;

class AuditLogger
{
    public static function log(array $data): void
    {
        Capsule::table('mod_bisup_ptr25_audit_logs')->insert([
            'request_id' => $data['request_id'] ?? null,
            'admin_id' => $data['admin_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'action' => $data['action'],
            'old_status' => $data['old_status'] ?? null,
            'new_status' => $data['new_status'] ?? null,
            'note' => $data['note'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

