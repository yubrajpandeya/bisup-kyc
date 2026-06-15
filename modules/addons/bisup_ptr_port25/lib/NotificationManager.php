<?php

namespace Bisup\PtrPort25;

use WHMCS\Database\Capsule;

class NotificationManager
{
    public static function notifyRequestSubmitted(int $requestId, array $moduleSettings): void
    {
        $request = RequestManager::find($requestId);
        if (!$request) {
            return;
        }

        $subject = 'PTR / Port 25 request received #' . $requestId;
        $message = self::requestSubmittedMessage($request);

        if (function_exists('localAPI')) {
            $result = localAPI('SendEmail', [
                'id' => (int) $request->client_id,
                'customtype' => 'general',
                'customsubject' => $subject,
                'custommessage' => $message,
            ]);

            AuditLogger::log([
                'request_id' => $requestId,
                'client_id' => (int) $request->client_id,
                'action' => 'notification_sent',
                'note' => 'Request notification sent through WHMCS SendEmail. WHMCS Mail BCC settings apply.',
            ]);

            if (($result['result'] ?? '') === 'success') {
                return;
            }
        }

        self::recordUnsentNotification($requestId, (int) $request->client_id, $subject, $message);
    }

    public static function notifyClientStatusUpdate(int $requestId, string $newStatus, string $note): void
    {
        $request = RequestManager::find($requestId);
        if (!$request || !function_exists('localAPI')) {
            return;
        }

        localAPI('SendEmail', [
            'id' => (int) $request->client_id,
            'customtype' => 'general',
            'customsubject' => 'PTR / Port 25 request update #' . $requestId,
            'custommessage' => '<p>Your PTR / Port 25 request has been updated.</p>'
                . '<p><strong>Status:</strong> ' . self::e($newStatus) . '</p>'
                . '<p>' . nl2br(self::e($note)) . '</p>',
        ]);
    }

    public static function configuredBccRecipients(): array
    {
        $value = Capsule::table('tblconfiguration')
            ->where('setting', 'BCCMessages')
            ->value('value');

        if (!$value) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($email) {
            $email = trim($email);
            return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        }, explode(',', $value))));
    }

    private static function requestSubmittedMessage(object $request): string
    {
        return '<p>Your PTR / Port 25 approval request has been received and is waiting for review.</p>'
            . '<table cellpadding="6" cellspacing="0" border="1">'
            . '<tr><th align="left">Request ID</th><td>#' . (int) $request->id . '</td></tr>'
            . '<tr><th align="left">Service ID</th><td>' . (int) $request->service_id . '</td></tr>'
            . '<tr><th align="left">Type</th><td>' . self::e($request->request_type) . '</td></tr>'
            . '<tr><th align="left">IP Address</th><td>' . self::e($request->ip_address) . '</td></tr>'
            . '<tr><th align="left">PTR Hostname</th><td>' . self::e($request->ptr_hostname) . '</td></tr>'
            . '<tr><th align="left">Mail Domain</th><td>' . self::e($request->mail_domain) . '</td></tr>'
            . '<tr><th align="left">Risk Level</th><td>' . self::e($request->risk_level) . '</td></tr>'
            . '<tr><th align="left">Status</th><td>' . self::e($request->status) . '</td></tr>'
            . '</table>'
            . '<p>Bisup will review the request and may contact you if more information is required.</p>';
    }

    private static function recordUnsentNotification(int $requestId, int $clientId, string $subject, string $message): void
    {
        Capsule::table('tblemails')->insert([
            'userid' => $clientId,
            'subject' => $subject,
            'message' => $message,
            'date' => date('Y-m-d H:i:s'),
            'to' => '',
        ]);

        AuditLogger::log([
            'request_id' => $requestId,
            'client_id' => $clientId,
            'action' => 'notification_recorded',
            'note' => 'WHMCS localAPI was unavailable; notification was recorded in the email log only.',
        ]);
    }

    private static function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
