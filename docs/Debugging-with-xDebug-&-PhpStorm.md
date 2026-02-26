## Prerequisites

1. Install [xdebug](https://xdebug.org/docs/install) using
`pecl install xdebug`

2. Update the `php.ini` files (make sure to use the correct php version!):

   * CLI:
    for debugging in the command line (e.g tests), you have to update the `/etc/php/7.4/cli/php.ini` file.
    Append the following snippet at the end of the file
    ```
    [xdebug]
    xdebug.remote_enable=1
    ````
   * APACHE:
    for debugging, while browsing the localhost, you have to update the `/etc/php/7.4/apache2/php.ini` file.
    Append the following snippet at the end of the file
    ```
    [xdebug]
    xdebug.remote_enable=1
    ````
   * ...
 
3. For debugging in the browser make sure to install & enable the necessary extensions.
   * Chrome: [xdebug helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc?hl=de)

## How to debug

1. Set some breakpoints for a file in PhpStorm. (Just click next to the line number, you should see a red point if you have set a breakpoint)

2. Start listening for PHP Debug Connections in PhpStorm. This button should be at the top right. Just next to the Run and Debug buttons. The icon looks like an old telephone.

3. The next part is environment-specific:
   * Browser: Nothing to do, the extension should handle everything, just trigger the breakpoint and check Phpstorm.

   * CLI: append `php -dxdebug.remote_autostart=On` in front of your command <br/>
    E.g. `php -dxdebug.remote_autostart=On bin/behat -f pretty tests/behat/features/web/click_statistics.feature:120`

   * Api tools like postman: just append xdebug as query param `XDEBUG_SESSION_START=PHPSTORM` <br/>
    E.g. `http://localhost/api/projects/mostViewed?XDEBUG_SESSION_START=PHPSTORM`

4. Now have fun debugging. 
   *    Jump from breakpoints to breakpoints.
   *    Step through the code step by step.
   *    Take a look at the current state of variables and so on.


## Notes:

Sometimes it will take a while for behat tests to run while using xdebug. Just wait for them to start.


