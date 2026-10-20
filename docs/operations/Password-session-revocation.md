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

An actual username change revokes every refresh token under the original and
resulting names before issuing one replacement session for this authenticated
request, including a bearer-only caller. This keeps the existing successful
cookie response while preventing old refresh records from following a username
into a later registration. The new refresh cookie remains usable as proof for
a later password change. An ordinary profile save that submits the unchanged
username still receives fresh authentication cookies as before, while preserving
all existing refresh sessions, including the previous current-session token.
Saving biography or currently-working-on fields must not sign out other devices.

Password resets revoke every refresh token, consume the reset proof in the same
transaction, and clear the reset caller's authentication cookies. A reset link
provides no trusted current refresh session. Other clients cannot use their old
refresh tokens afterward.

Credential persistence, revocation and replacement persistence use one database
transaction through the separate [account session coordinator](Account-session-coordination.md).
That prerequisite coordinates account writes with password login and refresh
issuance and handles stale authentication rejection. Stale password/username
PATCH requests return 401 and clear the caller's cookies and pending automatic
cookie refresh. Invalid reset proof and stale password rejection leave Doctrine
usable for the remaining request. On rejected authentication, unrelated session
preferences and other clients' refresh sessions are preserved. The existing
suspended cookie-mode appeal/profile exemptions and OAuth sign-in semantics
remain unchanged; ordinary suspended API login and refresh stay blocked.

Fresh access tokens include a random `jti`, so replacing a token within the same
second produces a different token. The existing JWT cutoff rule remains: `iat`
has second precision and tokens in the same second as a credential change can
survive that cutoff. This change does not introduce subsecond access-token
revocation. Account-claim and ban revocation behavior remains in place. Deploy
the UTC cutoff fix alongside this change for timezone-independent persistence
and hydration of access-token cutoffs.
