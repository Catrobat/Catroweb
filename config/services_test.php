<?php

declare(strict_types=1);

use App\Project\Apk\JenkinsDispatcher;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Security\TokenGenerator;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\System\Testing\FakeJenkinsDispatcher;
use App\System\Testing\FakeTranslationDelegate;
use App\System\Testing\ProxyTokenGenerator;
use App\Translation\TranslationDelegate;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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

  $services->load('App\System\Testing\Behat\Context\\', __DIR__.'/../src/System/Testing/Behat/Context/*');

  $services->set('token_generator.inner', TokenGenerator::class);

  $services->set(TokenGenerator::class, ProxyTokenGenerator::class)
    ->autowire(false)
    ->args([service('token_generator.inner')])
  ;

  $services->set(JenkinsDispatcher::class, FakeJenkinsDispatcher::class)
    ->args(['%jenkins%'])
  ;

  $services->set(CatrobatFileExtractor::class, CatrobatFileExtractor::class)
    ->args(['%catrobat.file.extract.dir%', '%catrobat.file.extract.path%'])
    ->public()
  ;

  $services->set(ProjectDataFixtures::class, ProjectDataFixtures::class)
    ->public()
  ;

  $services->set(UserDataFixtures::class, UserDataFixtures::class)
    ->public()
  ;

  $services->set(TranslationDelegate::class, FakeTranslationDelegate::class);
};
