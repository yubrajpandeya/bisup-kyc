<?php

namespace Bisup\PtrPort25;

use Throwable;

class AdminController
{
    public static function handle(array $vars): string
    {
        $adminId = (int) ($_SESSION['adminid'] ?? 0);
        $message = '';
        $error = '';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
                if (function_exists('check_token')) {
                    check_token('WHMCS.admin.default');
                }

                RequestManager::updateStatus(
                    (int) $_POST['request_id'],
                    (string) $_POST['status'],
                    $adminId,
                    trim((string) ($_POST['note'] ?? ''))
                );
                $message = 'Request status updated and audit log recorded.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        $requestId = (int) ($_GET['request_id'] ?? 0);
        if ($requestId > 0) {
            return self::detail($requestId, $message, $error);
        }

        return self::index($message, $error);
    }

    private static function index(string $message, string $error): string
    {
        $requests = RequestManager::list([
            'status' => $_GET['status'] ?? null,
            'risk_level' => $_GET['risk_level'] ?? null,
            'ip_address' => $_GET['ip_address'] ?? null,
        ]);

        $rows = '';
        foreach ($requests as $request) {
            $rows .= '<tr>'
                . '<td>#' . self::e($request->id) . '</td>'
                . '<td>' . self::e(trim(($request->firstname ?? '') . ' ' . ($request->lastname ?? ''))) . '<br><small>' . self::e($request->email ?? '') . '</small></td>'
                . '<td>' . self::e($request->service_id) . '</td>'
                . '<td>' . self::e($request->request_type) . '</td>'
                . '<td>' . self::e($request->ip_address) . '</td>'
                . '<td><span class="label label-' . self::riskClass($request->risk_level) . '">' . self::e($request->risk_level) . '</span></td>'
                . '<td>' . self::e($request->status) . '</td>'
                . '<td>' . self::e($request->created_at) . '</td>'
                . '<td><a class="btn btn-default btn-sm" href="addonmodules.php?module=bisup_ptr_port25&request_id=' . (int) $request->id . '">Review</a></td>'
                . '</tr>';
        }

        return self::render('admin_requests.tpl', [
            'message' => self::notice($message, 'success'),
            'error' => self::notice($error, 'danger'),
            'rows' => $rows ?: '<tr><td colspan="9">No requests found.</td></tr>',
        ]);
    }

    private static function detail(int $requestId, string $message, string $error): string
    {
        $request = RequestManager::find($requestId);
        if (!$request) {
            return self::notice('Request not found.', 'danger');
        }

        $documents = '';
        foreach (RequestManager::documents($requestId) as $doc) {
            $documents .= '<li>' . self::e($doc->document_type) . ': ' . self::e($doc->original_filename) . ' (' . number_format((int) $doc->file_size / 1024, 1) . ' KB)</li>';
        }

        $logs = '';
        foreach (RequestManager::auditLogs($requestId) as $log) {
            $logs .= '<tr>'
                . '<td>' . self::e($log->created_at) . '</td>'
                . '<td>' . self::e($log->admin_id ?: $log->client_id) . '</td>'
                . '<td>' . self::e($log->action) . '</td>'
                . '<td>' . self::e(($log->old_status ?: '-') . ' -> ' . ($log->new_status ?: '-')) . '</td>'
                . '<td>' . nl2br(self::e($log->note ?? '')) . '</td>'
                . '</tr>';
        }

        return self::render('admin_request_detail.tpl', [
            'message' => self::notice($message, 'success'),
            'error' => self::notice($error, 'danger'),
            'id' => self::e($request->id),
            'client_id' => self::e($request->client_id),
            'service_id' => self::e($request->service_id),
            'request_type' => self::e($request->request_type),
            'ip_address' => self::e($request->ip_address),
            'ptr_hostname' => self::e($request->ptr_hostname),
            'mail_domain' => self::e($request->mail_domain),
            'mail_usage_type' => self::e($request->mail_usage_type),
            'usage_reason' => nl2br(self::e($request->usage_reason)),
            'estimated_daily_volume' => self::e($request->estimated_daily_volume),
            'mail_server_software' => self::e($request->mail_server_software),
            'business_name' => self::e($request->business_name),
            'website_url' => self::e($request->website_url),
            'contact_person_name' => self::e($request->contact_person_name),
            'contact_number' => self::e($request->contact_number),
            'dns_status' => self::e('SPF: ' . $request->spf_status . ', DKIM: ' . $request->dkim_status . ', DMARC: ' . $request->dmarc_status),
            'risk_level' => self::e($request->risk_level),
            'status' => self::e($request->status),
            'documents' => $documents ?: '<li>No KYC documents recorded.</li>',
            'logs' => $logs ?: '<tr><td colspan="5">No audit logs found.</td></tr>',
            'token' => self::e($_SESSION['token'] ?? ''),
        ]);
    }

    private static function render(string $template, array $vars): string
    {
        $path = __DIR__ . '/templates/' . $template;
        $html = file_get_contents($path) ?: '';
        foreach ($vars as $key => $value) {
            $html = str_replace('{{' . $key . '}}', (string) $value, $html);
        }
        return $html;
    }

    private static function notice(string $message, string $type): string
    {
        if ($message === '') {
            return '';
        }
        return '<div class="alert alert-' . self::e($type) . '">' . self::e($message) . '</div>';
    }

    private static function riskClass(string $risk): string
    {
        return ['low' => 'success', 'medium' => 'warning', 'high' => 'danger'][$risk] ?? 'default';
    }

    private static function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
