# Security and Audit Policy

## Operational Rule

No Bisup staff member should create PTR records or enable outgoing Port 25 outside this WHMCS approval module.

The WHMCS request record is the source of truth for:

- Client identity
- Service ownership
- IP address
- KYC evidence
- Staff review notes
- Approval decision
- Technical enablement status
- Audit history

## KYC Storage

KYC files are stored under the WHMCS attachments directory by default:

```text
{attachments_dir}/bisup_ptr_port25/
```

The module validates:

- File extension
- MIME type
- File size
- Upload error status

Executable file types are not allowed.

## Audit Events

The module logs:

- Request creation
- Status changes
- Review notes
- KYC document uploads
- Approval
- Rejection
- More-info requests
- Enablement marking
- Suspension
- Abuse flagging

Each log entry stores:

- Request ID
- Admin ID or client ID
- Action
- Old status
- New status
- Note
- IP address
- Timestamp

## Manual Technical Enablement

Version 1 does not automate PTR or firewall changes.

After approval, technical staff may manually create PTR records or enable outgoing Port 25, then mark the request as `enabled` in the module. That status change is audited.

## Version 2 Automation Guardrails

Any future automation must:

- Only run after an approved request exists
- Log provider/API responses
- Preserve staff accountability
- Allow rollback or suspension
- Never bypass KYC and moderation

