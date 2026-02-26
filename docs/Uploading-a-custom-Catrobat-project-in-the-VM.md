1. Download any Catrobat project either from local SHARE or share.catrob.at
2. The downloaded file is a zip archive and has the .catrobat extension
3. Copy the .catrobat file into any folder. (E.g ~/share_projects)
4. Invoke the import command: `bin/console catrobat:import ~/share_projects catroweb`
5. The imported project should be now imported/uploaded, and owned by the used called catroweb.

Using **docker** you need to move the projects first into the container:

```
docker cp ~/share_projects app.catroweb:/var/www/catroweb/import_dir
docker exec -it app.catroweb bin/console catrobat:import import_dir catroweb
```
