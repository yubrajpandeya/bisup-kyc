# Security Policy

## Supported Versions

This project is in initial development. Security fixes are applied to the `main` branch until versioned releases are introduced.

## Reporting a Vulnerability

Please do not report suspected vulnerabilities through public GitHub issues.

Report vulnerabilities privately to the repository maintainer or the organization security contact. Include:

- A concise description of the issue
- Affected files or module areas
- Steps to reproduce, if available
- Potential impact
- Suggested mitigation, if known

## Sensitive Data Handling

This module may process KYC documents and personally identifiable information. Production deployments should verify:

- KYC storage is not publicly accessible
- WHMCS admin roles are restricted appropriately
- HTTPS is enforced
- Backups are protected
- Retention and deletion policies comply with applicable requirements

