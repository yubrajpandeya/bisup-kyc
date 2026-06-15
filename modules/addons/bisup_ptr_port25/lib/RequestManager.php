<?php

namespace Bisup\PtrPort25;

use InvalidArgumentException;
use WHMCS\Database\Capsule;

class RequestManager
{
    public const STATUSES = [
        'pending_kyc',
        'submitted',
        'under_review',
        'more_info_required',
        'approved',
        'rejected',
        'enabled',
        'suspended',
        'abuse_flagged',
    ];

    public static function list(array $filters = [])
    {
        $query = Capsule::table('mod_bisup_ptr25_requests as r')
            ->leftJoin('tblclients as c', 'c.id', '=', 'r.client_id')
            ->leftJoin('tblhosting as h', 'h.id', '=', 'r.service_id')
            ->select('r.*', 'c.firstname', 'c.lastname', 'c.email', 'h.domainstatus as service_status');

        foreach (['status', 'risk_level', 'client_id', 'service_id', 'ip_address', 'request_type'] as $field) {
            if (!empty($filters[$field])) {
                $query->where('r.' . $field, $filters[$field]);
            }
        }

        return $query->orderBy('r.created_at', 'desc')->limit(200)->get();
    }

    public static function find(int $id)
    {
        return Capsule::table('mod_bisup_ptr25_requests')->where('id', $id)->first();
    }

    public static function documents(int $requestId)
    {
        return Capsule::table('mod_bisup_ptr25_kyc_documents')->where('request_id', $requestId)->orderBy('uploaded_at', 'desc')->get();
    }

    public static function auditLogs(int $requestId)
    {
        return Capsule::table('mod_bisup_ptr25_audit_logs')->where('request_id', $requestId)->orderBy('created_at', 'desc')->get();
    }

    public static function eligibleServices(int $clientId, array $productIds)
    {
        $query = Capsule::table('tblhosting as h')
            ->join('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->where('h.userid', $clientId)
            ->where('h.domainstatus', 'Active')
            ->select('h.id', 'h.packageid', 'h.domain', 'h.dedicatedip', 'p.name as product_name');

        if ($productIds) {
            $query->whereIn('h.packageid', $productIds);
        }

        return $query->orderBy('h.id', 'desc')->get();
    }

    public static function createFromClient(int $clientId, array $data): int
    {
        self::validateClientSubmission($clientId, $data);
        $service = self::serviceForClient($clientId, (int) $data['service_id']);
        $riskLevel = self::calculateRiskLevel($data);

        $id = Capsule::table('mod_bisup_ptr25_requests')->insertGetId([
            'client_id' => $clientId,
            'service_id' => (int) $data['service_id'],
            'product_id' => (int) $service->packageid,
            'request_type' => $data['request_type'],
            'ip_address' => trim($data['ip_address']),
            'ptr_hostname' => strtolower(trim($data['ptr_hostname'] ?? '')),
            'mail_domain' => strtolower(trim($data['mail_domain'] ?? '')),
            'mail_usage_type' => $data['mail_usage_type'],
            'usage_reason' => trim($data['usage_reason']),
            'estimated_daily_volume' => (int) ($data['estimated_daily_volume'] ?? 0),
            'mail_server_software' => trim($data['mail_server_software'] ?? ''),
            'business_name' => trim($data['business_name'] ?? ''),
            'website_url' => trim($data['website_url'] ?? ''),
            'contact_person_name' => trim($data['contact_person_name'] ?? ''),
            'contact_number' => trim($data['contact_number'] ?? ''),
            'spf_status' => $data['spf_status'] ?? 'planned',
            'dkim_status' => $data['dkim_status'] ?? 'planned',
            'dmarc_status' => $data['dmarc_status'] ?? 'planned',
            'risk_level' => $riskLevel,
            'status' => 'submitted',
            'client_declaration' => !empty($data['client_declaration']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLogger::log([
            'request_id' => $id,
            'client_id' => $clientId,
            'action' => 'client_request_created',
            'new_status' => 'submitted',
            'note' => 'Client submitted PTR/Port 25 moderation request.',
        ]);

        return $id;
    }

    public static function updateStatus(int $requestId, string $newStatus, int $adminId, string $note = ''): void
    {
        if (!in_array($newStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid request status.');
        }

        $request = self::find($requestId);
        if (!$request) {
            throw new InvalidArgumentException('Request not found.');
        }

        $updates = [
            'status' => $newStatus,
            'reviewed_by' => $adminId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($newStatus === 'approved') {
            $updates['approved_by'] = $adminId;
        }
        if ($newStatus === 'rejected') {
            $updates['rejected_by'] = $adminId;
            $updates['rejection_reason'] = $note;
        }
        if ($newStatus === 'enabled') {
            $updates['enabled_by'] = $adminId;
            $updates['enabled_at'] = date('Y-m-d H:i:s');
        }

        Capsule::table('mod_bisup_ptr25_requests')->where('id', $requestId)->update($updates);

        AuditLogger::log([
            'request_id' => $requestId,
            'admin_id' => $adminId,
            'client_id' => $request->client_id,
            'action' => 'status_changed',
            'old_status' => $request->status,
            'new_status' => $newStatus,
            'note' => $note,
        ]);
    }

    private static function validateClientSubmission(int $clientId, array $data): void
    {
        foreach (['service_id', 'request_type', 'ip_address', 'mail_usage_type', 'usage_reason'] as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException('Missing required field: ' . $field);
            }
        }

        if (empty($data['client_declaration'])) {
            throw new InvalidArgumentException('Anti-spam declaration is required.');
        }

        if (!filter_var($data['ip_address'], FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address.');
        }

        if (!in_array($data['request_type'], ['ptr', 'port25', 'both'], true)) {
            throw new InvalidArgumentException('Invalid request type.');
        }

        if (in_array($data['request_type'], ['ptr', 'both'], true) && empty($data['ptr_hostname'])) {
            throw new InvalidArgumentException('PTR hostname is required.');
        }

        $service = self::serviceForClient($clientId, (int) $data['service_id']);
        if (!$service) {
            throw new InvalidArgumentException('Selected service is not active or does not belong to this client.');
        }

        if (!self::ipBelongsToService($data['ip_address'], $service)) {
            throw new InvalidArgumentException('The requested IP address is not assigned to the selected service.');
        }
    }

    private static function serviceForClient(int $clientId, int $serviceId)
    {
        return Capsule::table('tblhosting')
            ->where('id', $serviceId)
            ->where('userid', $clientId)
            ->where('domainstatus', 'Active')
            ->first();
    }

    private static function ipBelongsToService(string $ipAddress, object $service): bool
    {
        $candidates = [];
        if (!empty($service->dedicatedip)) {
            $candidates[] = trim((string) $service->dedicatedip);
        }
        if (!empty($service->assignedips)) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $service->assignedips);
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line));
                if (!empty($parts[0])) {
                    $candidates[] = $parts[0];
                }
            }
        }

        return in_array(trim($ipAddress), array_unique($candidates), true);
    }

    private static function calculateRiskLevel(array $data): string
    {
        $volume = (int) ($data['estimated_daily_volume'] ?? 0);
        $usage = strtolower((string) ($data['mail_usage_type'] ?? ''));

        if ($volume >= 5000 || str_contains($usage, 'newsletter') || str_contains($usage, 'bulk')) {
            return 'high';
        }

        if ($volume <= 500 && in_array($data['spf_status'] ?? '', ['valid', 'configured'], true)) {
            return 'low';
        }

        return 'medium';
    }
}
