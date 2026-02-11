⚠️ **New here?**  
If you haven’t heard about Catrobat, what we do, or how you can contribute, start with our  
[step-by-step guide](https://github.com/Catrobat/Catroid/blob/develop/README.md).

💡 _If any step in the following process is unclear, misleading, or incorrect, feel free to open a pull request with improvements._

# Catroweb

Catroweb is the name of the **Pocket Code sharing platform**, where our community uploads and shares projects.  
If you uploaded your game in a previous step, it should already be visible to other users.

They can download and remix your masterpiece, while the platform also provides features typical of a social network.

You can find our API specifications here:  
👉 https://developer.catrobat.org/Catroweb/

## Team Culture

We work as a team and expect everyone to follow shared rules and best practices to ensure high-quality code.  
We support each other, but also expect newcomers to proactively familiarize themselves with our coding principles.

Focus on, Test-Driven Development, Clean Code and quality Code Reviews.

## Technologies

Below is an overview of the main technologies used in Catroweb.  
Your tech stack should cover **most** of them — but don’t worry if something is new. You can learn along the way (The web is your friend 😉).

### Languages

[![HTML](http://img.shields.io/badge/HTML-darkblue)](https://www.w3schools.com/html/)
[![Sass](https://img.shields.io/badge/Sass-darkblue)](https://sass-lang.com/)
[![PHP](https://img.shields.io/badge/PHP-with_Symfony-green?labelColor=darkblue)](https://www.php.net/)
[![JavaScript](https://img.shields.io/badge/JavaScript-darkblue)](https://www.w3schools.com/js/)
[![SQL](https://img.shields.io/badge/SQL-darkblue)](https://www.w3schools.com/sql/)

### Tooling & Infrastructure (IDE, Version Control, Issue Tracking, ...)

[![PhpStorm](https://img.shields.io/badge/PhpStorm-recommended-green?labelColor=purple)](https://www.jetbrains.com/phpstorm/)
[![Symfony](https://img.shields.io/badge/Symfony-purple)](https://symfony.com/)
[![Git](https://img.shields.io/badge/Git-purple)](https://git-scm.com/)
[![GitHub Issues](https://img.shields.io/badge/GitHub-Issues-black)](https://github.com/Catrobat/Catroweb/issues)
[![Docker](https://img.shields.io/badge/Docker-purple)](https://www.docker.com/)
[![API Documentation](https://img.shields.io/badge/API-blue)](https://developer.catrobat.org/Catroweb/)

### Knowledge Repository

[![Confluence](https://img.shields.io/badge/Confluence-orange)](https://confluence.catrob.at/)

### Communication

[![Slack](https://img.shields.io/badge/Slack-green)](https://slack.com/)

## Start Contribution

If you feel confident that your tech stack matches our requirements, we invite you to start contributing 🎉

👉 https://github.com/Catrobat/Catroweb/blob/develop/.github/onboarding.md

## Issues

Found a bug?

1. Please check the existing issues first to avoid duplicates:  
   👉 https://github.com/Catrobat/Catroweb/issues
2. If it hasn’t been reported yet, open a new issue using the bug report template:  
   👉 https://github.com/Catrobat/Catroweb/issues/new?labels=bug&template=bug_report.md
## Running PHP Commands in Docker

If you are using Docker, do NOT run `php` commands on host machine.

Instead run:

docker compose exec app php bin/console <command>

Example:
docker compose exec app php bin/console doctrine:migrations:migrate
