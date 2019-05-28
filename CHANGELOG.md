# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
Proposed new version number: `2.1.1`. Urgently to be merged into `master` as a hotfix.
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
[Unreleased]: https://github.com/Catrobat/Catroweb-Symfony/compare/v2.1.0...HEAD
[2.1.0]: https://github.com/olivierlacan/keep-a-changelog/releases/tag/v2.1.0
