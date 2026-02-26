The cache should be cleared:
* after changing global parameters (.env, .parameters.yml)
* before testing
* when the branch was switched
* ...

We have 3 different environments in the project. (dev, test, prod)<br/>
Make sure to clear the correct cache.
```
bin/console cache:clear --env=dev
```
```
bin/console cache:clear --env=test
```
```
bin/console cache:clear --env=prod
```