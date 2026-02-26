> Docker is the recommended setup for local Catroweb development and matches CI behavior.

### Content
- [Install Docker](#install-docker)
- [Introduction to Catroweb with docker](#introduction-to-catroweb-with-docker)
- [Running Catroweb dev](#running-catroweb-dev-in-docker)
- [Testing](#testing)
- [Running Catroweb test](#running-a-reduced-container-containing-only-testing-tools-used-by-ci)
- [Docker commands](#docker-commands)
- [Docker in phpStorm](#docker-in-phpstorm)


# Installation

## Install docker

**Note:** Keep in mind that **you need enough disk space**.
We tested it on a Ubuntu 18 virtual machine with 30GB of space.

### LINUX 
* **Install the Docker community engine** from the [official docker website](https://docs.docker.com/install).
Just choose your operating system in the menu on the left side and follow the instructions.

* If you are using docker on Linux take a look at the [**post install instructions**](https://docs.docker.com/install/linux/linux-postinstall).

### WINDOWS 10/11 PRO (Might not work properly, we recommend Linux)

* Enable HYPER-V in the windows features and reboot
* Get and install Docker from [Docker Hub](https://hub.docker.com/editions/community/docker-ce-desktop-windows/)
  Make sure Linux containers are used and then reboot
* Enable "Expose daemon on tcp://localhost/2375 without tls" in the docker settings if you plan to build the container in PhpStorm.
* :exclamation: Info: make sure to use gitbash or something similar to run shell scripts. You can configure it as your default CLI in the phpstorm settings too. Have a look at this [link](https://www.jetbrains.com/help/phpstorm/configuring-line-endings-and-line-separators.html#:~:text=From%20the%20main%20menu%2C%20select%20File%20%7C%20File%20Properties%20%7C%20Line,ending%20style%20from%20the%20list). :fire:


## Install Git (if you haven't already)

### Linux 

* `sudo apt-get install git`

### Windows

* Install git from [git-scm](https://git-scm.com/download/win). 

**Important:** Make sure to checkout **Unix style line endings** and also commit with Unix style line endings. (Else our docker container will not build/run). Gitscm asks you in the installation process. Else you need to modify git manually before checking out the repository. (``)


## Checkout the Catroweb Project

You want to use your own fork of `https://github.com/Catrobat/Catroweb.git`. Add as many remotes as you need. For a how-to git checkout another tutorial.

  ```
  git clone <your-forked-repo>
  cd Catroweb
  git remote add catroweb https://github.com/Catrobat/Catroweb.git
  git checkout develop
  git pull catroweb develop
  ``` 

* Optionally: you could just add the project directly via PHPStorm - "get project from version control"


## Introduction to Catroweb with Docker
In this section we discuss the general usage of Docker and what Docker does.
\
Docker is a tool that makes it easy to run complex applications (like Catroweb) on different operating systems.
This is accomplished by bundling the application into a container. Every container can be imagined as a virtual machine.
We specifically use 'docker compose' which automatically bundles different services each into one container.

#### Catroweb needs multiple services for development:
- The application container is `app.catroweb`, which runs the Catroweb codebase.
  This container has **shared folders** with the host (so folders where changes are synchronized with the container):
    - src
    - tests (because this is shared you can view the testreport screens on the host)
    - translations
    - templates
  If you change these files on the host or container you must **NOT REBUILD** the container.
  Rebuild the container only when Docker image inputs change (for example Dockerfile/build dependencies).
- One MariaDB which runs with the name db.catroweb.dev
- And a phpMyAdmin container with the name phpmyadmin.catroweb.dev

#### Order

The services are started in a specific order. First, it deploys *MariaDB* then it starts *phpMyAdmin* and finally the *app*.
The App service waits for MariaDB to successfully create the database before migrating via doctrine.
When the migration finishes successfully, the apache2 server in the app container is started and listens to the 8080 port on the host. 

At the end of this guide are some **helpful commands for docker**: [Docker commands](#docker-commands)

And here are some **helpful links** if you want to dive deeper into docker or just google it:
- <https://docs.docker.com>
- <https://acadgild.com/blog/what-is-docker-container-an-introduction>


## Running Catroweb Dev in Docker

```bash
cd docker
docker compose -f docker-compose.dev.yaml build
docker compose -f docker-compose.dev.yaml up -d
```

This will start up the following containers:
- #### Apache, PHP with the catroweb source on Port 8080

    - dev env: http://localhost:8080/
    - test env: http://localhost:8080/index_test.php/
 
- #### MariaDB

   We have 2 databases - catroweb_dev + catroweb_test

- #### Chrome
   Used in behat tests. No need to manually start chrome headless anymore. Yay.

- #### phpMyAdmin
    <http://localhost:8081> to open to phpMyAdmin

    credentials:
    - Server: db.catroweb.dev
    - Username: root
    - Password: root

    credentials for test db:
    - Server: db.catroweb.test
    - Username: root
    - Password: root

* ### Developing and testing should work similarly to a native project. Just run the commands via docker:

    - Just run the commands via docker: 
    ```bash
    docker exec -it app.catroweb php bin/console catrobat:reset --hard
    ```
    
    - You can open the catroweb website in your browser with http://localhost:8080

    - To use all IntelliSense features, like code completion, etc. we need to have access to the libraries (vendor + node_modules). Since those directories are used during the build process we can't use a shared volume.

       -- Option A: copy the libraries from the container to the host. Run from the project root:
         ```
            sh docker/app/import-container-libraries.sh
         ```
         Be patient. This command might take a minute or two ;)

       -- Option B: run `composer install` and `yarn install` on the host (if you have the toolchain locally)

#### Notes:

- If you have a timeout while executing the reset or if the execution is very slow in general,
you can deactivate xdebug in the container - just execute:
    ```
    docker exec -it app.catroweb rm /etc/php/7.3/cli/conf.d/20-xdebug.ini
    ```


## Testing

* #### PHPUnit:
    ```bash
    docker exec -it app.catroweb bin/phpunit tests
    ```

* #### Behat:
    ```bash
    docker exec -it app.catroweb bin/behat
    ```


## Running a reduced container containing only testing tools. (Used by CI)

```bash
cd docker
docker compose -f docker-compose.test.yaml build
docker compose -f docker-compose.test.yaml up -d
```


## Docker commands

- see all the running container
  ```bash
  docker ps -a
  ```
  Here you can also look up the container id
- stop one container
  ```bash
  docker stop CONTAINER_ID
  ```
- stop all running container
  ```bash
  docker stop $(docker ps -q)
  ```
- remove all stopped containers
  ```bash
  docker container prune
  ```
- delete all docker resources that are not used currently
  ```bash
  docker system prune
  ```
- execute command inside container
  ```bash
  docker exec -it CONTAINER_ID COMMAND
  ```
  for example, open a bash console inside a container
  ```bash
  docker exec -it CONTAINER_ID bash
  ```
- copy something from the container to the host.
  ```bash
  docker cp CONTAINER_ID:PATH_IN_CONTAINER PATH_ON_HOST
  ```
  E.g. copy screens. (screens dir must exist!)
  ```bash
  docker cp app.catroweb/var/www/catroweb/tests/testreports/screens ~/screens
  ```


## Docker compose commands
 - Show logs
   ```bash
   docker compose -f docker-compose.dev.yaml logs
   ```


## Docker in PhpStorm

* In PhpStorm open the edit Run/Debug configuration window (Menu -> Run -> Edit Configurations...)
* Press on the + symbol then on Docker -> Docker-compose
* Add the docker/docker-compose.dev.yaml file
* optional: use --force-build option
* Note for Windows users: if the server can't be autodetected check your Dockerconfig. You must "Expose daemon on tcp://localhost/2375 without tls"

With this configuration, you can build and run the containers directly from PhpStorm.
You can then use the docker menu on the bottom of PhpStorm to look at the logs or execute commands.
