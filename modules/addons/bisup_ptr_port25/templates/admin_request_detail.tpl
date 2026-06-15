<p><a href="addonmodules.php?module=bisup_ptr_port25">&laquo; Back to moderation list</a></p>

<h2>PTR / Port 25 Request #{{id}}</h2>

{{message}}
{{error}}

<div class="row">
    <div class="col-md-7">
        <table class="table table-bordered">
            <tr><th>Client ID</th><td>{{client_id}}</td></tr>
            <tr><th>Service ID</th><td>{{service_id}}</td></tr>
            <tr><th>Request Type</th><td>{{request_type}}</td></tr>
            <tr><th>IP Address</th><td>{{ip_address}}</td></tr>
            <tr><th>PTR Hostname</th><td>{{ptr_hostname}}</td></tr>
            <tr><th>Mail Domain</th><td>{{mail_domain}}</td></tr>
            <tr><th>Mail Usage</th><td>{{mail_usage_type}}</td></tr>
            <tr><th>Estimated Daily Volume</th><td>{{estimated_daily_volume}}</td></tr>
            <tr><th>Mail Software</th><td>{{mail_server_software}}</td></tr>
            <tr><th>Business</th><td>{{business_name}}</td></tr>
            <tr><th>Website</th><td>{{website_url}}</td></tr>
            <tr><th>Contact</th><td>{{contact_person_name}} / {{contact_number}}</td></tr>
            <tr><th>DNS Readiness</th><td>{{dns_status}}</td></tr>
            <tr><th>Risk</th><td>{{risk_level}}</td></tr>
            <tr><th>Status</th><td>{{status}}</td></tr>
            <tr><th>Reason</th><td>{{usage_reason}}</td></tr>
        </table>
    </div>
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Moderation Action</strong></div>
            <div class="panel-body">
                <form method="post" action="addonmodules.php?module=bisup_ptr_port25&request_id={{id}}">
                    <input type="hidden" name="token" value="{{token}}" />
                    <input type="hidden" name="action" value="update_status" />
                    <input type="hidden" name="request_id" value="{{id}}" />

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="under_review">Under Review</option>
                            <option value="more_info_required">More Info Required</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="enabled">Enabled - Manual Technical Work Completed</option>
                            <option value="suspended">Suspended</option>
                            <option value="abuse_flagged">Abuse Flagged</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Internal Note</label>
                        <textarea name="note" class="form-control" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Moderation Decision</button>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><strong>KYC Documents</strong></div>
            <div class="panel-body">
                <ul>{{documents}}</ul>
            </div>
        </div>
    </div>
</div>

<h3>Audit Log</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Time</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Status</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>{{logs}}</tbody>
</table>
