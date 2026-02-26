Database migrations are a way to safely update your database schema both locally and on production. Instead of running the doctrine:schema:update command or applying the database changes manually with SQL statements, migrations allow to replicate the changes in your database schema in a safe manner.

Migrations are available in Symfony applications via the DoctrineMigrationsBundle, which uses the external Doctrine Database Migrations library. Read the [documentation](https://www.doctrine-project.org/projects/doctrine-migrations/en/current/index.html) of that library if you need a general introduction about migrations.

### Important

**Your changes to the database should be done in the _src\Entity_ folder (eg. FeaturedProgram.php) and not directly in the database (eg. with the help of phpmyadmin). Afterward, you can automatically generate and apply the migrations files.**

### Step-by-step guide to create migrations

If you have a new branch and you need to work on the database (changes, updates, ...), you should check the database first with:

```
bin/console doctrine:migrations:status
```

There should be no New Migrations and the Executed and Available Migrations are in sync.
Apply the changes you need to the corresponding entity file. After that, create the migration with:

```
bin/console doctrine:migrations:diff
```

Now with "git status" there should be a new file in _src/Migrations_. This file must be committed together with all the changes you have made in that ticket branch.

### Step-by-step guide to migrate migrations

If there are new migrations check it with:

```
bin/console doctrine:migrations:status
```

There should be New Migrations. Add this new migration with:

```
bin/console doctrine:migrations:migrate
```

Check the migration status again, now the database should be updated.

```
bin/console doctrine:migrations:status
```

### If you have never used migrations before and "status" says no Executed Migrations but you have Available Migrations:

Drop the database schema:

```
bin/console doctrine:schema:drop --force
```

Execute the migrations:

```
bin/console doctrine:migrations:migrate
```
