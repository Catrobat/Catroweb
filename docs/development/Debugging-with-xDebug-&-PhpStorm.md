# Debugging with Xdebug & PhpStorm

The dev Docker image ships Xdebug 3 with `xdebug.mode=coverage` only. To step-debug, set `xdebug.mode=debug` (natively: in the CLI and Apache/FPM `php.ini`; install with `pecl install xdebug` or the `php8.5-xdebug` package) and restart PHP.

1. Set breakpoints in PhpStorm and click **Start Listening for PHP Debug Connections**.
2. Trigger a session:
   - Browser: the Xdebug helper extension.
   - CLI: `php -dxdebug.mode=debug -dxdebug.start_with_request=yes bin/behat -s <suite> <file>:<line>`
   - API clients: add `XDEBUG_SESSION=PHPSTORM` as a query parameter or cookie.

Behat runs noticeably slower with Xdebug enabled.
