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
set('php_fpm_version', getenv('PHP_FPM_VERSION') ?: '8.5');

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
set('http_user', 'www-data');
set('writable_mode', 'acl');

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
  run('sudo /usr/sbin/service php{{php_fpm_version}}-fpm restart');
});

// Sync nsfw-scanner sources to /opt/nsfw-scanner and rebuild the container only
// when the source files actually changed (model download is multi-minute). On
// first run after switching from raw `docker run` to compose, the legacy
// container is force-removed so compose can take over the `nsfw-scanner` name.
// Requires the deploy user to be in the `docker` group on the prod host and
// to own /opt/nsfw-scanner (e.g. chown -R deploy:deploy /opt/nsfw-scanner).
task('restart:nsfw-scanner', function () {
  $dst = '/opt/nsfw-scanner';
  if (!test("[ -d {$dst} ]")) {
    info('nsfw-scanner not installed on this host, skipping');

    return;
  }

  $src = '{{release_path}}/docker/nsfw-scanner';
  $hashCmd = "find %s -type f -exec sha256sum {} + | sort | sha256sum | cut -d' ' -f1";
  $srcHash = trim(run(sprintf($hashCmd, $src)));
  $dstHash = trim(run(sprintf($hashCmd, $dst)));

  if ($srcHash === $dstHash) {
    info('nsfw-scanner sources unchanged, skipping rebuild');

    return;
  }

  info('nsfw-scanner sources changed, rebuilding');
  // -rlt (not -a): skip owner/group preservation since the deploy user is not root
  run("rsync -rlt --delete {$src}/ {$dst}/");

  $composeProject = trim(run("docker inspect nsfw-scanner --format '{{index .Config.Labels \"com.docker.compose.project\"}}' 2>/dev/null || true"));
  if ('' === $composeProject) {
    info('Migrating nsfw-scanner from raw docker-run to docker compose');
    run('docker rm -f nsfw-scanner 2>/dev/null || true');
  }

  run("cd {$dst} && docker compose -f docker-compose.prod.yaml up -d --build");
});

task('install:yarn', function () {
  cd('{{release_path}}');
  run('mkdir -p .corepack-bin && corepack enable --install-directory=.corepack-bin');
  run('export PATH={{release_path}}/.corepack-bin:$PATH && corepack prepare yarn@4.12.0 --activate && yarn install --immutable');
});

task('deploy:encore', function () {
  cd('{{release_path}}');
  run('export PATH={{release_path}}/.corepack-bin:$PATH && yarn run prod');
});

task('deploy:player', function () {
  // Built by the deployment workflow's player job; nginx caches .js for a year, so every build gets its own folder
  // and player/index.html points at the current one. A failed player build must not block a release.
  $build = __DIR__.'/player/build/dist/js/webapp';
  if (!is_file($build.'/app.js')) {
    warning('No complete browser player build found, skipping it');

    return;
  }
  // Named after all its files, so no changed file hides behind the year-long cache of its folder. WEB-INF is the
  // build tool's, not the player's.
  $hashes = [];
  foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($build, \FilesystemIterator::SKIP_DOTS)) as $file) {
    $relative = substr($file->getPathname(), strlen($build) + 1);
    if (!str_starts_with($relative, 'WEB-INF/')) {
      $hashes[$relative] = sha1_file($file->getPathname());
    }
  }
  ksort($hashes);
  $version = substr(sha1(json_encode($hashes, JSON_THROW_ON_ERROR)), 0, 12);
  run("mkdir -p {{release_path}}/public/player/{$version}"); // rsync creates only the last path segment
  upload($build.'/', "{{release_path}}/public/player/{$version}/", ['options' => ['--exclude=WEB-INF']]);
  // A meta refresh drops the #<project id>; the script keeps it.
  run('cp {{release_path}}/public/favicon.ico {{release_path}}/public/player/favicon.ico');
  run("printf '%s' '<!doctype html><script>location.replace(\"{$version}/index.html\" + location.hash)</script><meta http-equiv=\"refresh\" content=\"0; url={$version}/index.html\">' > {{release_path}}/public/player/index.html");
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

// dump the .env file as .env.local.php to speed up the loading of the env vars
task('dump:env', function () {
  cd('{{release_path}}');
  run('bin/console dotenv:dump prod');
});

// Smoke test: verify the health endpoint after deployment
task('smoke_test', function () {
  $maxRetries = 5;
  $retryDelay = 5;
  for ($i = 1; $i <= $maxRetries; ++$i) {
    $result = run('curl -sf -o /dev/null -w "%{http_code}" -H "Host: share.catrobat.org" https://localhost/api/health --max-time 10 -k || echo "000"');
    if ('200' === trim($result)) {
      info("Health check passed (attempt {$i})");

      return;
    }
    warning("Health check attempt {$i}/{$maxRetries} returned HTTP {$result}");
    if ($i === $maxRetries) {
      $body = run('curl -s -H "Host: share.catrobat.org" https://localhost/api/health --max-time 10 -k || echo "N/A"');
      warning("Final health check failed. Response body: {$body}");
    }
    if ($i < $maxRetries) {
      sleep($retryDelay);
    }
  }

  throw new \RuntimeException("Health check failed after {$maxRetries} attempts (Final HTTP Code: {$result})");
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
  'install:yarn',
  'deploy:encore',
  'deploy:player',
  'dump:env',
  'deploy:jwt',
  'deploy:cache:clear',
  'database:migrate',
  'deploy:symlink',
  'restart:nginx',
  'restart:php-fpm',
  'restart:nsfw-scanner',
  'update:flavors',
  'update:achievements',
  'update:tags',
  'update:extensions',
  'update:special',
  'smoke_test',
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
