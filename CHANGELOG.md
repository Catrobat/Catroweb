# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.22.6]
  - Add DKIM mail signing
  - update dependencies
  - update translations API & features
  - Add refresh token to register API
  - Add refresh token to OAuth API
  - Fix verify/email service controller registration
  - Fix ResetPasswordSubscriber event
  - Disable Stacktrace logging and increase min level to warning
  - Get rid of bs container-max with to better fit the sidebar design
  - Slightly enhance authentication desktop design
  - fix bearer token-expiration null pointer exception
  - increase behat test window size to a more modern size 335->360

## [3.21.1]
- remove fos_user registration & reset password
- add symfonycasts reset password
- add symfonycasts confirm registration
- use API for registration
- replace registration terms of use popup with information text
- add symfony/mailer
- add response cache to project categories API
- update dependencies
- update translations API & features

## [3.20.34]
- update dependencies
- update translations API & features
- download counter restrictions
- edit project name
- add autoprefixer
- ...

## [3.19.0]
  - update dependencies (ElasticSearch v7, ...)
  - update translations
  - add ETag caching to language translation api
  - Improved feedback loop when users trigger a download
  - Removed email registration confirmation email
  - Enhanced language selection menu

## [3.18.3]
  - update dependencies
  - update translations
  - studio enhancements
  - fix password visibility toggle
  - clean up configs
  - enhance webpack usage, js, behat hooks, ..

## [3.17.2]
  - update dependencies (bootstrap 4 => 5!)
  - update translations
  - new welcome section
  - enable random project category with optimized performance
  - optimize tag/extension display

## [3.16.0]
  - new Achievement + Tag for #catrobatfestival2021
  - update dependencies
  - update translations

## [3.15.2]
  - update dependencies
  - update translations
  - webpack improvements

## [3.14.0]
  - tag & extensions rework
  - update dependencies
  - update translations
  - new media-lib design (part 1)

## [3.13.0]
- update dependencies
- update translations
- Adds new iTranslate features
- Adds new flavors
- basic studio logic: activity list, member list, detail view, admin settings
- error logging translate api

## [3.12.3]
- update/fix dependencies
- update translations
- remove random category from startpage
- Adds new iTranslate features: admin interface, 
- Adds new flavors

## [3.11.1]
- update dependencies
- update translations
- fix image upload bug
## [3.11.0]
- enable Achievements for production  
- new Achievement animation  
- Achievement user view  
- build dir is now built on demand (remove compiled webpack files)
- update dependencies
- update translations
- iTranslate next steps / advancements
## [3.10.1]
- privacy policy is now a redirect
- update dependencies
- update translations
- minor cron job improvements
- removing dead code
- fix sass deprecations
## [3.10.0]
- Backend for Studios added
- Achievements Events added
- Admin interface for cron job
- Admin interface for special updater
- Admin interface for static achievements data
- iTranslate API improved & extended
- update dependencies
- update translations
- increase and fix psalm issues to lvl 4
## [3.9.0]
- new API route: authentication/upgrade
- Removed deprecated unused API routes
- Updated dependencies
- Fix tabs on user profiles
- Fix invisible loading spinners
- Disable click outside popups  
- Achievements Overview + Updater 
- Part 1 iTranslate integration  
- Internal enhancements
## [3.8.5]
- Upgraded API version
- Updated dependencies
- Added automatic release PR creation to GA
- GitHub Actions - cancel duplicated runs  
- Admin Area refactoring
- Minor internal refactorings
## [3.8.3]
- Enable auto deployment
## [3.8.2]
- internal API rework
- better translation fallbacks
- fix docker image (code coverage)
- adapt GitHub Actions to reduce overhead and prevent crowdin clashes with other teams
## [3.8.1]
- Translation Hotfix
- Dependencies
## [3.8.0]
- Added new API version 
  - new routes for survey
  - new categories oversight
- Dependencies
## [3.7.1]
### Fixed 
- fixes preloading of images
- somue dependency updates
## [3.7.0]
### Added
- New Frontend for APK Signing (hidden until backend finished)
- New Catblocks release :hibiscus:
- Add translation capability to project and comments via link to google translation
### Changed
- CAPI update 1.0.52 
    - (GET|HEAD requests contain hash)
    - Projects now have Tag information
    - Register endpoint returns token
    - Report API supports bearer and upload token
- Admin Area
    - Refactoring
    - Better Logging
- Dependencies
- Optimized lazy loading (images)
- Preloading (fonts, css)
### Fixed
- use official HWIOauth Bundle
- Show path in remix graph working again
- Microsoft Edge Support
- CAPI
    - Added project id and project url
## [3.6.0]
### Added
- serve images as webp
- CAPI v??
- Huawei App Gallery
- project credits & discription syncronized with XML
- new catblocks release
- username in project search
### Changed
- use webpack encore
- help pages replaced
- change buttons to material
### Fixed
- Regex Hotfix
- fixed rremix graph on open
- language dropdown, shows correct language in certain browsers
### Security
- API Hotfix
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
- Fixes a problem in db connection with `server_version` in `doctrine.yml`([DoctrineSource])
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
- Headlines are now centered throughout all designs.
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

[DoctrineSource]: https://github.com/doctrine/DoctrineBundle/issues/351
[Unreleased]: https://github.com/Catrobat/Catroweb/compare/v3.23.0...HEAD
[3.22.6]: https://github.com/Catrobat/Catroweb/compare/v3.22.6...v3.23.0
[3.21.1]: https://github.com/Catrobat/Catroweb/compare/v3.21.1...v3.22.6
[3.20.34]: https://github.com/Catrobat/Catroweb/compare/v3.20.34...v3.21.1
[3.19.0]: https://github.com/Catrobat/Catroweb/compare/v3.19.0...v3.20.34
[3.18.3]: https://github.com/Catrobat/Catroweb/compare/v3.18.3...v3.19.0
[3.17.2]: https://github.com/Catrobat/Catroweb/compare/v3.17.2...v3.18.3
[3.16.0]: https://github.com/Catrobat/Catroweb/compare/v3.16.0...v3.17.2
[3.15.2]: https://github.com/Catrobat/Catroweb/compare/v3.15.2...v3.16.0
[3.14.0]: https://github.com/Catrobat/Catroweb/compare/v3.14.0...v3.15.2
[3.13.0]: https://github.com/Catrobat/Catroweb/compare/v3.13.0...v3.14.0
[3.12.3]: https://github.com/Catrobat/Catroweb/compare/v3.12.3...v3.13.0
[3.11.1]: https://github.com/Catrobat/Catroweb/compare/v3.11.1...v3.12.3
[3.11.0]: https://github.com/Catrobat/Catroweb/compare/v3.11.0...v3.11.1
[3.10.1]: https://github.com/Catrobat/Catroweb/compare/v3.10.1...v3.11.0
[3.10.1]: https://github.com/Catrobat/Catroweb/compare/v3.10.0...v3.10.1
[3.10.0]: https://github.com/Catrobat/Catroweb/compare/v3.9.0...v3.10.0
[3.9.0]: https://github.com/Catrobat/Catroweb/compare/v3.8.7...v3.9.0
[3.8.5]: https://github.com/Catrobat/Catroweb/compare/v3.8.6...v3.8.7
[3.8.5]: https://github.com/Catrobat/Catroweb/compare/v3.8.5...v3.8.6
[3.8.4]: https://github.com/Catrobat/Catroweb/compare/v3.8.4...v3.8.5
[3.8.3]: https://github.com/Catrobat/Catroweb/compare/v3.8.3...v3.8.4
[3.8.2]: https://github.com/Catrobat/Catroweb/compare/v3.8.2...v3.8.3
[3.8.2]: https://github.com/Catrobat/Catroweb/compare/v3.8.1...v3.8.2
[3.8.1]: https://github.com/Catrobat/Catroweb/compare/v3.8.0...v3.8.1
[3.8.0]: https://github.com/Catrobat/Catroweb/compare/v3.7.1...v3.8.0
[3.7.1]: https://github.com/Catrobat/Catroweb/compare/v3.7.0...v3.7.1
[3.7.0]: https://github.com/Catrobat/Catroweb/compare/v3.6.0...v3.7.0
[3.6.0]: https://github.com/Catrobat/Catroweb/compare/v3.5.0...v3.6.0
[3.5.0]: https://github.com/Catrobat/Catroweb/compare/v3.4.5...v3.5.0
[3.4.5]: https://github.com/Catrobat/Catroweb/compare/v3.4.4...v3.4.5
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
