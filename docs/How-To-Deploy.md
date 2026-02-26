# Deployer
We are using deployer to deploy to our development- and production-Server. 

- You may need to modify the file deploy.php. Ask our coordinator if specific settings are unclear.
- Make sure you are in the correct network. (vpn)

### Slack Webhook
We also have a slack webhook for our deployment system. For more information ask our coordinator.

### Credentials
If you need the credentials, ask your coordinator.

### To deploy to the server, 

#### 1) Update your env.local file to with the correct values for:
  ```
  #DEPLOY_GIT=https://github.com/Catrobat/Catroweb-Symfony.git
  #SLACK_WEBHOOK=
  #DEPLOY_SHARE=
  #DEPLOY_WEBTEST=
  #DEPLOY_WEBTEST_BRANCH=
  #DEPLOY_POREVIEW=
  #DEPLOY_POREVIEW_BRANCH=
  #DEPLOY_CATBLOCKS=
  #DEPLOY_CATBLOCKS_BRANCH=
  ```
  depending on which server you need to deploy to.

  E.g for web-test without credentials:
  ```
  DEPLOY_GIT=https://github.com/Catrobat/Catroweb-Symfony.git
  SLACK_WEBHOOK=https://hooks.slack.com/services/_KEY_
  DEPLOY_WEBTEST=_USERNAME_@_HOST_
  DEPLOY_WEBTEST_BRANCH=develop
  ```

#### 2) go into the root directory and run

  `bin/dep deploy <stage_name>`

  E.g
  ```
  bin/dep deploy share
  bin/dep deploy web-test
  bin/dep deploy android
  bin/dep deploy catblocks
  ```

#### 3) Enter the correct password 
  Everything else should happen magically.

#### 4) In case of errors
You might need to check the server configs (Take a look into "How to set up a server")

