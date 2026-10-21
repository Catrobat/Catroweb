# OAuth

Social login uses [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle) (Google, Facebook, Apple). A new provider needs, following the existing ones: a resource owner in `config/packages/hwi_oauth.php`, its `/login/check-<provider>` path in the `oauth` firewall (`config/packages/security.php`) and `config/routes.php`, `<provider>_id`/`<provider>_access_token` fields on `User`, the mapping in `src/Security/OAuth/`, and a button in `templates/Security/OauthRegistration.html.twig`.

## Production credentials per provider

The web login buttons live in `templates/Security/OauthRegistration.html.twig`; a provider's button
should only be shown once its credentials are set in `.env.prod.local`. Each provider must list
`https://<domain>/login/check-<provider>` as an allowed redirect URI.

| Provider | Env vars                                                                             | Where to get them                                                                                                                                                                                           |
| -------- | ------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Google   | `GOOGLE_ID`, `GOOGLE_SECRET`                                                         | Google Cloud Console → APIs & Services → Credentials (OAuth client, type Web)                                                                                                                               |
| Facebook | `FB_ID`, `FB_SECRET`                                                                 | Meta for Developers → app → App settings → Basic; add "Facebook Login" with the redirect URI; app mode must be "Live"                                                                                       |
| Apple    | `APPLE_ID`, `APPLE_SECRET='auto'`, `APPLE_AUTH_KEY`, `APPLE_KEY_ID`, `APPLE_TEAM_ID` | Apple Developer → Identifiers → Services ID (= `APPLE_ID`) with the domain and redirect URI; Keys → a "Sign in with Apple" key (`.p8` = `APPLE_AUTH_KEY`, its id = `APPLE_KEY_ID`); Team ID from Membership |

Apple notes:

- `APPLE_SECRET='auto'` makes HWIOAuth sign a short-lived client secret for every login. A
  hand-made client-secret JWT is valid for at most six months and then fails silently.
- Put the `.p8` key on one line with `\n` escapes inside double quotes, like `FB_OAUTH_PUBLIC_KEY`.
- Apple returns with a cross-site form POST, which does not carry the `SameSite=Lax` session
  cookie. `App\Security\OAuth\AppleFormPostListener` re-submits that POST once from our own
  origin so the CSRF state stored in the session is found again.
