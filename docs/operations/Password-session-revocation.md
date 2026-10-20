# Password changes and refresh sessions

A successful password change revokes all old refresh tokens. An authenticated
password PATCH preserves the current browser only if its `REFRESH_TOKEN` cookie
names a valid stored token belonging to that account. The old token is revoked
and a persisted replacement plus a fresh access token are delivered in the
existing HttpOnly `REFRESH_TOKEN` and `BEARER` cookies. No response body tokens
or new preservation headers are introduced.

Bearer-only API callers, missing/expired cookies and cookies for another account
preserve no session. The response clears authentication cookies; clients must
sign in again with the new password. The endpoint still returns 204. Dry runs
and rejected password changes leave sessions intact. Combined username/password
changes revoke tokens under the original and resulting usernames.

Password resets revoke every refresh token, consume the reset proof in the same
transaction, and clear the reset caller's authentication cookies. A reset link
provides no trusted current refresh session. Other clients cannot use their old
refresh tokens afterward.

Password persistence, revocation and replacement persistence use one database
transaction. Refresh issuance and password-based API/form login issuance lock the
same account row. Username-only profile updates that issue replacement cookies
use the same lock and hash check, including an unchanged username. A username-only
update revokes every refresh token under the original and resulting names before
issuing one replacement session for this authenticated request, including a
bearer-only caller. This keeps the existing successful cookie response while
preventing old-name re-registration from recovering another account's sessions.
The update and token persistence occur together, so the new refresh cookie remains
usable as current-session proof. Stale password/username PATCH requests return 401 and
clear this caller's stale cookies and pending automatic cookie refresh. A rejected
form-login success handler clears the pending main authentication token and its
session entry, so the response listener cannot persist the rejected login.
Unrelated session preferences and other clients' refresh sessions are preserved.
After obtaining that lock, refresh issuance rechecks that its
proof still exists; password login compares the authenticated hash captured
before reload against the current persisted hash. Thus an in-flight refresh or
password login cannot issue a surviving refresh session after a password change
has revoked its proof. Suspension snapshots reject a newly suspended actor during an in-flight login or
profile/password operation. Existing policy remains: a user already suspended may
sign in with `X-Auth-Mode: cookie` to appeal, and the `/api/users/me` PATCH
exemption still permits their profile/password updates. Ordinary suspended API
login and refresh issuance remain blocked. OAuth provider sign-in semantics
remain unchanged.

Fresh access tokens include a random `jti`, so replacing a token within the same
second produces a different token. The existing JWT cutoff rule remains: `iat`
has second precision and tokens in the same second as a credential change can
survive that cutoff. This change does not introduce subsecond access-token
revocation. Account-claim and ban revocation behavior remains in place; the new
transaction coordination covers password change/reset and refresh/password-login
issuance. Deploy the UTC cutoff fix alongside this change for timezone-independent
persistence and hydration of access-token cutoffs.
