<?php

declare(strict_types=1);

use App\Project\Apk\JenkinsDispatcher;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\System\Testing\FakeJenkinsDispatcher;
use App\System\Testing\FakeTranslationDelegate;
use App\Translation\TranslationDelegate;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();

  $parameters->set('catrobat.file.extract.dir', '%catrobat.pubdir%resources_test/extract/');
  $parameters->set('catrobat.file.extract.path', 'resources_test/extract/');
  $parameters->set('catrobat.file.storage.dir', '%catrobat.pubdir%resources_test/projects/');
  $parameters->set('catrobat.screenshot.dir', '%catrobat.pubdir%resources_test/screenshots/');
  $parameters->set('catrobat.screenshot.path', 'resources_test/screenshots/');
  $parameters->set('catrobat.thumbnail.dir', '%catrobat.pubdir%resources_test/thumbnails/');
  $parameters->set('catrobat.thumbnail.path', 'resources_test/thumbnails/');
  $parameters->set('catrobat.featuredimage.dir', '%catrobat.pubdir%resources_test/featured/');
  $parameters->set('catrobat.featuredimage.path', 'resources_test/featured/');
  $parameters->set('catrobat.apk.dir', '%catrobat.pubdir%resources_test/apk/');
  $parameters->set('catrobat.mediapackage.dir', '%catrobat.pubdir%resources_test/mediapackage/');
  $parameters->set('catrobat.mediapackage.path', 'resources_test/mediapackage/');
  $parameters->set('catrobat.tests.upld-dwnld-dir', 'tests/TestData/UploadDownloadTemp');
  $parameters->set('catrobat.logs.dir', '%kernel.project_dir%/tests/TestData/Cache/log/');
  $parameters->set('catrobat.testreports.behat', 'tests/TestReports/Behat/');
  $parameters->set('catrobat.testreports.screenshot', 'tests/TestReports/TestScreenshots/');

  $services = $containerConfigurator->services();

  $services->defaults()
    ->autowire()
    ->autoconfigure()
    ->public()
  ;

  // Overwrite services to ensure testability
  $services->set(JenkinsDispatcher::class, FakeJenkinsDispatcher::class)->args(['%jenkins%']);
  $services->set(TranslationDelegate::class, FakeTranslationDelegate::class);

  // Load additional test-only services:
  $services->load('App\System\Testing\Behat\Context\\', __DIR__.'/../src/System/Testing/Behat/Context/*');
  $services->set(ProjectDataFixtures::class);
  $services->set(UserDataFixtures::class);
};
