# Migrations

Change the schema in the Doctrine entities (`src/DB/Entity/`), never by hand in the database, then generate and commit a migration with the same PR:

```bash
bin/console doctrine:migrations:status    # must be in sync before you start
bin/console doctrine:migrations:diff      # new file under migrations/
bin/console doctrine:migrations:migrate
```

If `status` shows no executed but available migrations on a fresh local DB: `bin/console doctrine:schema:drop --force`, then `migrate`.
