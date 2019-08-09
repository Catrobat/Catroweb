# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
Proposed new version number is `2.3.0` 
### Added
- `tests/testdata/log` was missing -> added now (#326)
- rename user account feature + translations (#330)
- added more from <user> on details page (#331) 
- added tutorial tag (#334)
- human readable text insteaad of html quargel for search input (#341)
- admin falvor checks (#344)
- added embroidery flavor (#347)
### Changed
- themes will now be accepted via user agent not in link (#325)
- increased max. description length to 10.000 chars (#329)
- allow deep indexing by crawlers (#332)
- show categories (featured programs...) in sidebar (#333) 
- generate apk button hidden for iOS users (#335)
- project loader refactored (#336)
- renaming `program` to `project`, `profile` to `user`.. (#337)
### Security 
### Fixed
- private programs now really private (#328)
- program statistics design fix (#327)
- optimized images for web traffic (#338)
- keep aspect ration when cropping thumbnails (#339) 
- search uses `AND` instead of `OR` (#342)
- fixing admin RAM cake (#343)
- ficed a bug in `FlavorListener` (#348) 
### Removed
- removed the possibility to log into facebook (#324)
- removed the possibility to post programs to facebook (#324)
- removed legacy remix notification (#340) 
- request uri too long (#345)
### Api
- only public programs are now returned from api (#328)

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
[Unreleased]: https://github.com/Catrobat/Catroweb-Symfony/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/Catrobat/Catroweb-Symfony/compare/v2.1.1...v2.2.0
[2.1.1]: https://github.com/Catrobat/Catroweb-Symfony/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/olivierlacan/keep-a-changelog/releases/tag/v2.1.0
