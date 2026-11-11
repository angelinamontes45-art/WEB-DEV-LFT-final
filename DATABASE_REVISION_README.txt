LFT Dumaguete – project + database revision (MariaDB 10.4)

IMPORTANT: This is a compatibility-preserving incremental upgrade, not a clean-slate rewrite.

1. BACK UP your current database and website files.
2. Restore a COPY of lft_dumaguete on a local test instance.
3. In phpMyAdmin select lft_dumaguete, then import database/migrations/2026_11_connected_schema.sql ONCE.
4. Deploy the PHP project from this ZIP to the test site.
5. Test customer login, booking, payment, staff check-in, admin space rename/unpublish, and contact form.
6. Run php scripts/verify-connected-schema.php and investigate nonzero results.
7. Only then consider production deployment with a fresh backup.

Schema additions:
- bookings.space_id -> spaces.id, alongside legacy bookings.space for PHP compatibility.
- messages.user_id -> users.id (optional for anonymous contacts).
- user_memberships (users <-> memberships), event_registrations (users <-> events), space_amenities (spaces <-> amenities).
- site_id on user-facing catalog/content tables as a proposed site ownership link.
- Internal doctrine_migration_versions and messenger_messages remain independent infrastructure tables. They SHOULD NOT receive artificial foreign keys merely to force a connected ERD.

Code changes:
- Contact form saves user_id for logged-in senders.
- Admin space delete unpublishes a space with booking history instead of destroying the record.
- Admin space rename keeps legacy bookings.space display text synchronized.
- Existing booking insert statements are supported by the migration's space_id triggers.

LIMITATIONS:
- Migration is NOT idempotent; do not run twice.
- The app's database/schema.php is a legacy SQLite schema utility; the live app uses MySQL/MariaDB through Includes/db.php. It is not used as the migration source.
- Membership purchase/enrollment and event registration workflows are NOT implemented by the existing application; the new junction tables remain empty until those workflows are built.
- All relationships in the ERD should be meaningful. Framework tables need not connect to business entities.
- This package was PHP syntax-checked where PHP CLI is available, but no live MariaDB integration test was possible here.
