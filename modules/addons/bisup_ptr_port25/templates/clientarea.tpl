{if $message}
    <div class="alert alert-success">{$message}</div>
{/if}

{if $error}
    <div class="alert alert-danger">{$error}</div>
{/if}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">PTR / Port 25 Approval Request</h3>
    </div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data" action="index.php?m=bisup_ptr_port25">
            <input type="hidden" name="token" value="{$token}" />

            <div class="form-group">
                <label for="service_id">Active Service</label>
                <select name="service_id" id="service_id" class="form-control" required>
                    <option value="">Select service</option>
                    {foreach from=$services item=service}
                        <option value="{$service->id}">
                            #{$service->id} - {$service->product_name} {$service->domain} {$service->dedicatedip}
                        </option>
                    {/foreach}
                </select>
            </div>

            <div class="form-group">
                <label for="request_type">Request Type</label>
                <select name="request_type" id="request_type" class="form-control" required>
                    <option value="ptr">PTR / rDNS only</option>
                    <option value="port25">Outgoing Port 25 only</option>
                    <option value="both">Both PTR / rDNS and Port 25</option>
                </select>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="ip_address">Server IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" class="form-control" required />
                </div>
                <div class="col-sm-6 form-group">
                    <label for="ptr_hostname">Requested PTR Hostname</label>
                    <input type="text" name="ptr_hostname" id="ptr_hostname" class="form-control" placeholder="mail.example.com" />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="mail_domain">Mail Sending Domain</label>
                    <input type="text" name="mail_domain" id="mail_domain" class="form-control" />
                </div>
                <div class="col-sm-6 form-group">
                    <label for="mail_server_software">Mail Server Software</label>
                    <input type="text" name="mail_server_software" id="mail_server_software" class="form-control" placeholder="Postfix, Exim, Mailcow, etc." />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="mail_usage_type">Mail Usage Type</label>
                    <select name="mail_usage_type" id="mail_usage_type" class="form-control" required>
                        <option value="Transactional emails">Transactional emails</option>
                        <option value="Business emails">Business emails</option>
                        <option value="Hosting client emails">Hosting client emails</option>
                        <option value="Application/system emails">Application/system emails</option>
                        <option value="Newsletter with consent">Newsletter with consent</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-sm-6 form-group">
                    <label for="estimated_daily_volume">Estimated Daily Email Volume</label>
                    <input type="number" name="estimated_daily_volume" id="estimated_daily_volume" class="form-control" min="0" value="0" />
                </div>
            </div>

            <div class="form-group">
                <label for="usage_reason">Reason for Request</label>
                <textarea name="usage_reason" id="usage_reason" class="form-control" rows="4" required></textarea>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="business_name">Business / Organization Name</label>
                    <input type="text" name="business_name" id="business_name" class="form-control" />
                </div>
                <div class="col-sm-6 form-group">
                    <label for="website_url">Website URL</label>
                    <input type="url" name="website_url" id="website_url" class="form-control" />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="contact_person_name">Contact Person Name</label>
                    <input type="text" name="contact_person_name" id="contact_person_name" class="form-control" />
                </div>
                <div class="col-sm-6 form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4 form-group">
                    <label for="spf_status">SPF Status</label>
                    <select name="spf_status" id="spf_status" class="form-control">
                        <option value="configured">Configured</option>
                        <option value="planned">Planned</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                <div class="col-sm-4 form-group">
                    <label for="dkim_status">DKIM Status</label>
                    <select name="dkim_status" id="dkim_status" class="form-control">
                        <option value="configured">Configured</option>
                        <option value="planned">Planned</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                <div class="col-sm-4 form-group">
                    <label for="dmarc_status">DMARC Status</label>
                    <select name="dmarc_status" id="dmarc_status" class="form-control">
                        <option value="configured">Configured</option>
                        <option value="planned">Planned</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="document_type">KYC Document Type</label>
                    <select name="document_type" id="document_type" class="form-control">
                        <option value="identity">Citizenship / National ID / Passport</option>
                        <option value="address_proof">Address Proof</option>
                        <option value="company_registration">Company Registration</option>
                        <option value="pan_vat">PAN / VAT</option>
                    </select>
                </div>
                <div class="col-sm-6 form-group">
                    <label for="kyc_document">KYC Document</label>
                    <input type="file" name="kyc_document" id="kyc_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required />
                </div>
            </div>

            <div class="checkbox">
                <label>
                    <input type="checkbox" name="client_declaration" value="1" required />
                    I confirm that this server will not be used for spam, phishing, spoofing, malware, bulk unsolicited emails, or illegal activity. I understand that Bisup may suspend the service, block Port 25, or terminate service if abuse is detected.
                </label>
            </div>

            {if $antiSpamPolicyText}
                <p class="text-muted">{$antiSpamPolicyText}</p>
            {/if}

            <button type="submit" class="btn btn-primary">Submit for Review</button>
        </form>
    </div>
</div>

