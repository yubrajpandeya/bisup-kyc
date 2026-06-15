# Installation

## Requirements

- WHMCS 8.x recommended
- PHP 8.1 or newer recommended
- Admin access to WHMCS
- Writable WHMCS attachments directory for private KYC storage

## Install

1. Copy `modules/addons/bisup_ptr_port25/` into your WHMCS installation.
2. In WHMCS admin, go to **System Settings > Addon Modules**.
3. Activate **Bisup PTR & Port 25 Approval Module**.
4. Configure:
   - Eligible product IDs
   - Maximum KYC file size
   - Allowed file types
   - Notification email
   - Senior approval requirement for high-risk requests
5. Assign admin role permissions for the module.

## Client Access

The addon exposes a client-area page through WHMCS addon routing and adds a sidebar link named **PTR / Port 25 Request**.

Clients should only see active services that match configured eligible product IDs.

## Admin Access

Open:

```text
WHMCS Admin > Addons > PTR & Port 25 Moderation
```

Admins can review requests, change status, add internal notes, and download KYC documents if their WHMCS role allows module access.

## Production Notes

- Test uploads, approval, rejection, and audit logging in staging first.
- Confirm the WHMCS attachments path is not publicly accessible.
- Confirm only authorized staff roles can access the addon.
- Do not enable PTR or Port 25 unless the request is approved in this module.

