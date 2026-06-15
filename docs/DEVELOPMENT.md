# Development Notes

## Scope

The first version is a WHMCS addon module, not a provider automation tool.

Automatic PTR creation, provider API calls, and Port 25 firewall changes are intentionally out of scope for the current release.

## Main Entry Points

- `bisup_ptr_port25.php`: WHMCS addon metadata, activation, client-area entry, admin output.
- `clientarea.php`: Client request submission flow.
- `admin.php`: Admin list/detail/status workflow.
- `hooks.php`: WHMCS sidebar link and lifecycle hooks.
- `lib/Database.php`: Table schema.
- `lib/RequestManager.php`: Request CRUD and status changes.
- `lib/KycManager.php`: Upload validation and storage.
- `lib/AuditLogger.php`: Audit trail.
- `lib/NotificationManager.php`: Notification integration layer.

## Database Tables

- `mod_bisup_ptr25_requests`
- `mod_bisup_ptr25_kyc_documents`
- `mod_bisup_ptr25_audit_logs`
- `mod_bisup_ptr25_settings`

## Coding Rules

- Use WHMCS Capsule for database access.
- Validate service ownership before accepting a request.
- Keep KYC files outside public web paths.
- Add audit logs for every meaningful state change.
- Any future automation must require an `approved` request status.
