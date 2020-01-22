# Docker

> For now catroweb with docker is only for **local development**.

### Content
- [Install Docker](#install-docker)
- [Running Catroweb](#running-catroweb-in-docker)
- [Testing](#testing)
- [Docker commands](#docker-commands)
- [Docker in phpStorm](#docker-in-phpstorm)


## Install docker

To install docker engine - community on your machine visit the official docker website:
<https://docs.docker.com/install/>
Then just choose your operating system in the menu on the left side and follow the instructions.

## Running Catroweb in Docker
Please quit all processes that are running on port 80, on apple devices there is the standard apache server you can deactivate it by 
```bash
sudo apachectl stop 
```

```bash
cd docker
docker-compose -f docker-compose.dev.yml build
docker-compose -f docker-compose.dev.yml up -d
```

This will start up the following containers:
- #### Apache, PHP with the catroweb source
    on Port 80
    
- #### MariaDB
- #### phpMyAdmin
    <http://localhost:8001> to open to phpMyAdmin
    
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
    Two terminal windows:
    ```bash
    // First Window
    docker exec -it app.catroweb.dev bin/console cache:clear -e test
    docker exec -it app.catroweb.dev google-chrome-stable --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222  --no-sandbox
    // Second Window
    docker exec -it app.catroweb.dev bin/behat
    ```

* #### manual:
    For manual testing add a line to your host file: \
    `` localhost catroweb `` \
    and run these commands in the app.catroweb.dev container: \
    ```bash
    docker exec -it app.catroweb.dev php bin/console catrobat:reset --hard
    ```
    Then you can open catroweb in your browser with http://catroweb
    
    If you have a timeout while executing the reset or if the execution is very slow in general, 
    you can deactivate xdebug in the container just execute:
    ```
    docker exec -it app.catroweb.dev rm /etc/php/7.3/cli/conf.d/20-xdebug.ini
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
