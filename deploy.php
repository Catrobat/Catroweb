<?php

namespace Deployer;

use Symfony\Component\Dotenv\Dotenv;

require 'recipe/symfony3.php';
require 'recipe/slack.php';

// Load .env file
(new Dotenv(true))->load('.env');
(new Dotenv(true))->load('.env.local');

// Project name
set('application', getenv('APP_NAME'));
set('repository', getenv('DEPLOY_GIT'));
set('git_tty', false);

// Slack Data
set('slack_webhook', getenv(('SLACK_WEBHOOK')));
set('slack_text', 'Web-Team deploying `{{branch}}` to *{{target}}*');
set('slack_success_text', 'Deploy to *{{target}}* successful');
set('slack_success_color', '#4BB543');

// Symfony build set
set('symfony_env', 'prod');

// Symfony shared dirs
set('shared_dirs',
  [
    'var/log',
    'var/sessions',
    'public/resources'
  ]);

// Shared files between deploys
add('shared_files',
  [
    '.env.local',
    '.env.prod.local',
    '.env.dev.local'
  ]);

// Symfony writable dirs
set('writable_dirs',
  [
    'var/cache',
    'var/log',
    'var/sessions'
  ]);


// Symfony executable and variable directories
set('bin_dir', 'bin');
set('var_dir', 'var');
set('web_dir', 'public');
set('public_dir', 'public');

set('allow_anonymous_stats', false);

// Hosts
host(getenv('DEPLOY_SHARE'))
  ->stage('share')
  ->set('symfony_env', 'prod')
  ->set('branch', 'release/v3.4.3')
  ->set('composer_options','install --verbose --prefer-dist --optimize-autoloader')
  ->set('deploy_path', '/var/www/share/');


host(getenv('DEPLOY_WEBTEST'))
  ->stage('web-test')
  ->set('symfony_env', 'dev')
  ->set('branch', getenv('DEPLOY_WEBTEST_BRANCH'))
  ->set('composer_options','install --verbose --prefer-dist --optimize-autoloader')
  ->set('deploy_path', '/var/www/share/');

host(getenv('DEPLOY_POREVIEW'))
  ->stage('po-review')
  ->set('symfony_env', 'dev')
  ->set('branch', getenv('DEPLOY_POREVIEW_BRANCH'))
  ->set('composer_options','install --verbose --prefer-dist --optimize-autoloader')
  ->set('deploy_path', '/var/www/share/');

host(getenv('DEPLOY_CATBLOCKS'))
  ->stage('catblocks')
  ->set('symfony_env', 'dev')
  ->set('branch', getenv('DEPLOY_CATBLOCKS_BRANCH'))
  ->set('composer_options','install --verbose --prefer-dist --optimize-autoloader')
  ->set('deploy_path', '/var/www/share/');

host(getenv('DEPLOY_ANDROID'))
  ->stage('android')
  ->set('symfony_env', 'prod')
  ->set('branch', getenv('DEPLOY_ANDROID_BRANCH'))
  ->set('composer_options','install --verbose --prefer-dist --optimize-autoloader --no-dev')
  ->set('deploy_path', '/var/www/share/');



// Tasks

// Manually define this task because deployer uses the old symfony structure with web instead of
// public. Change this when deployer gets updated.
task('install:assets', function () {
  run('{{bin/php}} {{bin/console}} assets:install --symlink --relative public');
});

// For such sudo commands to work, the server must allow those commands without a password
// change the sudoers file if needed!
task('restart:nginx', function () {
  run('sudo /usr/sbin/service nginx restart');
});
task('restart:php-fpm', function () {
  run('sudo /usr/sbin/service php7.4-fpm restart');
});
task('install:npm', function () {
  cd('{{release_path}}');
  run('npm install');
});
task('deploy:grunt', function () {
  cd('{{release_path}}');
  run('grunt');
});
task('deploy:encore', function () {
  cd('{{release_path}}');
  run('npm run encore dev');
});

task('deploy:jwt', function () {
  cd('{{release_path}}');
  run('sh docker/app/init-jwt-config.sh');
});

/**
 * Main task
 */
task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  'deploy:clear_paths',
  'deploy:create_cache_dir',
  'deploy:shared',
  'deploy:assets',
  'deploy:vendors',
  'deploy:assetic:dump',
  'install:assets',
  'deploy:cache:clear',
  'deploy:cache:warmup',
  'deploy:writable',
  'deploy:symlink',
  'database:migrate',
  'install:npm',
  'deploy:grunt',
  'deploy:encore',
  'deploy:jwt',
  'restart:nginx',
  'restart:php-fpm',
  'deploy:unlock',
  'slack:notify:success',
  'cleanup',
])->desc('Deploy Catroweb!');


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
// should maybe not be done automatically. we can do that no problem but that is not that nice.
//before('deploy:symlink', 'database:migrate');

before('deploy:prepare', 'slack:notify');
after('deploy:failed', 'slack:notify:failure');
