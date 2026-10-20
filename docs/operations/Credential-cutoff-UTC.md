# Credential cutoff UTC transition

`fos_user.credentials_changed_at` is a nullable DATETIME. The dedicated
`utc_datetime` Doctrine type writes and hydrates this field in UTC, regardless
of the PHP default timezone. Other DATETIME fields keep their existing behavior.

Legacy values have no timezone or offset. A repeated autumn DST wall time cannot
be converted reliably. Migration `Version20261020220000` replaces every non-null
legacy cutoff with the same `UTC_TIMESTAMP()` instant in one statement. It never
reinterprets the old wall time. Null cutoffs remain null. Existing access tokens
issued before that instant for affected users become invalid and must be
refreshed or the user must sign in again. The existing same-second JWT policy
still applies. This migration does not revoke refresh tokens.

Deploy with authentication writers stopped, apply the migration, and start only
UTC-aware application workers. Do not mix old local-time writers with the new
type. The production CLI timezone was observed as UTC on 2026-10-20, and no
explicit FPM `date.timezone` override was observed; this does not prove every
historic writer used UTC, hence the conservative reset.

The migration is deliberately irreversible: the original unzoned values cannot
be reconstructed safely. A rollback must retain UTC-aware credential storage and
hydration. Do not restore local-time writers against this column. Review this
cutover and its access-token expiry effect before production deployment.
