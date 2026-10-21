# Importing a custom Catrobat project

Put downloaded `.catrobat` files in a directory and import them as user `catroweb`:

```bash
bin/console catrobat:import ~/share_projects catroweb
# Docker:
docker cp ~/share_projects app.catroweb:/var/www/catroweb/import_dir
docker exec -it app.catroweb bin/console catrobat:import import_dir catroweb
```

`bin/console catrobat:import:share` imports recent projects from share.catrobat.org instead.
