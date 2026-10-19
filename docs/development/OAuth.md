**We are using HWIOAuthBundle for the authentication of the users using OAuth2.0.**

The HWIOAuthBundle is already installed and configured inside our project.
If you want to add support for a new provider then first head to https://github.com/hwi/HWIOAuthBundle to check
if the provider you want to add is currently supported in HWIOAuthBundle.

## Configure new resource owner

In order to configure a new resource owner you should go to the hwi_oauth.yaml file.

First add under fosub properties tag the following line:

`your_provider_name: your_provider_name_id`

Then under the resource_owners tag add the following lines:

```
   hwi_oauth:
    resource_owners:
        resource_owner_name:
            type:                resource_owner_name (your provider name)
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                display: popup
                csrf: true
```

Some providers also allow to add scope as a parameter and some other parameters. If you want to check which parameters can be added for the specific provider please refer to the HWIOAuthBundle documentation and official OAuth documentation of the specific provider.

Client Secrets and ID's can be created in the developers console of the selected resource owner. You should first head to the developers console of the resource owner that you want to add and create there application. When you create the app you will get client secret and ID and these should be stored in our env file in order that they can be accessed by HWIOAuthBundle

## Configure oauth firewall

In order to configure oauth firewall go to security.yaml file and under the oauth resource_owners tag add the login path:

```
         oauth:
                remember_me: true
                resource_owners:
                    google:             "/login/check-google"
                    facebook:           "/login/check-facebook"
                    apple:              "/login/check-apple"
                    provider_name:      "login/check-your_provider_name"
```

After this you will need also configure FOSUBUserProvider service. In order to do that go to the services.yaml file and under my.oauth_aware.user_provider.service arguments tag add your provider id as the parameter in the array:

```
  my.oauth_aware.user_provider.service:
    class: App\Catrobat\Security\FOSUBUserProvider
    arguments:
      - '@fos_user.user_manager'
      - {google: google_id, facebook: facebook_id, apple: apple_id, your_provider_name: your_provider_name_id}
```

The login path that we added in security.yaml should also be added to the routes.yaml file:

```
provider_name_login:
    path: /login/check-your_provider_name
```

After configuring everything in yaml files you need to create two new fileds and their setters and getters in the User entity for storing provider id and access token.
Please follow the naming convetions!

```
/**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $your_provider_name_id = null;
 /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $your_provider_name_access_token = null;
```

You succesfully configured new provider.
The last thing you need to do is to add a button for your new provider in oauth_registration.html.twig with the right path and service name.

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
