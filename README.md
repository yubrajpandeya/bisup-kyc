# Bisup WHMCS PTR & Port 25 Approval Module

A WHMCS addon module for reviewing PTR/rDNS and outgoing Port 25 requests through a documented KYC, approval, and audit workflow.

The first release focuses on governance and operational control:

- KYC collection
- Client/service/IP request capture
- Admin moderation
- Approval/rejection workflow
- Staff accountability
- Audit logs
- Notification-ready workflow events

PTR creation and Port 25 firewall automation are outside the current release scope. The module is designed to establish the approval record before any network or DNS change is performed.

## Repository Status

This project is in initial development and should be tested in a WHMCS staging environment before production deployment.

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
6. Technical staff complete any required DNS or network changes after an approved request exists.
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

## Security

This module handles sensitive identity documents. Review [SECURITY.md](SECURITY.md) and [docs/SECURITY_AND_AUDIT.md](docs/SECURITY_AND_AUDIT.md) before deployment.

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Development Notes](docs/DEVELOPMENT.md)
- [Security and Audit](docs/SECURITY_AND_AUDIT.md)
