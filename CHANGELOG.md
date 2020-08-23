# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- serve images as webp
- CAPI v??
### Changed
- use webpack encore
### Fixed

## [3.5.0]
### Added
- various new bricks
- phpUnit tests
### Changed
- new projectList design
- new Notifications design
- update dependencies
- update translations
- better UX feedback by loading spinners
- Admin interface refactoring
- internal concept to store projects (allow deduplication)
### Fixed
- project download error handling
- minor style issues
- minor CI check issues
- project structure
- removed code smells
### Removed
- GameJam (will be studios in the future)
- Recommendation categories (will be re-added in a later release)

## [3.4.5]
### Fixed
- welcome section for embroidery and luna
## [3.4.4]
### Added
- Oauth Login - Google, Facebook, Apple (web only, no API) 
- Crowdin Synchronization (GitHub Action)
- CodeCov Reports (GitHub Action)
- Psalm added (Static Analysis)
### Changed
- Landing page categories use Extensions in addition to flavors to find projects.
- update dependencies
- Embroidery color and logo
- admin interface refactoring (media package category and files)
### Fixed
- remove debug projects from search results
- Symfony project structure (migrations)
- 
## [3.4.3]
### Changed
- updated dependencies
- admin interface (approve)
- remove limited users
- remove search option on code view page
### Fixed
- recommendation system
- featured projects
- max-version in search
- button css
- grammar of translations
## [3.4.0]
### Added
- New Bricks (BackgroundRequestBrick, LookRequestBrick)
- CAPI upgrades to v1.0.41 (user routes, health check, no total-results)
- Detail page for scratch projects
### Changed
- CAPI upgrades to v1.0.41 (no total-results)
- Bumped many dependencies (Thx to @dependabot)
- Multiple flavors for assets
- New project view design (remix, code view, code stats on their own pages)
- Admin Area refactoring
- Debug projects only hidden in production
### Fixed
- Default favicon.ico
- BadRequest API response in json format
- API validation messages
- Do not show webview content on mobile - ony if web-view
- Improved search (elastic search)
- Github action computation time reduced
## [3.3.4]
### Changed
- Admin Interface
- Bumped many dependencies (Thx to dependabot)
- Improved CI system
- Docker switch from Ubuntu to Debian
### Fixed
- Search queries including special character
- Project Code/Stats view will be updated on every project upload
- static images optimized
- mutable assets are no longer wrongly cached after an update (profile/project images)
## [3.3.3]
### Added
- added Apple Site Association
- CODE QUALITY TOOLS :innocent:
- New Brick (ClearUserListBrick)
- New Brick (UserBrick)
- Download Multiple Media Files (only web)
- Media Library Search
- Download whole Media Library Objects
- various Material.io Content
- SCSS coding standard
- Search Progress Indicator added
- Loading spinner for code view added
- Releasing Catblocks for the first time :birthday:
### Changed
- improved GithubActions :heart_eyes:
- complete rework of follower feature
- Major refactoring of Behat Test System
- Major refactoring of ResetCommand
- DB switched for test environment (mysql instead of sqlite3)
- Migrated to php7.4 (hyped for php8.0)
- Updated Search to find meaningful content
- Switched Backup Strategies to BORG
- Major Admin Area Refactoring
- Typography
### Fixed
- fixed consistent time access
- Featured and Approved projects cant be reported anymore (bad spammers :facepunch:)
- Invisible projects can be downloaded
- NPM & Composer Security Fixes and Updates
- Improve Font loading
- No email in username allowed anymore
- Upload with Tags / Extensions
### Removed
- remove LDAP login
- remove download of multiple media files
### API
- Catroweb-API in basic version added (v1.0.38) https://app.swaggerhub.com/apis-docs/HCrane/CAPI/v1.0.38
## [3.2.0]
### Added
- Added 4 new Blocks used in Catrobat
- Added new Scratch Remix category
- Added new Remix Notification
- Added new Remix Notification category
- Admin Area: Add a tool for finding rejected/reported programs and comments
### Changed
- generate no new Upload token upon upload
### Fixed
- account deletion refined and fixed a bug causing unnecessary code to be executed
- Bugfix for Notification System
## [3.1.1]
### Fixed
- bug when uploading a project would not extract all assets
## [3.1.0]
### Added
- User history admin view

### Changed
- Notification menu extended
- updated deploy script and create wiki entry
### Fixed

- removed project navigation from Media Library Sidebar
- removed LocaleBundle deprecation
- Bug with comments on remixed projects 
- Bangala translation fixed
- Changed buttons
- removed ContainerAwareCommand deprecation
- removed template deprecation
- CodeStatistics Logic updated and added missing bricks
- Upload bug with google account
- Hotfix forces `SECURE_SCHEMA` to `https`
- Admin interface problems because of unnecessary flavor check
- validation bug with legacy projects

### API
- `max_version` parameter is back

## [3.0.0]
### Added
- `tests/testdata/log` was missing -> added now
- rename user account feature + translations 
- added more from <user> on details page 
- added tutorial tag 
- human readable text instead of html quargel for search input 
- admin flavor checks 
- added embroidery flavor 
- added popup setting programs private/public
- added arduino flavor
- added the possibility to write credits
### Changed
- themes will now be accepted via user agent not in link 
- increase max. description length to 10.000 chars
- allow deep indexing by crawlers 
- show categories (featured programs...) in sidebar 
- generate apk button hidden for iOS users
- project loader refactored 
- renaming `program` to `project`, `profile` to `user`.. 
- Major framework upgrade `3.4` -> `4.3`
- refactored notification system (beautify, seperated into categories, mark all as read)

### Fixed
- private programs now really private 
- program statistics design fix 
- optimized images for web traffic 
- keep aspect ratio when cropping thumbnails  
- search uses `AND` instead of `OR` 
- fixing admin RAM cake (#343)
- fixed a bug in `FlavorListener`  
### Removed
- removed the possibility to log into facebook 
- removed the possibility to post programs to facebook 
- removed legacy remix notification  
- request uri too long 
### API
- return only public programs from api 

## [2.2.0]

### Added
- Filtering for `debugBuild`
- prefer programs of current flavor in API calls
### Security 
- Fixes javascript vulnerability
### Fixed
- use mobile window size for tests
- corrects search box position
### Removed
- Removed Geocoder
- Removed jQuery 2.1.0


## [2.1.1]
### Fixed
- Fixes a problem in db connection with `server_version` in `doctrine.yml`([Source])
## [2.1.0]
### Added
- Program Owner now visible on small devices too.
- User Badge in the sidebar will be updated immediately after marking a notification as read.
- Media Library assets now will be shown by name if no thumbnail is given.
- Media Library assets(sounds) can now be played without download.
- "Show Password" option added for login.
- Production logs are now rotating.
- Deployment script adapted for more privacy and more modularity.
- On the profile page all programs are loaded at once, are ordered and all own programs will be displayed.
- Headlines are now centered through out all designs.
- Programs that are uploaded ar no longer set to private if languageVersion is higher than supported.
### Changed
- Recommended Program section will only be displayed if programs for this section exist.
- Recommender System diversity enriched.
- Complete change of Media-Library (new Ajax Loading, sorting)
- Hardcoded translations switched to dynamic translations
- Usernames are now consistently visible in program details.
### Removed
- Project documentation removed from repo and relocated to github wiki.
### Fixed
- Tooltip is now displayed once not twice.
- Media-Library files are named correctly on download. (Filename not ID, works only in browser)
- Notification badge in the sidebar will now be displayed correctly.
- Clean logs command fixed.
- Limiting text to display width.
- DB Migration fixed to work correct.
- Follow Icon gets positioned correctly again.
- Color of text in search field is adjusted for "Luna & Cat"

[Source]: https://github.com/doctrine/DoctrineBundle/issues/351
[Unreleased]: https://github.com/Catrobat/Catroweb/compare/v3.5.0...HEAD
[3.4.4]: https://github.com/Catrobat/Catroweb/compare/v3.4.5...v3.5.0
[3.4.4]: https://github.com/Catrobat/Catroweb/compare/v3.4.4...v3.4.5
[3.4.4]: https://github.com/Catrobat/Catroweb/compare/v3.4.3...v3.4.4
[3.4.3]: https://github.com/Catrobat/Catroweb/compare/v3.4.0...v3.4.3
[3.4.0]: https://github.com/Catrobat/Catroweb/compare/v3.3.0...v3.4.0
[3.3.0]: https://github.com/Catrobat/Catroweb/compare/v3.2.0...v3.3.0
[3.2.0]: https://github.com/Catrobat/Catroweb/compare/v3.1.1...v3.2.0
[3.1.1]: https://github.com/Catrobat/Catroweb/compare/v3.1.0...v3.1.1
[3.1.0]: https://github.com/Catrobat/Catroweb/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/Catrobat/Catroweb/compare/v2.2.0...v3.0.0
[2.3.0]: https://github.com/Catrobat/Catroweb/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/Catrobat/Catroweb/compare/v2.1.1...v2.2.0
[2.1.1]: https://github.com/Catrobat/Catroweb/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/Catrobat/Catroweb/releases/tag/v2.1.0
