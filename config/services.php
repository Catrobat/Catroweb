<?php

declare(strict_types=1);

use App\Admin\ApkGeneration\ApkController;
use App\Admin\ApkGeneration\ApkPendingAdmin;
use App\Admin\ApkGeneration\ApkReadyAdmin;
use App\Admin\Comments\CommentsAdmin;
use App\Admin\MediaLibrary\MediaAssetAdmin;
use App\Admin\MediaLibrary\MediaCategoryAdmin;
use App\Admin\Moderation\AppealQueueAdmin;
use App\Admin\Moderation\AppealQueueController;
use App\Admin\Moderation\ModerationQueueAdmin;
use App\Admin\Moderation\ModerationQueueController;
use App\Admin\Projects\ApproveProjects\ApproveProjectsAdmin;
use App\Admin\Projects\ApproveProjects\ApproveProjectsController;
use App\Admin\Projects\BrokenProjects\BrokenProjectsAdmin;
use App\Admin\Projects\ProjectsAdmin;
use App\Admin\Projects\SpecialProjects\ExampleProjectAdmin;
use App\Admin\Projects\SpecialProjects\FeaturedProjectAdmin;
use App\Admin\Projects\UploadProject\UploadProjectAdmin;
use App\Admin\Projects\UploadProject\UploadProjectController;
use App\Admin\Statistics\Translation\CommentMachineTranslationAdmin;
use App\Admin\Statistics\Translation\Controller\CommentMachineTranslationAdminController;
use App\Admin\Statistics\Translation\Controller\ProjectMachineTranslationAdminController;
use App\Admin\Statistics\Translation\ProjectCustomTranslationAdmin;
use App\Admin\Statistics\Translation\ProjectMachineTranslationAdmin;
use App\Admin\System\CronJobs\CronJobsAdmin;
use App\Admin\System\CronJobs\CronJobsAdminController;
use App\Admin\System\DB_Updater\AchievementsAdmin;
use App\Admin\System\DB_Updater\Controller\AchievementsAdminController;
use App\Admin\System\DB_Updater\Controller\ExtensionsAdminController;
use App\Admin\System\DB_Updater\Controller\FlavorsAdminController;
use App\Admin\System\DB_Updater\Controller\SpecialUpdaterAdminController;
use App\Admin\System\DB_Updater\Controller\TagsAdminController;
use App\Admin\System\DB_Updater\ExtensionsAdmin;
use App\Admin\System\DB_Updater\FlavorsAdmin;
use App\Admin\System\DB_Updater\SpecialUpdaterAdmin;
use App\Admin\System\DB_Updater\TagsAdmin;
use App\Admin\System\FeatureFlag\FeatureFlagAdmin;
use App\Admin\System\FeatureFlag\FeatureFlagController;
use App\Admin\System\Logs\LogsAdmin;
use App\Admin\System\Logs\LogsController;
use App\Admin\System\Maintenance\MaintenanceAdmin;
use App\Admin\System\Maintenance\MaintenanceController;
use App\Admin\UserCommunication\BroadcastNotification\BroadcastNotificationAdmin;
use App\Admin\UserCommunication\BroadcastNotification\BroadcastNotificationController;
use App\Admin\UserCommunication\MaintenanceInformation\MaintenanceInformationAdmin;
use App\Admin\UserCommunication\MaintenanceInformation\MaintenanceInformationController;
use App\Admin\UserCommunication\SendMailToUser\SendMailToUserAdmin;
use App\Admin\UserCommunication\SendMailToUser\SendMailToUserController;
use App\Admin\UserCommunication\Survey\AllSurveysAdmin;
use App\Admin\Users\ConsentLog\ConsentLogAdmin;
use App\Admin\Users\UserAdmin;
use App\Admin\Users\UserDataReport\UserDataReportAdmin;
use App\Admin\Users\UserDataReport\UserDataReportController;
use App\Api\AchievementsApi;
use App\Api\AuthenticationApi;
use App\Api\CommentsApi;
use App\Api\FollowersApi;
use App\Api\MediaLibraryApi;
use App\Api\ModerationApi;
use App\Api\NotificationsApi;
use App\Api\ProjectsApi;
use App\Api\SearchApi;
use App\Api\Services\OverwriteController;
use App\Api\StudioApi;
use App\Api\UserApi;
use App\Api\UtilityApi;
use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaAsset;
use App\DB\Entity\MediaLibrary\MediaCategory;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\ExampleProgram;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\System\CronJob;
use App\DB\Entity\System\FeatureFlag;
use App\DB\Entity\System\MaintenanceInformation;
use App\DB\Entity\System\Survey;
use App\DB\Entity\Translation\CommentMachineTranslation;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\ConsentLog;
use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\DB\Entity\User\User;
use App\Security\Authentication\ApiAuthenticationSuccessHandler;
use App\Security\Authentication\JwtRefresh\ApiRefreshTokenSuccessHandler;
use App\Security\Captcha\CaptchaVerifier;
use App\User\UserProvider;
use Monolog\Formatter\LineFormatter;
use OpenAPI\Server\Service\SerializerInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Dotenv\Command\DotenvDumpCommand;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();

  $parameters->set('catrobat.apk.dir', '%catrobat.pubdir%resources/apk/');
  $parameters->set('catrobat.featuredimage.dir', '%catrobat.pubdir%resources/featured/');
  $parameters->set('catrobat.featuredimage.path', 'resources/featured/');
  $parameters->set('catrobat.exampleimage.dir', '%catrobat.pubdir%resources/example/');
  $parameters->set('catrobat.exampleimage.path', 'resources/example/');
  $parameters->set('catrobat.file.extract.dir', '%kernel.project_dir%/public/resources/extract/');
  $parameters->set('catrobat.file.extract.path', 'resources/extract/');
  $parameters->set('catrobat.file.storage.dir', '%kernel.project_dir%/public/resources/programs/');
  $parameters->set('catrobat.file.storage.path', 'resources/programs/');
  $parameters->set('catrobat.logs.dir', '%kernel.project_dir%/var/log/');
  $parameters->set('catrobat.media.dir', '%catrobat.pubdir%resources/media/');
  $parameters->set('catrobat.media.path', 'resources/media/');
  $parameters->set('catrobat.media.sample.dir', '%kernel.project_dir%/tests/TestData/DataFixtures/Media/SampleMedia/');
  $parameters->set('catrobat.media.sample.path', 'tests/TestData/DataFixtures/Media/SampleMedia/');
  $parameters->set('catrobat.media.font.dir', '%catrobat.pubdir%/build/fonts/Roboto-Regular-webfont.ttf');
  $parameters->set('catrobat.media.font.path', 'build/fonts/Roboto-Regular-webfont.ttf');
  $parameters->set('catrobat.pubdir', '%kernel.project_dir%/public/');
  $parameters->set('catrobat.resources.dir', '%kernel.project_dir%/public/resources/');
  $parameters->set('catrobat.resources.path', '%catrobat.pubdir%resources/');
  $parameters->set('catrobat.screenshot.dir', '%catrobat.pubdir%resources/screenshots/');
  $parameters->set('catrobat.screenshot.path', 'resources/screenshots/');
  $parameters->set('catrobat.template.dir', '%catrobat.pubdir%resources/templates/');
  $parameters->set('catrobat.template.path', 'resources/templates/');
  $parameters->set('catrobat.template.screenshot.dir', '%catrobat.pubdir%resources/templates/screenshots/');
  $parameters->set('catrobat.template.screenshot.path', 'resources/templates/screenshots/');
  $parameters->set('catrobat.template.storage.dir', '%kernel.project_dir%/public/resources/templates/');
  $parameters->set('catrobat.template.storage.path', 'resources/templates/');
  $parameters->set('catrobat.template.thumbnail.dir', '%catrobat.pubdir%resources/templates/thumbnails/');
  $parameters->set('catrobat.template.thumbnail.path', 'resources/templates/thumbnails/');
  $parameters->set('catrobat.test.directory.source', '%kernel.project_dir%/tests/TestData/DataFixtures/');
  $parameters->set('catrobat.test.directory.target', '%kernel.project_dir%/tests/TestData/DataFixtures/GeneratedFixtures/');
  $parameters->set('catrobat.thumbnail.dir', '%catrobat.pubdir%resources/thumbnails/');
  $parameters->set('catrobat.thumbnail.path', 'resources/thumbnails/');
  $parameters->set('catrobat.translations.project_cache_threshold', 15);
  $parameters->set('catrobat.upload.temp.dir', '%catrobat.pubdir%resources/tmp/uploads/');
  $parameters->set('catrobat.upload.temp.path', 'resources/tmp/uploads/');
  $parameters->set('dkim.private.key', '%kernel.project_dir%/.dkim/private.key');
  $parameters->set('.container.dumper.inline_class_loader', true);
  $parameters->set('reset_password.throttle_limit', 86400);

  $services = $containerConfigurator->services();
  $services->defaults()
    ->autowire()        // Automatically inject dependencies
    ->autoconfigure()   // Automatically configure services with tags (e.g., listeners)

      // Load all classes in the App namespace as services, making them available for DI
    ->load('App\\', __DIR__.'/../src/*')

      // Exclude files and directories not meant for DI to improve performance and avoid misconfigurations
    ->exclude([
      __DIR__.'/../src/Kernel.php',                // Exclude Kernel, as it's not a service
      __DIR__.'/../src/System/Testing',            // Exclude testing classes
      __DIR__.'/../src/DB/Entity',                 // Exclude Doctrine entities from DI container
      __DIR__.'/../src/Api/OpenAPI',               // OpenAPI namespace has its own service definitions
    ])
  ;

  // Import OpenAPI service definitions from YAML
  $containerConfigurator->import(__DIR__.'/../src/Api/OpenAPI/Server/Resources/config/services.yaml');

  // Register additional vendor classes as services
  $services->set(UuidGenerator::class);
  $services->alias(Sonata\AdminBundle\SonataConfiguration::class, 'sonata.admin.configuration');
  $services->alias(UserProviderInterface::class, UserProvider::class);
  $services->set('security.acl.permission.map', AdminPermissionMap::class);
  $services->set(ApiAuthenticationSuccessHandler::class)
    ->args([
      service('lexik_jwt_authentication.handler.authentication_success'),
      service(App\Security\Authentication\AuthenticationModeResolver::class),
      service(App\Security\Authentication\MainFirewallSessionLogin::class),
      service(App\Security\Authentication\AuthenticationSuccessResponseProcessor::class),
    ])
  ;
  $services->set(ApiRefreshTokenSuccessHandler::class)
    ->args([
      service('gesdinet_jwt_refresh_token.security.authentication.success_handler'),
      service(App\Security\Authentication\AuthenticationSuccessResponseProcessor::class),
    ])
  ;

  // Custom formatting for our logs
  $logFormat = "[%%datetime%%] %%level_name%% %%channel%%: %%message%% | ip=%%extra.client_ip%% user=%%extra.session_user%% %%context%% %%extra%%\n";
  $services->set('monolog.formatter.catrobat_custom_formatter', LineFormatter::class)
    ->args([$logFormat, null, true, false])
    ->call('includeStacktraces', [true])
    ->call('setBasePath', ['/var/www/my_project'])
    ->call('indentStacktraces', ['    '])
    ->call('setMaxLevelNameLength', [5])
  ;

  $services->set(CaptchaVerifier::class)
    ->arg('$captchaVerifyUrl', '%env(CAPTCHA_API_ENDPOINT)%/%env(CAPTCHA_SITE_KEY)%/siteverify')
    ->arg('$captchaSecret', '%env(CAPTCHA_SECRET)%')
    ->arg('$captchaEnabled', '%env(bool:CAPTCHA_ENABLED)%')
    ->arg('$appEnv', '%env(APP_ENV)%')
  ;

  // -------------------------------------------------------------------------------------------------------------------
  // CAPI: Api service tagging
  //   - custom tags not yet supported by attributes
  //
  $services->set(MediaLibraryApi::class)->tag('open_api_server.api', ['api' => 'mediaLibrary']);
  $services->set(ProjectsApi::class)->tag('open_api_server.api', ['api' => 'projects']);
  $services->set(CommentsApi::class)->tag('open_api_server.api', ['api' => 'comments']);
  $services->set(UserApi::class)->tag('open_api_server.api', ['api' => 'user']);
  $services->set(AuthenticationApi::class)->tag('open_api_server.api', ['api' => 'authentication']);
  $services->set(UtilityApi::class)->tag('open_api_server.api', ['api' => 'utility']);
  $services->set(SearchApi::class)->tag('open_api_server.api', ['api' => 'search']);
  $services->set(NotificationsApi::class)->tag('open_api_server.api', ['api' => 'notifications']);
  $services->set(AchievementsApi::class)->tag('open_api_server.api', ['api' => 'achievements']);
  $services->set(FollowersApi::class)->tag('open_api_server.api', ['api' => 'followers']);
  $services->set(StudioApi::class)->tag('open_api_server.api', ['api' => 'studio']);
  $services->set(ModerationApi::class)->tag('open_api_server.api', ['api' => 'moderation']);
  $services->set(OverwriteController::class);
  $services->alias(SerializerInterface::class, 'open_api_server.service.serializer');

  // -------------------------------------------------------------------------------------------------------------------
  // Sonata admin service definitions:
  //   - used by config/packages/sonata_admin.php to build the navigation tree
  //   - each services defines a page in the admin interface - go to /admin
  //
  $services->set('admin.block.projects.overview', ProjectsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Projects Overview',
        'show_mosaic_button' => false,
        'default' => true,
        'code' => null,
        'model_class' => Program::class,
        'controller' => null,
        'pager_type' => 'simple',
      ]
    )
  ;
  $services->set('admin.block.projects.approve', ApproveProjectsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Approve Projects',
        'code' => null,
        'model_class' => Program::class,
        'controller' => ApproveProjectsController::class,
      ]
    )
  ;
  $services->set('admin.block.projects.broken', BrokenProjectsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Broken Projects',
        'show_mosaic_button' => false,
        'code' => null,
        'model_class' => Program::class,
        'controller' => null,
        'pager_type' => 'simple',
      ]
    )
  ;
  $services->set('admin.block.comments.overview', CommentsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Comments Overview',
        'show_mosaic_button' => false,
        'code' => null,
        'model_class' => UserComment::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.moderation.reports', ModerationQueueAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Moderation Queue',
        'code' => null,
        'model_class' => ContentReport::class,
        'controller' => ModerationQueueController::class,
      ]
    )
  ;
  $services->set('admin.block.moderation.appeals', AppealQueueAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Appeal Queue',
        'code' => null,
        'model_class' => ContentAppeal::class,
        'controller' => AppealQueueController::class,
      ]
    )
  ;
  $services->set('admin.media_library.category', MediaCategoryAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Media Library Categories',
        'show_mosaic_button' => false,
        'code' => null,
        'model_class' => MediaCategory::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.media_library.asset', MediaAssetAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Media Library Assets',
        'show_mosaic_button' => false,
        'code' => null,
        'model_class' => MediaAsset::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.featured.projects', FeaturedProjectAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Featured Projects',
        'code' => null,
        'model_class' => FeaturedProgram::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.example.projects', ExampleProjectAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Example Projects',
        'code' => null,
        'model_class' => ExampleProgram::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.projects.upload', UploadProjectAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Upload Project',
        'code' => null,
        'model_class' => Program::class,
        'controller' => UploadProjectController::class,
      ]
    )
  ;
  $services->set('admin.block.apk.pending', ApkPendingAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Pending',
        'code' => null,
        'model_class' => Program::class,
        'controller' => ApkController::class,
      ]
    )
  ;
  $services->set('admin.block.apk.list', ApkReadyAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Ready',
        'code' => null,
        'model_class' => Program::class,
        'controller' => ApkController::class,
      ]
    )
  ;
  $services->set('admin.block.users.overview', UserAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'model_class' => User::class,
        'label' => 'User Overview',
        'show_mosaic_button' => false,
        'default' => true,
        'code' => null,
        'controller' => null,
        'pager_type' => 'simple',
      ]
    )
  ;
  $services->set('admin.block.users.data_report', UserDataReportAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'User Data Report',
        'code' => null,
        'model_class' => User::class,
        'controller' => UserDataReportController::class,
      ]
    )
  ;
  $services->set('admin.block.users.consent_log', ConsentLogAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Consent Log',
        'show_mosaic_button' => false,
        'code' => null,
        'model_class' => ConsentLog::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.survey', AllSurveysAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Surveys',
        'pager_type' => 'simple',
        'icon' => '<i class="fa fa-cogs"></i>',
        'code' => null,
        'model_class' => Survey::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.special_updater', SpecialUpdaterAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'DB Special Updater',
        'icon' => '<i class="fa fa-cogs"></i>',
        'code' => null,
        'model_class' => CronJob::class,
        'controller' => SpecialUpdaterAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.cron_jobs', CronJobsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Cron Jobs',
        'icon' => '<i class="fa fa-hourglass"></i>',
        'code' => null,
        'model_class' => CronJob::class,
        'controller' => CronJobsAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.achievements', AchievementsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'DB-Updater Achievements',
        'code' => null,
        'model_class' => Achievement::class,
        'controller' => AchievementsAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.flavors', FlavorsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'DB-Updater Flavors',
        'code' => null,
        'model_class' => Flavor::class,
        'controller' => FlavorsAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.extensions', ExtensionsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'DB-Updater Extensions',
        'code' => null,
        'model_class' => Extension::class,
        'controller' => ExtensionsAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.tags', TagsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'DB-Updater Tags',
        'code' => null,
        'model_class' => Tag::class,
        'controller' => TagsAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.tools.maintain', MaintenanceAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'System Dashboard',
        'icon' => '<i class="fa fa-cogs"></i>',
        'code' => null,
        'model_class' => CronJob::class,
        'controller' => MaintenanceController::class,
      ]
    )
  ;
  $services->set('admin.block.tools.logs', LogsAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Logs',
        'icon' => '<i class="fa fa-cogs"></i>',
        'code' => null,
        'model_class' => CronJob::class,
        'controller' => LogsController::class,
      ]
    )
  ;
  $services->set('admin.block.tools.broadcast', BroadcastNotificationAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Send Notification',
        'code' => null,
        'model_class' => BroadcastNotification::class,
        'controller' => BroadcastNotificationController::class,
      ]
    )
  ;
  $services->set('admin.block.tools.mail', SendMailToUserAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Send Mail',
        'code' => null,
        'model_class' => CronJob::class,
        'controller' => SendMailToUserController::class,
      ]
    )
  ;
  $services->set('admin.block.tools.feature_flag', FeatureFlagAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Feature Flag',
        'code' => null,
        'model_class' => FeatureFlag::class,
        'controller' => FeatureFlagController::class,
      ]
    )
  ;
  $services->set('admin.block.tools.maintenance_information', MaintenanceInformationAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Maintenance Information',
        'code' => null,
        'model_class' => MaintenanceInformation::class,
        'controller' => MaintenanceInformationController::class,
      ]
    )
  ;
  $services->set('admin.block.statistics.project_machine_translation', ProjectMachineTranslationAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Project Machine Translation',
        'code' => null,
        'model_class' => ProjectMachineTranslation::class,
        'controller' => ProjectMachineTranslationAdminController::class,
      ]
    )
  ;
  $services->set('admin.block.statistics.project_custom_translation', ProjectCustomTranslationAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Project Custom Translation',
        'code' => null,
        'model_class' => ProjectCustomTranslation::class,
        'controller' => null,
      ]
    )
  ;
  $services->set('admin.block.statistics.comment_machine_translation', CommentMachineTranslationAdmin::class)
    ->tag(
      'sonata.admin',
      [
        'manager_type' => 'orm',
        'label' => 'Comment Machine Translation',
        'code' => null,
        'model_class' => CommentMachineTranslation::class,
        'controller' => CommentMachineTranslationAdminController::class,
      ]
    )
  ;

  // enable prod env dumping without composer
  $services->set(DotenvDumpCommand::class)
    ->tag('console.command')
    ->arg('$projectDir', '%kernel.project_dir%/.env')
    ->arg('$defaultEnv', '%kernel.environment%')
    ->public()
  ;

  // -------------------------------------------------------------------------------------------------------------------
  // Load additional services, or overwrite them for the test environment by defining them in services_test.php
  //
  if ('test' === ($_SERVER['APP_ENV'] ?? 'dev')) {
    $containerConfigurator->import('services_test.php');
  }
};
