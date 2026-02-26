Always clear the test cache before starting the tests!

```
  bin/console cache:clear -e test
```

| command                                                                                                       | description                                   |
| ------------------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| google-chrome-stable --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --disable-gpu | Starts Headless Chrome                        |
| bin/phpspec run                                                                                               | Start all phpspec tests                       |
| bin/phpspec run <path>                                                                                        | Start a specific phpspec file                 |
| bin/phpunit tests                                                                                             | Start all phpUnit tests                       |
| bin/behat                                                                                                     | Start all Behat tests                         |
| bin/behat -f pretty                                                                                           | Start all Behat Tests with detailed output    |
| bin/behat -s XXX                                                                                              | Start a Testing Suite                         |
| bin/behat scr/.../Features/.../fancyTests.feature                                                             | Start all tests in a .feature file            |
| bin/behat scr/.../Features/.../fancyTests.feature:63                                                          | Start a specific test in a .feature file      |
| bin/console phpcheckstyle                                                                                     | Generates a new php coding style check report |
