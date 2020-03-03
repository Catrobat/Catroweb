# Docker

> For now catroweb with docker is only for **local development**.

### Content
- [Install Docker](#install-docker)
- [Introduction to Catroweb with docker](#introduction-to-catroweb-with-docker)
- [Running Catroweb dev](#running-catroweb-dev-in-docker)
- [Testing](#testing)
- [Running Catroweb test](#running-catroweb-test-in-docker)
- [Docker commands](#docker-commands)
- [Docker in phpStorm](#docker-in-phpstorm)


## Install docker
To install docker engine - community on your machine visit the official docker website:
<https://docs.docker.com/install/>
Then just choose your operating system in the menu on the left side and follow the instructions.
\
If you are using docker on Linux look at this link for steps post install: <https://docs.docker.com/install/linux/linux-postinstall/>
\
Additionally if you have docker running inside a virtual machine keep in mind that you have enough disk space.
We tested it on a Ubuntu 18 virtual machine with 30GB of space.


## Introduction to Catroweb with docker
In this section we discuss the general usage of docker and what docker does.
\
Docker is a tool that makes it easy to run complex applications (like Catroweb) on different operating systems.
This is accomplished by bundling the application into container. Every container can be imagined as a virtual machine.
We specifically use docker-compose which automatically bundles different services each into one container.

#### Catroweb needs 3 services for development purposes:
- The application itself which is the container with the name app.catroweb.dev, which runs ubuntu with the whole catroweb code.
  This container has shared folders with the host (so folders where changes are synchronised with the container):
    - src
    - tests (because this is shared you can view the testreport screens on the host)
    - translations
    - templates
- One MariaDB which runs with the name db.catroweb.dev
- And a phpMyAdmin container with the name phpmyadmin.catroweb.dev

#### Order
These services are started in a specific order. First it deploys the *MariaDB* then it starts *phpMyAdmin* and finally *app*.
The App service waits for mariadb to successfully creating the database before migrating via doctrine.
When the migration finishes successfully, the apache2 server in the app container is started and listens to the 8080 port on the host.
At the end of this guide are some helpful commands for docker: [Docker commands](#docker-commands)
\
Below are some helpful links if you want to dive deeper into docker or just google it:
- <https://docs.docker.com>
- <https://acadgild.com/blog/what-is-docker-container-an-introduction>


## Running Catroweb dev in Docker
```bash
cd docker
docker-compose -f docker-compose.dev.yml build
docker-compose -f docker-compose.dev.yml up -d
```

This will start up the following containers:
- #### Apache, PHP with the catroweb source
    on Port 8080

- #### MariaDB
- #### Chrome
- #### phpMyAdmin
    <http://localhost:8081> to open to phpMyAdmin

    credentials:
    - Server: db.catroweb.dev
    - Username: root
    - Password: root

## Testing

* #### phpspec:
    ```bash
    docker exec -it app.catroweb.dev bin/phpspec run
    ```

* #### phpUnit:
    ```bash
    docker exec -it app.catroweb.dev bin/phpunit tests
    ```

* #### behat:
    ```bash
    docker exec -it app.catroweb.dev bin/console cache:clear -e test
    docker exec -it app.catroweb.dev sh docker/app/set-test-permissions.sh
    docker exec -it app.catroweb.dev bin/behat
    ```

* #### manual:
    For manual testing add a line to your host file: \
    `` localhost catroweb `` \
    and run these commands in the app.catroweb.dev container: \
    ```bash
    docker exec -it app.catroweb.dev php bin/console catrobat:reset --hard
    ```
    Then you can open catroweb in your browser with http://catroweb:8080

    If you have a timeout while executing the reset or if the execution is very slow in general,
    you can deactivate xdebug in the container just execute:
    ```
    docker exec -it app.catroweb.dev rm /etc/php/7.3/cli/conf.d/20-xdebug.ini
    ```

## Running Catroweb test in Docker
```bash
cd docker
docker-compose -f docker-compose.test.yml build
docker-compose -f docker-compose.test.yml up
```

## Docker commands

- see all the running container
```bash
docker ps -a
```
Here you can also lookup the container id
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
for example open a bash console inside a container
```bash
docker exec -it CONTAINER_ID bash
```

## Docker in phpStorm

In phpStorm open the edit Run/Debug configuration window (Menu -> Run -> Edit Configurations...)
Press on the + symbol then on Docker -> Docker-compose
Add the docker/docker-compose.dev.yml file

With the configuration you can build and run the containers directly from phpStorm.
You can then use the docker menu on the bottom of phpStorm to look at the logs, execute commands.
