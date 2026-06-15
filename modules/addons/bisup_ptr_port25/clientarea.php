<?php

namespace Bisup\PtrPort25;

use Throwable;

class ClientAreaController
{
    public static function handle(array $vars): array
    {
        $clientId = (int) ($_SESSION['uid'] ?? 0);
        if (!$clientId) {
            return [
                'pagetitle' => 'PTR / Port 25 Request',
                'breadcrumb' => ['index.php?m=bisup_ptr_port25' => 'PTR / Port 25 Request'],
                'templatefile' => 'templates/clientarea',
                'requirelogin' => true,
                'vars' => ['error' => 'Please log in to submit a request.'],
            ];
        }

        $settings = self::moduleSettings($vars);
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (function_exists('check_token')) {
                    check_token('WHMCS.default');
                }

                if (empty($_FILES['kyc_document']['name'])) {
                    throw new \InvalidArgumentException('KYC document upload is required.');
                }

                $requestId = RequestManager::createFromClient($clientId, $_POST);

                KycManager::storeUploadedDocument(
                    $requestId,
                    $clientId,
                    $_FILES['kyc_document'],
                    $_POST['document_type'] ?? 'identity',
                    $settings
                );

                NotificationManager::notifyRequestSubmitted($requestId, $settings);
                $message = 'Your request has been submitted for Bisup staff review.';
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $services = RequestManager::eligibleServices($clientId, self::eligibleProductIds($settings));

        return [
            'pagetitle' => 'PTR / Port 25 Request',
            'breadcrumb' => ['index.php?m=bisup_ptr_port25' => 'PTR / Port 25 Request'],
            'templatefile' => 'templates/clientarea',
            'requirelogin' => true,
            'vars' => [
                'services' => $services,
                'message' => $message,
                'error' => $error,
                'antiSpamPolicyText' => $settings['anti_spam_policy_text'] ?? '',
            ],
        ];
    }

    private static function moduleSettings(array $vars): array
    {
        return [
            'eligible_product_ids' => $vars['eligible_product_ids'] ?? '',
            'max_kyc_file_size_mb' => $vars['max_kyc_file_size_mb'] ?? 5,
            'allowed_file_types' => $vars['allowed_file_types'] ?? 'pdf,jpg,jpeg,png',
            'notification_email' => $vars['notification_email'] ?? '',
            'anti_spam_policy_text' => $vars['anti_spam_policy_text'] ?? '',
        ];
    }

    private static function eligibleProductIds(array $settings): array
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) ($settings['eligible_product_ids'] ?? '')))));
    }
}
