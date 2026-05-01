# PHARMACIA: Pharmacy Management System

## README Analysis (Current Codebase)
This repository contains a PHP/MySQL web application for pharmacy operations. The current implementation includes role-based dashboards and modules for inventory, sales, purchases, suppliers, customers, employees, alerts, and reporting.

This README is intentionally analysis-focused: it describes what is present in the repository today, where setup can fail, and what needs attention.

## Project Scope
- Technology stack: PHP, MySQL/MariaDB, HTML/CSS/JavaScript.
- Entry point: `index.php` redirects to `modules/auth/login.php`.
- Main role areas:
	- `modules/admin/` (full operational modules)
	- `modules/pharmacist/` (restricted operations)
	- `modules/cashier/` (sales-focused operations)

## Repository Structure (High-Level)
```text
Pharmacy-Management-System-/
	config/
		config.php
		security.php
	database/
		install.php
		populate_sample_data.php
		ER_Diagram.png
		RelationalModel.png
	includes/
		session_check.php
		alerts.php
		activity_logger.php
	modules/
		auth/
		admin/
		pharmacist/
		cashier/
	assets/
	SYSTEM_GUIDE.md
```

## Feature Analysis
### Implemented Areas
- Authentication flow with session variables and role-based redirects.
- Admin modules for inventory, sales/POS, purchases, suppliers, customers, employees, alerts, and reports.
- Pharmacist and cashier dashboards and sales/customer views.
- Alerting and activity log helpers.
- Installation script that creates core tables, views, and triggers.

### Data Model (from `database/install.php`)
Core tables created in the installer include:
- `roles`
- `employee`
- `customer`
- `meds`
- `suppliers`
- `sales`
- `sales_items`
- `medicine_batches`
- `purchase`
- `refunds`
- `activity_logs`
- `audit_log`

Database logic included:
- Trigger to increase stock after purchase insert.
- Trigger to decrease stock after sales item insert.
- Views for daily sales, low stock, expiry alerts, and sales details.

## Security Analysis
`config/security.php` includes:
- CSRF token generation/verification helpers.
- Input validation/sanitization helpers.
- Password hashing support (Argon2ID helper).
- Session hardening and timeout checks.
- Security headers and HTTPS enforcement logic.

Note: HTTPS enforcement and strict cookie settings can impact local development if not adjusted for localhost behavior.

## Setup Analysis
Two setup paths currently exist, and they are not fully aligned.

### Path A: Local installer (`database/install.php`)
1. Configure local MySQL server (default in installer: host `localhost`, user `root`, empty password, DB `pharmacy_db`).
2. Run installer via browser or CLI-accessible PHP environment.
3. Installer creates schema, triggers/views, and default admin employee record.

### Path B: External DB config (`config/config.php`)
- Current file is configured for an Aiven-hosted MySQL endpoint using SSL and `DB_PASS` environment variable.
- This is environment-specific and may fail immediately on machines without those credentials/certificates.

## Known Inconsistencies and Risks
- `README.md` (previous version) described outdated paths and credentials.
- `database/populate_sample_data.php` references columns/tables that do not fully match `database/install.php` schema (for example, role and medicine field differences, plus `admin`/`emplogin` usage).
- Login query in `modules/auth/login.php` uses lowercase column names (`username`, `password`) while installer creates `E_Username`, `E_Password`.
- `includes/activity_logger.php` currently uses a relative include path that may not resolve correctly from its location.
- `config/config.php` currently echoes a success message on DB connect, which can interfere with redirects/output headers.

## Recommended Local Development Baseline
For reliable local testing, align these files before feature work:
1. `config/config.php`
2. `database/install.php`
3. `database/populate_sample_data.php`
4. `modules/auth/login.php`

Suggested baseline:
- Use one canonical schema.
- Use one credential format and matching password verification strategy.
- Keep sample data script synchronized with the same schema.

## Default Access (If Schema/Auth Are Aligned)
- The installer inserts an admin user with username `admin` and password `admin123` (hashed at insert time).
- Additional sample credentials from `populate_sample_data.php` are only valid if that script is corrected and successfully executed against a matching schema.

## Screenshots and System Docs
- UI captures are available in the `Screenshots/` directory.
- Supplemental guide: `SYSTEM_GUIDE.md`.
- Database diagrams: `database/ER_Diagram.png`, `database/RelationalModel.png`.

## Contribution Note
Before adding new features, prioritize schema/auth consistency fixes. This will prevent breakage across dashboards and role-based login flows.





