**We are using HWIOAuthBundle for the authentication of the users using OAuth2.0.**

The HWIOAuthBundle is already installed and configured inside our project.
If you want to add support for a new provider then first head to https://github.com/hwi/HWIOAuthBundle to check
if the provider you want to add is currently supported in HWIOAuthBundle.
## Configure new resource owner
In order to configure a new resource owner you should go to the hwi_oauth.yaml file.

First add under fosub properties tag the following line:

``your_provider_name: your_provider_name_id``

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
After this you will need also configure FOSUBUserProvider service. In order to do that go to the services.yaml file and under   my.oauth_aware.user_provider.service arguments tag add your provider id as the parameter in the array:
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
After configuring everything in yaml files you need to create  two new fileds and their setters and getters in the User entity for storing provider id and access token.
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
