# Data consistency checks

Run the consistency check from the deployed application directory. Its default
mode reports missing project files, screenshots, thumbnails, image variants and
reports pointing at missing content. It does not modify data or regenerate files:

```sh
sudo -n -u www-data /usr/bin/php bin/console catrobat:check:data-consistency
```

Production write commands must run as
`sudo -n -u www-data /usr/bin/php bin/console ...`. Running as `deploy` can fail
on filesystem permissions. Do not add `nice`: that invocation does not match
the production sudo rule.

Review the dry-run report before choosing a write option:

```sh
sudo -n -u www-data /usr/bin/php bin/console catrobat:check:data-consistency --regenerate-thumbnails
sudo -n -u www-data /usr/bin/php bin/console catrobat:check:data-consistency --hide
```

`--regenerate-thumbnails` rebuilds missing thumbnails and variants only where a
source screenshot exists. `--hide` makes projects with no recoverable project
file invisible and flags them as broken; it never deletes them. It refuses to
hide more than 5% of scanned projects unless `--force` is explicitly supplied.
Use repeatable `--project=<id>` options to restrict the scan. Production changes
require the deployment owner's approval; a dry run does not authorize repairs.
