<?php

namespace Deployer;

require 'recipe/symfony.php';


// Project name
set('application', 'PocketCode Share');
set('repository', 'https://github.com/Catrobat/Catroweb-Symfony.git');
set('git_tty', false);


// Symfony build set
set('symfony_env', 'prod');

// Symfony shared dirs
set('shared_dirs',
  [
    'var/logs',
    'var/sessions',
    'web/resources',
    'backups'
  ]);

// Shared files between deploys
add('shared_files',
  [
    'app/config/parameters.yml',
    'app/config/parameters_dev.yml', // only dev!
  ]);

// Symfony writable dirs
set('writable_dirs',
  [
    'var/cache',
    'var/logs',
    'var/sessions',
    'backups',
  ]);

// Symfony executable and variable directories
set('bin_dir', 'bin');
set('var_dir', 'var');

set('allow_anonymous_stats', false);

// Hosts
host('unpriv@cat-share-exp.ist.tugraz.at')
  ->stage('exp')
  ->set('symfony_env', 'prod')
  ->set('branch', 'php7Iwillsaveyou')
  ->set('composer_options','install --verbose --prefer-dist --optimize-autoloader')
  ->set('deploy_path', '/var/www/share/');

// Tasks
task('build', function() {
  run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
// should maybe not be done automatically. we can do that no problem but that is not that nice.

//before('deploy:symlink', 'database:migrate');

//after('deploy:unlock', 'nginx:reload');

