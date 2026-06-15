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
                            {{status_options}}
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note Type</label>
                        <select name="note_type" class="form-control">
                            <option value="internal">Internal note only</option>
                            <option value="client_alert">Client alert email + audit note</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Note</label>
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

<div class="modal fade" id="bisupKycViewModal" tabindex="-1" role="dialog" aria-labelledby="bisupKycViewTitle">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="bisupKycViewTitle">KYC Document</h4>
            </div>
            <div class="modal-body" style="height: 75vh; padding: 0;">
                <iframe id="bisupKycViewFrame" src="about:blank" style="width:100%; height:100%; border:0;" title="KYC document preview"></iframe>
            </div>
            <div class="modal-footer">
                <a id="bisupKycDownloadLink" href="#" class="btn btn-default">Open in Browser</a>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('.bisup-kyc-view');
    var frame = document.getElementById('bisupKycViewFrame');
    var title = document.getElementById('bisupKycViewTitle');
    var openLink = document.getElementById('bisupKycDownloadLink');

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var url = button.getAttribute('data-url');
            title.textContent = button.getAttribute('data-title') || 'KYC Document';
            frame.setAttribute('src', url);
            openLink.setAttribute('href', url);

            if (window.jQuery && jQuery.fn.modal) {
                jQuery('#bisupKycViewModal').modal('show');
            } else {
                window.open(url, '_blank', 'noopener');
            }
        });
    });

    if (window.jQuery && jQuery.fn.modal) {
        jQuery('#bisupKycViewModal').on('hidden.bs.modal', function () {
            frame.setAttribute('src', 'about:blank');
        });
    }
});
</script>
