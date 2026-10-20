# Account session coordination

Password-based API and form login, refresh issuance, and authenticated account updates share a transaction lock on the account row. Capture the authenticated password hash, identifier and suspension state before reloading the managed User. Under the lock, reject a changed password, renamed identifier or newly suspended account before updating the account or issuing tokens.

An ordinary stale credential or refresh proof rejection leaves Doctrine usable for the remaining request. Stale account updates return HTTP 401 and clear the caller's authentication cookies. A rejected form login clears the pending main authentication token and session entry so the response cannot persist a rejected authentication.

The existing suspension policy remains: cookie-mode password login can access appeals, and an already suspended user can use exempt profile operations. Ordinary API login and refresh stay blocked while suspended. External-provider OAuth sign-in retains its existing behavior.

Direct refresh-token helpers persist the generated model. Returning a generated token without saving it would give a browser a cookie that cannot refresh.

Credential writers must use the same account lock for complete ordering with token issuance. This change prepares that coordination; password/reset revocation and username-session migration are specified separately.
