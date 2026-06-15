# Bisup WHMCS PTR & Port 25 Approval Module

WHMCS addon module for moderating PTR/rDNS and outgoing Port 25 requests before any manual technical change is made.

Version 1 is intentionally focused on:

- KYC collection
- Client/service/IP request capture
- Admin moderation
- Approval/rejection workflow
- Staff accountability
- Audit logs
- Email/ticket-ready notification structure

PTR creation and Port 25 firewall automation are **not** part of v1. Bisup staff should not create PTR records or enable outgoing Port 25 outside this WHMCS approval module.

## Module Path

Install the module at:

```text
modules/addons/bisup_ptr_port25/
```

## Current Structure

```text
modules/addons/bisup_ptr_port25/
  bisup_ptr_port25.php
  hooks.php
  clientarea.php
  admin.php
  lib/
    AuditLogger.php
    Database.php
    KycManager.php
    NotificationManager.php
    RequestManager.php
  templates/
    admin_request_detail.tpl
    admin_requests.tpl
    clientarea.tpl
docs/
  DEVELOPMENT.md
  INSTALLATION.md
  SECURITY_AND_AUDIT.md
prd.txt
```

## Workflow

1. Client opens the request page from WHMCS.
2. Client selects an active service and submits PTR/Port 25 details.
3. Client uploads KYC documents and accepts the anti-spam declaration.
4. Staff reviews the request in the addon admin page.
5. Senior/admin staff approve, reject, request more info, suspend, or flag abuse.
6. Technical staff manually perform PTR/Port 25 changes only after approval.
7. Every staff action is recorded in the audit log.

## Statuses

- `pending_kyc`
- `submitted`
- `under_review`
- `more_info_required`
- `approved`
- `rejected`
- `enabled`
- `suspended`
- `abuse_flagged`

## Development Status

This repository contains the first implementation scaffold. It is ready for WHMCS integration testing in a staging WHMCS install before production use.

See [docs/INSTALLATION.md](docs/INSTALLATION.md) and [docs/SECURITY_AND_AUDIT.md](docs/SECURITY_AND_AUDIT.md).

