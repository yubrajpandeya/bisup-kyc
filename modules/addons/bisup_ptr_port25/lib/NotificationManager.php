<?php

namespace Bisup\PtrPort25;

use WHMCS\Database\Capsule;

class NotificationManager
{
    public static function notifyRequestSubmitted(int $requestId, array $moduleSettings): void
    {
        $email = trim((string) ($moduleSettings['notification_email'] ?? ''));
        if ($email === '') {
            return;
        }

        $request = RequestManager::find($requestId);
        if (!$request) {
            return;
        }

        Capsule::table('tblemails')->insert([
            'userid' => 0,
            'subject' => 'PTR / Port 25 request submitted #' . $requestId,
            'message' => 'A new Bisup PTR / Port 25 approval request is waiting for review. Request ID: ' . $requestId,
            'date' => date('Y-m-d H:i:s'),
            'to' => $email,
        ]);
    }
}

