# LFT Dumaguete — Relational Database Integration Audit

## Applied fix
- `Booking/index.php`: both walk-in and advance booking INSERT statements now populate `bookings.space_id` using the selected space name, while retaining the legacy `bookings.space` display value. This removes dependence on undocumented database triggers for newly created bookings. PHP syntax checked.

## Verified in uploaded source
- Database dump: `database/lft_dumaguete.sql` defines foreign keys for bookings/users, bookings/spaces, messages/users, event registrations, user memberships, space amenities, and amenities/site settings.
- Admin space deletion preserves spaces referenced by bookings by unpublishing them; space rename updates legacy booking names.
- Customer/admin authentication uses password hashing and verification.
- All PHP files pass `php -l` syntax checks.

## Outstanding before production
1. `DATABASE_REVISION_README.txt` references `database/migrations/2026_11_connected_schema.sql`, but that file is **not present** in the supplied archive. Do not follow that migration instruction. The supplied SQL dump appears to contain the new relationships already; use it only for a new test database, never overwrite a live database with sample data.
2. `database/lft_dumaguete.sql` has no booking space synchronization triggers. Existing records are populated, and the booking creation code is now patched, but any other direct SQL insert needs to set `space_id` explicitly.
3. `user_memberships`, `event_registrations`, and `space_amenities` relationships are present, but the application does not implement all associated enrollment/registration/assignment workflows.
4. `Includes/db.php` contains local XAMPP root credentials. Use environment-based configuration and a least-privileged database account for deployment.
5. `bookings.user_id` uses ON DELETE CASCADE: deleting a user could delete booking/payment history. Review retention rules and consider RESTRICT or soft-deletion.
6. Existing pricing values are inconsistent across `rates` descriptive strings and `hourly_rate` numeric fields; review prices before enabling real transactions.
7. `scripts/verify-connected-schema.php` only prints counts; despite the README's implication, it does not exit nonzero when it finds violations.
8. Live database connectivity, payment flows, permission boundaries, and end-to-end booking workflows have **not** been tested in this environment.

## Suggested test sequence
1. Back up current MySQL database and website.
2. Import the SQL dump into a fresh local `lft_dumaguete` test database.
3. Run customer registration/login, booking for both types, and verify `SELECT id,space,space_id FROM bookings ORDER BY id DESC LIMIT 5;` has valid space IDs.
4. Test payment, receipt, staff check-in, admin space rename/unpublish, contact form, and role restrictions.
5. Confirm relationship integrity with `php scripts/verify-connected-schema.php` and manually inspect every nonzero count.
6. Add workflows for memberships, events, and space amenities as needed.

Status: **Partially finalized; NOT production-certified.**
