<?php

use WHMCS\View\Menu\Item as MenuItem;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('AdminHomeWidgets', 1, function () {
    return new BisupPtrPort25DashboardWidget();
});

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

class BisupPtrPort25DashboardWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'PTR / Port 25 Approvals';
    protected $description = 'Pending KYC moderation requests';
    protected $weight = 20;
    protected $columns = 1;
    protected $cache = false;
    protected $requiredPermission = '';

    public function getData()
    {
        $table = \WHMCS\Database\Capsule::table('mod_bisup_ptr25_requests');

        return [
            'pending' => (clone $table)->whereIn('status', ['submitted', 'under_review', 'more_info_required'])->count(),
            'submitted' => (clone $table)->where('status', 'submitted')->count(),
            'under_review' => (clone $table)->where('status', 'under_review')->count(),
            'approved' => (clone $table)->where('status', 'approved')->count(),
            'high_risk' => (clone $table)->where('risk_level', 'high')->whereIn('status', ['submitted', 'under_review', 'more_info_required'])->count(),
            'latest' => \WHMCS\Database\Capsule::table('mod_bisup_ptr25_requests')
                ->whereIn('status', ['submitted', 'under_review', 'more_info_required'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    public function generateOutput($data)
    {
        $pending = (int) ($data['pending'] ?? 0);
        $submitted = (int) ($data['submitted'] ?? 0);
        $underReview = (int) ($data['under_review'] ?? 0);
        $approved = (int) ($data['approved'] ?? 0);
        $highRisk = (int) ($data['high_risk'] ?? 0);
        $badgeClass = $pending > 0 ? 'danger' : 'success';

        $rows = '';
        foreach (($data['latest'] ?? []) as $request) {
            $rows .= '<tr>'
                . '<td><a href="addonmodules.php?module=bisup_ptr_port25&request_id=' . (int) $request->id . '">#' . (int) $request->id . '</a></td>'
                . '<td>' . self::e($request->ip_address) . '</td>'
                . '<td>' . self::e($request->status) . '</td>'
                . '<td>' . self::e($request->risk_level) . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="4" class="text-muted">No pending approval requests.</td></tr>';
        }

        return '
            <div class="widget-content-padded">
                <div class="row text-center">
                    <div class="col-xs-4">
                        <div class="alert alert-' . $badgeClass . '" style="margin-bottom:10px;">
                            <div style="font-size:28px;font-weight:700;line-height:1;">' . $pending . '</div>
                            <div>Pending</div>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="alert alert-info" style="margin-bottom:10px;">
                            <div style="font-size:28px;font-weight:700;line-height:1;">' . $submitted . '</div>
                            <div>Submitted</div>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="alert alert-warning" style="margin-bottom:10px;">
                            <div style="font-size:28px;font-weight:700;line-height:1;">' . $highRisk . '</div>
                            <div>High Risk</div>
                        </div>
                    </div>
                </div>
                <div class="small text-muted" style="margin-bottom:10px;">
                    Under review: ' . $underReview . ' &nbsp; Approved awaiting enablement: ' . $approved . '
                </div>
                <table class="table table-condensed table-striped" style="margin-bottom:10px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IP</th>
                            <th>Status</th>
                            <th>Risk</th>
                        </tr>
                    </thead>
                    <tbody>' . $rows . '</tbody>
                </table>
                <a class="btn btn-primary btn-sm" href="addonmodules.php?module=bisup_ptr_port25">Open moderation</a>
            </div>';
    }

    private static function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

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
