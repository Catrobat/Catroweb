/**
 * Keeps the BEARER JWT cookie alive by periodically pinging the server.
 *
 * Since both BEARER and REFRESH_TOKEN are HttpOnly, JS cannot read or
 * refresh them directly. Instead, we make a lightweight request every
 * 45 minutes. The server-side RefreshBearerCookieOnKernelRequestEventListener
 * detects the expired BEARER, validates the REFRESH_TOKEN, and sets a
 * fresh BEARER cookie in the response.
 *
 * The JWT_TTL is 3600 seconds (1 hour), so pinging every 45 minutes
 * ensures the token is refreshed before it expires.
 */
export class TokenExpirationHandler {
  constructor() {
    const routingDataset = document.getElementById('js-api-routing')?.dataset
    if (!routingDataset) return

    this.baseUrl = routingDataset.baseUrl || ''

    // Ping every 45 minutes (JWT_TTL is 3600s = 60min)
    this.interval = setInterval(() => this.pingServer(), 45 * 60 * 1000)
  }

  pingServer() {
    // A simple HEAD request to the notifications count endpoint.
    // It's lightweight (no body), authenticated (sends cookies),
    // and triggers the server-side BEARER refresh if needed.
    fetch(this.baseUrl + '/api/notifications/count', {
      method: 'GET',
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    }).catch(() => {
      // If the ping fails (e.g., no network), stop retrying
      clearInterval(this.interval)
    })
  }
}
