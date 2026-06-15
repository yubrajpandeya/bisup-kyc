<?php

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar) {
    if (!isset($_SESSION['uid'])) {
        return;
    }

    $servicePanel = $primarySidebar->getChild('Service Details Overview');
    if ($servicePanel) {
        $servicePanel->addChild('Bisup PTR Port 25 Request', [
            'label' => 'PTR / Port 25 Request',
            'uri' => 'index.php?m=bisup_ptr_port25',
            'order' => 90,
        ]);
    }
});

add_hook('AfterModuleSuspend', 1, function ($vars) {
    if (empty($vars['params']['serviceid'])) {
        return;
    }

    try {
        \WHMCS\Database\Capsule::table('mod_bisup_ptr25_requests')
            ->where('service_id', (int) $vars['params']['serviceid'])
            ->whereIn('status', ['submitted', 'under_review', 'approved'])
            ->update([
                'status' => 'suspended',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    } catch (Throwable $e) {
        logActivity('Bisup PTR/Port25 hook failed after suspension: ' . $e->getMessage());
    }
});

add_hook('AfterModuleTerminate', 1, function ($vars) {
    if (empty($vars['params']['serviceid'])) {
        return;
    }

    try {
        \WHMCS\Database\Capsule::table('mod_bisup_ptr25_requests')
            ->where('service_id', (int) $vars['params']['serviceid'])
            ->whereIn('status', ['submitted', 'under_review', 'approved', 'enabled'])
            ->update([
                'status' => 'suspended',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    } catch (Throwable $e) {
        logActivity('Bisup PTR/Port25 hook failed after termination: ' . $e->getMessage());
    }
});

