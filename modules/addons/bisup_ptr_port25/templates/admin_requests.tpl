<h2>Bisup PTR &amp; Port 25 Moderation</h2>

{{message}}
{{error}}

<form method="get" action="addonmodules.php" class="form-inline" style="margin-bottom: 16px;">
    <input type="hidden" name="module" value="bisup_ptr_port25" />
    <select name="status" class="form-control">
        <option value="">All statuses</option>
        <option value="submitted">Submitted</option>
        <option value="under_review">Under Review</option>
        <option value="more_info_required">More Info Required</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="enabled">Enabled</option>
        <option value="suspended">Suspended</option>
        <option value="abuse_flagged">Abuse Flagged</option>
    </select>
    <select name="risk_level" class="form-control">
        <option value="">All risk levels</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select>
    <input type="text" name="ip_address" class="form-control" placeholder="IP address" />
    <button type="submit" class="btn btn-default">Filter</button>
</form>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Service</th>
            <th>Type</th>
            <th>IP</th>
            <th>Risk</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {{rows}}
    </tbody>
</table>

