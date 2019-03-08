﻿
# Important

***Since we are in the middle of the transition to Symfony 3.4 and php7.2 the only branch working out out box is ```php7IWillSaveYou```.  So for now use this branch instead of dev-master to rebase your tickets.***

# Introduction
**Welcome to the WebTeam** with the motto "**part of the crew, part of the ship**".

- Please read these things carefully and ask if something is not clear.
  - If you are not using our VM please look in the instructions for setting up our project.
  - Else just follow the Instructions for the Virtual Machine.
- Next you should read about our Workflow
- Further Information can be found in the section:  Tutorials & Commands

Sincerely the WebTeam.
###    Who do I contact if I... 
 - have questions about the project / need help:
   -  Google
   -  @everyone
   ---
-  want to have pushed a ticket from Issue Pool to XYZ:
  - @Coordinator 
  ---
- need a new ticket:
  -  [Jira](https://jira.catrob.at/secure/RapidBoard.jspa?rapidView=26)
  -  @Coordinator
  -  @Senior
  ---
- have questions to a Codereview:
  -  @Coordinator
  -  @Senior (best would be the guy who reviewed the code)
  ---
-  have a deactivated timesheet and want to have it reactivated:
  -  E-Mail an Mathias Müller oder Christian Schindler

# Setup Symfony Framework
## Virtual Machine 
If you like to work with an Virtual Machine, there is already one set up. 
You can get it [here.](https://1drv.ms/u/s!AuZE9YACHBqevUGpfxfLhGV0ta2r "https://1drv.ms/u/s!AuZE9YACHBqevUGpfxfLhGV0ta2r")

#### First things  to do in the VM

1. Setup git name and email:
  ```
  git config --global user.name "user_name"
  git config --global user.email "email_id"
  ```
2. Update the vm: 
  ```
  sudo apt-get update
  sudo apt-get upgrade
  ```
3. Go to the project folder and update the project:
  ```
  cd ~/Catroweb-Symfony/
  git pull
  php composer.phar install
  php bin/console catrobat:reset --hard
  grunt
  ```
4. Register phpStorm. Students get a free license.
More information at: https://www.jetbrains.com/student/

That's it. You can start testing.


####  Login credentials


- VM:

    username: catroweb
    password: cw

- Mysql/PhpMyAdmin:

    username: root
    password: root

## Setup Guide Ubuntu 18

1. Download and install Ubuntu from [here.](http://www.ubuntu.com/)

2. You need to install following: 
  ```
  sudo apt-get install php php7.2-ldap php7.2-cli php7.2-curl php7.2-sqlite3 php7.2-intl php-apcu mysql-server apache2 php-imagick php-mbstring php-gettext git curl npm grunt sass phpmyadmin
  ```

3. Configure and set up Mysql and Phpmyadmin: 

  Here is a [Tutorial for Ubunutu  18](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-on-ubuntu-18-04), or if you only want a short summary:
  
- phpmyadmin settings:  
   -  choose apache2  (**check it with 'space / leerzeichen'** (if there is no * it is  **NOT** checked!,))  
   - choose dbconfig yes
  ```
  sudo phpenmod mbstring
  sudo systemctl restart apache2
  sudo mysql
  SELECT user,authentication_string,plugin,host FROM mysql.user;
  ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
  FLUSH PRIVILEGES;
  ```
  Now you should be able to login to phpMyAdmin with **username**: root **passsword**: 'password'
  Replace 'password' with whatever you want.

4. Setup git name and email:
  ```
  git config --global user.name "user_name"
  git config --global user.email "email_id"
  ```
5. Setup Catroweb:
  ```
  git clone https://github.com/Catrobat/Catroweb-Symfony.git
  cd Catroweb-Symfony
  git checkout php7IWillSaveYou
  curl -sS https://getcomposer.org/installer | php
  ``` 
  Login to http://localhost/phpmyadmin create a database called "symfony"
  Create your local parameters.yml files based on the dist versions
  ```
  cp config/packages/parameters.yml.dist config/packages/parameters.yml
  cp config/packages/test/parameters.yml.dist config/packages/test/parameters.yml
  cp config/packages/dev/parameters.yml.dist config/packages/dev/parameters.yml
  ```
  Update your local parameters.yml files (in config/packages, config/packages/dev/) with the one you can find [here.](https://confluence.catrob.at/display/CATWEB/Parameters)
  !! Use your database password !!! when updating the parameters.yml file
  ```
  php composer.phar install
  npm install
  php bin/console catrobat:reset --hard
  grunt
  ```

6. Setup Apache:
  ```
  sudo ln -s <absolute path to the symfony public directory> /var/www/catroweb
  sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/catroweb.conf
  ```
   Edit the catroweb.conf file to look like this:
   
  >ServerName catroweb
  >ServerAdmin webmaster@localhost
  >DocumentRoot /var/www/catroweb
  ><Directory /var/www/catroweb>
  >DirectoryIndex /index.php
  >FallbackResource /index.php
  > &lt;/Directory> 
  ```
  sudo gedit /etc/apache2/sites-available/catroweb.conf
  ```
  Add at the top of the hosts file:
  > 127.0.0.1                                  catroweb
  ```
  sudo gedit /etc/hosts
  ```
  ```
  sudo a2dissite 000-default.conf
  sudo a2ensite catroweb.conf
  sudo service apache2 restart
  ```

7.  We test with Headless Chrome, so install Google Chrome from [here.](https://www.google.com/intl/de_ALL/chrome/)

8. Set permissions go to the root of the project and execute the following commands: 
  ```
  rm -rf var/cache/*
  rm -rf var/log/*
  HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
  sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
  sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
  chmod o+w public/resources/ -R
  chmod o+w+x tests/behat/sqlite/ -R
  ```


Setup should be done! Lets test it.

## Customize Ubuntu

If you don't like the design of Ubuntu you can easily change its appearance with some tweaks. 
For example, you can **style it like windows 10**.

https://www.howtogeek.com/37.23819/how-to-make-ubuntu-look-more-like-windows/

or just **get rid of Ubuntu Unity**?

If you don't like Ubuntu Unity, here is a tutorial on how to get rid of it: 
https://wiki.ubuntuusers.de/Gnome_Flashback

#### Improve the power of your terminal
If you would like to have a command line interface like the one we use in our VM that always displays the current git branch you are working on, update your ```~/.bashrc``` file with this [one](https://gist.github.com/HCrane/5a98753adb5939d21c960f944d095d07).

### Size of the VM
We deliberately kept the size of the VM as small as possible so sharing it over the inernet would not take ages. 
If you do have to extend the size of the VM there are a few steps you can take. IN your host system you probably have VirtualBox which brings the VBoxManage.exe with it.

  VBoxManage clonehd "source.vmdk" "cloned.vdi" --format vdi
    VBoxManage modifyhd "cloned.vdi" --resize 51200
    VBoxManage clonehd "cloned.vdi" "resized.vmdk" --format vmdk
  
For the first 2 Commands also exists a GUI Tool that can be found [here](https://forums.virtualbox.org/viewtopic.php?t=22422). If you did these steps and encounter no errors you can mount the newly created .vmdk file instead of the old one.
You just have to start the machine (which should be no problem otherwise you did something wrong) and install and run gparted.

    sudo apt install gparted
    gparted

In the GUI that opens you should see that there is some unallocated space. Just move the slider out so 100% is covered and apply the operation. Congrats if nothing crashed you have a risezide VM ;)

# Workflow

## Testing

***Important***: Before starting your tests clear the cache for the test enviornment! 
```
bin/console cache:clear --env=test
```
---

For our project we use three **different Test suites**. PhpSpec, PhpUnit and Behat ([Tutorial](http://behat.org/en/latest/guides.html)). ---
 - **PhpUnit**
  - Unit tests. Files are in "tests/phpUnit/..."
  - ```bin/phpunit tests```
 - **PhpSpec**
  - Specification tests. Files are in "tests/PhpSpec/specs/Catrobat/..."
  - ```bin/phpspec run```
 - **Behat**
  - Tests for the API. Files are in "tests/behat/features/..."
  -   Failed Tests:  
    -> **Firefox screenshot**  for each failed  **Scenario**  in "tests/testreports/screens/..."  
    -> **errors.json**  file for  **response errors**  in "tests/testreports/behat/"
  - For Behat Headless Chrome must run in the background! 
  - ```google-chrome-stable --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 bin/behat ```
---
#### Tests that fail right now:
| PhpUnit | PhpSpec | Behat |
| :-----: | :-----: | :---: |
| 0       | 3       |      1|
Note: The first behat test using chrome headless always fails after clearing the cache.
Just stop the test execution and restart the tests.

---
#### Test your Code Sytle

After successfully testing your code please apply the Style check tests.  

- **PHP:**
The PHP coding standard can be tested with **PHPCheckStyle**:
  ```
  bin/console phpcheckstyle
  ```
  (please adjust it to your own installation!) 
  The HTML report can be found in /Catroweb-Symfony/style-report/

---
#### Additional Configurations:
In the project you can find the two files phpspec.yml.dist and behat.yml.dist . Copy this files and rename it to phpspec.yml and behat.yml. This two files are local and only available for you, so you can modify them as much as you want. To use standard setting just leave this files untouched. 

---
####  FAQ

- At your first Behat run you might get fatal errors. Just reset sqlite permissions
  ```
  chmod o+w+x tests/behat/sqlite/ -R
  ```

- If your changes in feature files seem to change nothing you can try to clear the cache
  ```
  bin/console cache:clear -e test
  ```

## Before you start working on a new Ticket

 1. Get the latest version of the project
  ```
  checkout php7Iwillsaveyou
  pull origin php7Iwillsaveyou
  composer.phar install
  bin/console catro:reset --hard
  grunt
  ```
2.  Run tests on current php7Iwillsaveyou
If everything works continue. If not, check what is not working, fix it or ask someone else if it's ok to proceed.

3.  Work on your ticket using our Git Workflow


## Git Workflow

 1. checkout newest version of dev-master
  ```
  git checkout dev-master
  git pull
  ```
 2. create new branch "WEB-XXX_name_of_ticket"
  ```
  git checkout -b "WEB-XXX_name_of_ticket"
  ```
 3. Code your work for the ticket

 4. Make sure that every commit has the correct commit message layout so it shows up on JIRA.

 5. Test everything
 
 6.  checkout newest version of dev-master
    ```
    git checkout dev-master
    git pull
    ```
 7. go to your branch and rebase with dev-master
  ```
  git checkout "WEB-XXX_name_of_ticket"
  git rebase dev-master -i"
  ```
8.  Squash your commits, there must be only 1 commit! (XX is the amount of your commits)
  ```
  git reset --soft HEAD~XX &&
  git commit
  ```
9. **Test everything!**
10.  Use GitHub and create a Pull-Request. Don't forget to select "dev-master" branch and not master branch.

## Git Commit Message Layout
must read: [How to Write a Git Commit Message, by Chirs Beams](http://chris.beams.io/posts/git-commit/)
> Line 1: WEB-XXX Fitting Title (< 50 chars)
> Line 2: empty line 
> Lines 3 - X: actual commit message; first focusing on WHY then focusing on WHAT you have changed.

 - If you manipulated the database schema (user has new field f.e.) mention it at the end of the commit message 
-  Wrap lines 3 - X at 75 characters

## Migrations
In our project we use a database migration system.

 - **never** make changes directly in the database with tools like phpMyAdmin.
 - make your changes in the "Entity\" folder (eg. FeaturedProgram.php) 
    
---
#### If you have never used migrations before and "status" says no  _Executed Migrations_  but you have  _Available Migrations_:

1.  Drop the database schema:
  ```
  php bin/console doctrine:schema:drop --force
  ```
2.  Execute the migrations:
  ```
  php bin/console doctrine:migrations:migrate
  ```
---
#### Step-by-step guide to create migrations

1. If you have a new branch and you need to work on the database (changes, updates, ...) 
you should check the database first with:
  ```
  php bin/console doctrine:migrations:status
  ```
  
2. There should be no  _New Migrations_  and the  _Executed_  and  _Available Migrations_  are in sync.  
Apply the changes you need to the corresponding entity file. After that, create the migration with:
  ```
  php bin/console doctrine:migrations:diff
  ```

3. Now with "git status" there should be a new file in  _app/DoctrineMigrations_. This file must be committed together with all you have done in the branch.  Do not forget to test, rebase and merge.  

---
#### Step-by-step guide to migrate migrations

1. If there are new migrations check it with:
  ```
  php bin/console doctrine:migrations:status
  ```
2. There should be  _New Migrations_. Add this new migrations with:
  ```
  php bin/console doctrine:migrations:migrate
  ```
3. Check the migration status again, now the database should be updated.

  ```
  php bin/console doctrine:migrations:status
  ```

## Coding Standard

#### PHP
- For Php we use this  Coding Standard ([click here](https://confluence.catrob.at/display/CATWEB/Coding+Standard)).

- This standard can be tested with **PHPCheckStyle**:
 (more info in the testing section)
  ```
  time php ~/Catroweb-Symfony/vendor/phpcheckstyle/phpcheckstyle/run.php --src ~/Catroweb-Symfony/src/ --config ~/Catroweb-Symfony/style-report/catroweb.xml
  ```

---
#### SASS / CSS
- use the **hyphens naming convention** for **classes** and **id**s 
_Example:_
  ```
  <div  class="default-wrapper"  id="program-wrapper-of-death"></div>
  ```
- **avoid names** that could be **used in libraries** 
-  **css selectors** should be more **specific** than just .base 
_Selector Example:_
  ```
  div#wrapper .container > .program
  {
    background-color: red;
  }
  ```

# Catroweb Project details

### General information

- Our [Discord]([https://discord.gg/xMc3a2n](https://discord.gg/xMc3a2n)) server
- [Slack](https://catrobat.slack.com/messages/CACR1MB0S/convo/C5YSX6YJX-1531212366.000051/)
- [Jira](https://jira.catrob.at/secure/RapidBoard.jspa?rapidView=26)
- [Confluence](https://confluence.catrob.at/)
---
- [About us](https://confluence.catrob.at/display/CATWEB)
- [Meeting notes](https://confluence.catrob.at/display/CATWEB/Meeting+notes)
---
- Catrobat Project: [https://github.com/Catrobat/](https://github.com/Catrobat/)
- Our Github Repository: [https://github.com/Catrobat/Catroweb-Symfony](https://github.com/Catrobat/Catroweb-Symfony)
---
- Servers 
  - Productive server:  [https://share.catrob.at](https://share.catrob.at/pocketcode/)
  - Test server of the WebTeam:  [https://web-test.catrob.at](https://web-test.catrob.at/)
  - Test server of the Catroid:  [https://catroid-test.catrob.at](https://catroid-test.catrob.at/)
----
- Marketing site:  [http://www.catrobat.org](http://www.catrobat.org/)
- Developer site:  [http://developer.catrobat.org/](http://developer.catrobat.org/)
---
- Test Apps
  - [test app](https://confluence.catrob.at/download/attachments/4948000/Catroid-webtest.apk?version=1&modificationDate=1439212847202&api=v2). (old version 0.9.15)  
  - Newest [test app](https://confluence.catrob.at/download/attachments/4948000/web-test.apk?version=1&modificationDate=1445953123150&api=v2). (version 0.9.18)
  - This apps can upload and download programs from the  [Webtest](https://web-test.catrob.at/).


# Tutorials & Cheat sheets

Here you can find additional Information that might be useful from time to time.

- [Symfony Framework](https://symfony.com/)
  - For a quick start: Symfony  ["Quick Tour"](https://symfony.com/doc/current/quick_tour/the_big_picture.html)
  - Good overview:  [Symfony Best Practice](http://symfony.com/doc/current/best_practices/index.html)
---
- Writing tests with  [Behat](http://behat.org/en/latest/)
  - [CheatSheet](https://confluence.catrob.at/download/attachments/4948000/2012-03-behat-cheat-sheet-en.pdf?version=1&modificationDate=1439212004789&api=v2)
---
- Writing tests with  [phpSpec](http://www.phpspec.net/en/latest)
- ---
-  Writing tests with [phpUnit](https://phpunit.de/)
---
- Working with [Composer](https://getcomposer.org/)
  - [CheatSheet](https://devhints.io/composer)
  - [Packagist](https://packagist.org/) The PHP Package Repository
----
- Working with [Bootstrap 4](https://getbootstrap.com/)
  - [CheatSheet](https://www.creative-tim.com/bootstrap-cheat-sheet)
---
- Working with [SASS](https://sass-lang.com/)
  -  [CheatSheet](https://sass-cheatsheet.brunoscopelliti.com/)
---

#### Clear cache/logs & reset permissions
```
rm -rf var/cache/* 
rm -rf var/log/* 
HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
```
---
#### Testing Cheat Sheet

Clear the test cache before starting the tests!
```
  bin/console cache:clear -e test
```

|| |
| ---- | ---- |
|google-chrome-stable --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 | Starts Headless Chrome |
|bin/phpspec run | Start all phpspec tests |
|bin/phpspec run <path> | Start a specific phpspec file |
|bin/phpunit tests | Start all phpUnit tests |
|bin/behat | Start all Behat tests |
|bin/behat -f pretty | Start all Behat Tests with detailed output |
|bin/behat -s XXX | Start a Testing Suite |
|bin/behat scr/.../Features/.../fancyTests.feature | Start all tests in a .feature file |
|bin/behat scr/.../Features/.../fancyTests.feature:63 | Start a specific test in a .feature file |
|bin/console phpcheckstyle | Generates a new php coding style check report |


####  Cheat Sheet

|| |
| ---- | ---- |
| bin/console list | List console commands|
| bin/console catrobat:reset --hard|Recreate database, clear cache |
| chmod o+w public/resources -R|Sometimes neccessary if there are problems with the privileges|
| bin/console doctrine:migrations:XXX | General command for handling migrations XXX can be: status, migrate, diff |
| bin/console fos:user:promote | With parameter --super promotes an user to an admin. For all options use parameter --help.|
| bin/console fos:user:demote | With parameter --super demotes an user to a "normal" user. For all options use parameter --help. |
| bin/console fos:user:change-password <username> <password> | Change the users password. For all options use parameter --help. |
| sudo service apache2 restart| Restart apache|
| bin/console cache:clear | Clear the cache |
| bin/console cache:clear --env=test | Clear the cache for the test environment|
| bin/console about | Useful information about the symfony environment|





