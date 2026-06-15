# Security and Audit Policy

## Approval Control

The WHMCS request record is intended to be the authoritative approval record for:

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

Recommended deployment controls:

- Keep the WHMCS attachments directory outside the public web root where possible.
- Disable direct web access to KYC storage paths.
- Restrict file downloads to authorized WHMCS admin roles.
- Use HTTPS for all client and admin traffic.
- Apply an internal retention policy for identity documents.

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

The current release does not automate PTR or firewall changes.

After approval, authorized staff can complete the required DNS or network change and then mark the request as `enabled` in the module. That status change is audited.

## Version 2 Automation Guardrails

Any future automation must:

- Only run after an approved request exists
- Log provider/API responses
- Preserve staff accountability
- Allow rollback or suspension
- Never bypass KYC and moderation

## Vulnerability Reporting

Do not open public issues for suspected security vulnerabilities. Report privately to the repository maintainer or the organization security contact.
