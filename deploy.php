<?php

declare(strict_types=1);

namespace Deployer;

require 'recipe/symfony.php';
require 'contrib/slack.php';

// ---------------------------------------------------
// Configuration via environment variables
// ---------------------------------------------------

set('default_timeout', 6000);

// Project name and repository
set('application', getenv('APP_NAME') ?: 'Catroweb');
set('repository', getenv('DEPLOY_GIT') ?: 'https://github.com/Catrobat/Catroweb.git');
set('git_tty', false);

// Slack configuration
set('slack_webhook', getenv('SLACK_WEBHOOK') ?? '');
set('slack_text', 'Web-Team deploying `{{branch}}` to *{{target}}*');
set('slack_success_text', 'Deploy to *{{target}}* successful');
set('slack_success_color', '#4BB543');

// Symfony environment
set('symfony_env', 'prod');
set('writable_recursive', true);

// Shared directories
set('shared_dirs', [
  'var/log',
  'var/sessions',
  'public/resources',
  '.jwt',
]);

// Shared files between releases
add('shared_files', [
  '.env.prod.local',      // keep only production .env
  'google_cloud_key.json',
  '.dkim/private.key',
]);

// Writable directories
set('writable_dirs', [
  'var/cache',
  'var/log',
  'var/sessions',
  'public/resources',
]);

// Symfony directories
set('bin_dir', 'bin');
set('var_dir', 'var');
set('web_dir', 'public');
set('public_dir', 'public');

set('allow_anonymous_stats', false);

// Hosts
$deployShare = getenv('DEPLOY_HOST') ?: '127.0.0.1';
$deployUser = getenv('DEPLOY_USER') ?: 'deploy';
$deployBranch = getenv('DEPLOY_BRANCH') ?: 'main';
host($deployShare)
  ->set('labels', ['stage' => 'share'])
  ->set('symfony_env', 'prod')
  ->set('branch', $deployBranch)
  ->set('deploy_path', '/var/www/share')
  ->set('remote_user', $deployUser)
;

// ---------------------------------------------------
// Tasks
// ---------------------------------------------------

// Manually define this task because deployer uses the old symfony structure with web instead of
// public. Change this when deployer gets updated.
task('install:assets', function () {
  run('{{bin/console}} assets:install --symlink --relative public');
});

// For such sudo commands to work, the server must allow those commands without a password
// change the sudoers file if needed!
task('restart:nginx', function () {
  run('sudo /usr/sbin/service nginx restart');
});

task('restart:php-fpm', function () {
  run('sudo /usr/sbin/service php8.4-fpm restart');
});

task('install:npm', function () {
  cd('{{release_path}}');
  run('npm install');
});

task('deploy:encore', function () {
  cd('{{release_path}}');
  run('npm run prod');
});

task('deploy:jwt', function () {
  cd('{{release_path}}');
  run('sh docker/app/init-jwt-config.sh');
});

task('update:achievements', function () {
  cd('{{release_path}}');
  run('bin/console catrobat:update:achievements');
});

task('update:tags', function () {
  cd('{{release_path}}');
  run('bin/console catrobat:update:tags');
});

task('update:extensions', function () {
  cd('{{release_path}}');
  run('bin/console catrobat:update:extensions');
});

task('update:flavors', function () {
  cd('{{release_path}}');
  run('bin/console catrobat:update:flavors');
});

task('update:special', function () {
  cd('{{release_path}}');
  run('bin/console catrobat:update:special');
});

task('sonata:admin:setup:acl', function () {
  cd('{{release_path}}');
  run('bin/console sonata:admin:setup-acl');
});

// dump the .env file as .env.local.php to speed up the loading of the env vars
task('dump:env', function () {
  cd('{{release_path}}');
  run('bin/console dotenv:dump prod');
});

// ---------------------------------------------------
// Main deployment task
// ---------------------------------------------------

desc('Start the deployment process');
task('deploy', [
  'deploy:prepare',
  'deploy:clear_paths',
  'deploy:vendors',
  'install:assets',
  'dump:env',
  'deploy:cache:clear',
  'deploy:symlink',
  'database:migrate',
  'install:npm',
  'deploy:encore',
  'deploy:jwt',
  'restart:nginx',
  'restart:php-fpm',
  'sonata:admin:setup:acl',
  'update:flavors',
  'update:achievements',
  'update:tags',
  'update:extensions',
  'update:special',
  'deploy:cache:clear',
  'deploy:unlock',
  'slack:notify:success',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
// should maybe not be done automatically. we can do that no problem but that is not that nice.
// before('deploy:symlink', 'database:migrate');

before('deploy:prepare', 'slack:notify');
after('deploy:failed', 'slack:notify:failure');
