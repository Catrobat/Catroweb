<?php

declare(strict_types=1);

use App\Admin\ApkGeneration\ApkController;
use App\Admin\ApkGeneration\ApkPendingAdmin;
use App\Admin\ApkGeneration\ApkReadyAdmin;
use App\Admin\Comments\CommentsAdmin;
use App\Admin\Comments\ReportedComments\ReportedCommentsAdmin;
use App\Admin\Comments\ReportedComments\ReportedCommentsController;
use App\Admin\DB_Updater\AchievementsAdmin;
use App\Admin\DB_Updater\Controller\AchievementsAdminController;
use App\Admin\DB_Updater\Controller\CronJobsAdminController;
use App\Admin\DB_Updater\Controller\ExtensionsAdminController;
use App\Admin\DB_Updater\Controller\SpecialUpdaterAdminController;
use App\Admin\DB_Updater\Controller\TagsAdminController;
use App\Admin\DB_Updater\CronJobsAdmin;
use App\Admin\DB_Updater\ExtensionsAdmin;
use App\Admin\DB_Updater\SpecialUpdaterAdmin;
use App\Admin\DB_Updater\TagsAdmin;
use App\Admin\MediaPackage\MediaPackageAdmin;
use App\Admin\MediaPackage\MediaPackageCategoriesAdmin;
use App\Admin\MediaPackage\MediaPackageCategoryController;
use App\Admin\MediaPackage\MediaPackageFileAdmin;
use App\Admin\Projects\ApproveProjects\ApproveProjectsAdmin;
use App\Admin\Projects\ApproveProjects\ApproveProjectsController;
use App\Admin\Projects\ProjectsAdmin;
use App\Admin\Projects\ReportedProjects\ReportedProjectsAdmin;
use App\Admin\Projects\ReportedProjects\ReportedProjectsController;
use App\Admin\SpecialProjects\ExampleProgramAdmin;
use App\Admin\SpecialProjects\FeaturedProgramAdmin;
use App\Admin\Statistics\Translation\CommentMachineTranslationAdmin;
use App\Admin\Statistics\Translation\Controller\CommentMachineTranslationAdminController;
use App\Admin\Statistics\Translation\Controller\ProjectMachineTranslationAdminController;
use App\Admin\Statistics\Translation\ProjectCustomTranslationAdmin;
use App\Admin\Statistics\Translation\ProjectMachineTranslationAdmin;
use App\Admin\Survey\AllSurveysAdmin;
use App\Admin\Tools\BroadcastNotification\BroadcastNotificationAdmin;
use App\Admin\Tools\BroadcastNotification\BroadcastNotificationController;
use App\Admin\Tools\FeatureFlag\FeatureFlagAdmin;
use App\Admin\Tools\FeatureFlag\FeatureFlagController;
use App\Admin\Tools\FeatureFlag\FeatureFlagManager;
use App\Admin\Tools\Logs\Controller\LogsController;
use App\Admin\Tools\Logs\LogsAdmin;
use App\Admin\Tools\Maintenance\MaintainAdmin;
use App\Admin\Tools\Maintenance\MaintainController;
use App\Admin\Tools\MaintenanceInformation\MaintenanceInformationAdmin;
use App\Admin\Tools\MaintenanceInformation\MaintenanceInformationController;
use App\Admin\Tools\SendMailToUser\SendMailToUserAdmin;
use App\Admin\Tools\SendMailToUser\SendMailToUserController;
use App\Admin\Users\ReportedUsers\ReportedUsersAdmin;
use App\Admin\Users\ReportedUsers\ReportedUsersController;
use App\Admin\Users\UserAdmin;
use App\Admin\Users\UserDataReport\UserDataReportAdmin;
use App\Admin\Users\UserDataReport\UserDataReportController;
use App\Api\AuthenticationApi;
use App\Api\Exceptions\ApiExceptionSubscriber;
use App\Api\MediaLibraryApi;
use App\Api\NotificationsApi;
use App\Api\ProjectsApi;
use App\Api\SearchApi;
use App\Api\Services\Authentication\JWTTokenRefreshService;
use App\Api\Services\OverwriteController;
use App\Api\UserApi;
use App\Api\UtilityApi;
use App\Api_deprecated\Listeners\ProgramListSerializerEventSubscriber;
use App\Api_deprecated\Listeners\UploadExceptionEventSubscriber;
use App\Api_deprecated\OAuth\OAuthService;
use App\Api_deprecated\Security\ApiTokenAuthenticator;
use App\Application\Controller\Ci\BuildApkController;
use App\Application\Controller\MediaLibrary\MediaPackageController;
use App\Application\Framework\ExceptionEventSubscriber;
use App\Application\Framework\VersionStrategy;
use App\Application\Locale\LocaleEventSubscriber;
use App\Application\Theme\ThemeRequestEventSubscriber;
use App\Application\Twig\TwigExtension;
use App\DB\Entity\FeatureFlag;
use App\DB\Entity\MaintenanceInformation;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\Project\Special\ExampleProgram;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\Survey;
use App\DB\Entity\System\CronJob;
use App\DB\Entity\Translation\CommentMachineTranslation;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\DB\Entity\User\User;
use App\DB\Generator\MyUuidGenerator;
use App\Project\Apk\ApkCleanupEventSubscriber;
use App\Project\Apk\ApkRepository;
use App\Project\Apk\JenkinsDispatcher;
use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatFile\CatrobatFileCompressor;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\CatrobatFileSanitizer;
use App\Project\CatrobatFile\DescriptionValidatorEventSubscriber;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\LicenseUpdaterEventSubscriber;
use App\Project\CatrobatFile\NameValidatorEventSubscriber;
use App\Project\CatrobatFile\NotesAndCreditsValidatorEventSubscriber;
use App\Project\CatrobatFile\ProgramFileRepository;
use App\Project\CatrobatFile\ProgramFlavorEventSubscriber;
use App\Project\CatrobatFile\ProgramXmlHeaderValidatorEventSubscriber;
use App\Project\CatrobatFile\VersionValidatorEventSubscriber;
use App\Project\EventListener\ExampleProgramImageListener;
use App\Project\EventListener\FeaturedProgramImageListener;
use App\Project\EventListener\ProjectPostUpdateNotifier;
use App\Project\EventSubscriber\ProjectDownloadEventSubscriber;
use App\Project\Extension\ProjectExtensionEventSubscriber;
use App\Project\Extension\ProjectExtensionManager;
use App\Project\ProjectManager;
use App\Project\Remix\RemixGraphManipulator;
use App\Project\Remix\RemixManager;
use App\Project\Remix\RemixSubgraphManipulator;
use App\Project\Remix\RemixUpdaterEventSubscriber;
use App\Project\Scratch\AsyncHttpClient;
use App\Project\Scratch\ScratchManager;
use App\Project\Scratch\ScratchProjectUpdaterEventSubscriber;
use App\Security\Authentication\CookieService;
use App\Security\Authentication\JwtRefresh\RefreshBearerCookieOnKernelResponseEventSubscriber;
use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use App\Security\Authentication\WebView\WebviewAuthenticator;
use App\Security\Authentication\WebView\WebviewJWTAuthenticator;
use App\Security\OAuth\HwiOauthAccountConnector;
use App\Security\OAuth\HwiOauthRegistrationFormHandler;
use App\Security\OAuth\HwiOauthRegistrationFormType;
use App\Security\OAuth\HwiOauthUserProvider;
use App\Security\OAuth\OAuthSuccessHandler;
use App\Security\TokenGenerator;
use App\Storage\ImageRepository;
use App\Storage\ScreenshotRepository;
use App\Studio\StudioManager;
use App\System\Commands\Helpers\RemixManipulationProjectManager;
use App\System\Log\LoggerProcessor;
use App\System\Mail\MailerAdapter;
use App\Translation\CustomTranslationAchievementEventSubscriber;
use App\Translation\GoogleTranslateApi;
use App\Translation\ItranslateApi;
use App\Translation\MachineTranslationEventSubscriber;
use App\Translation\TranslationDelegate;
use App\User\Achievements\AchievementManager;
use App\User\EventListener\UserPostPersistNotifier;
use App\User\EventListener\UserPostUpdateNotifier;
use App\User\Notification\NotificationManager;
use App\User\ResetPassword\PasswordResetRequestedSubscriber;
use App\User\UserManager;
use App\Utils\CanonicalFieldsUpdater;
use App\Utils\ElapsedTimeStringFormatter;
use App\Utils\RequestHelper;
use Google\Cloud\Translate\V2\TranslateClient;
use Monolog\Formatter\LineFormatter;
use OpenAPI\Server\Service\SerializerInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
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
  $parameters->set('catrobat.mediapackage.dir', '%catrobat.pubdir%resources/mediapackage/');
  $parameters->set('catrobat.mediapackage.path', 'resources/mediapackage/');
  $parameters->set('catrobat.mediapackage.sample.dir', '%catrobat.pubdir%tests/TestData/DataFixtures/MediaPackage/SampleMediaPackage/');
  $parameters->set('catrobat.mediapackage.sample.path', 'tests/TestData/DataFixtures/MediaPackage/SampleMediaPackage/');
  $parameters->set('catrobat.mediapackage.font.dir', '%catrobat.pubdir%webfonts/fa-solid-900.ttf');
  $parameters->set('catrobat.mediapackage.font.path', 'webfonts/fa-solid-900.ttf');
  $parameters->set('catrobat.pubdir', '%kernel.project_dir%/public/');
  $parameters->set('catrobat.resources.dir', '%kernel.project_dir%/public/resources/');
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
  $parameters->set('catrobat.translations.dir', '%kernel.project_dir%/translations');
  $parameters->set('catrobat.translations.project_cache_threshold', 15);
  $parameters->set('catrobat.upload.temp.dir', '%catrobat.pubdir%resources/tmp/uploads/');
  $parameters->set('catrobat.upload.temp.path', 'resources/tmp/uploads/');
  $parameters->set('es_host', '%env(ES_HOST)%');
  $parameters->set('es_port', '%env(ES_PORT)%');
  $parameters->set('dkim.private.key', '%kernel.project_dir%/.dkim/private.key');
  $parameters->set('container.dumper.inline_class_loader', true);
  $parameters->set('features', '%kernel.project_dir%/config/features.php');
  $parameters->set('reset_password.throttle_limit', 86400);

  $services = $containerConfigurator->services();

  $services->set('security.acl.permission.map', AdminPermissionMap::class);

  $services->defaults()
    ->autowire()
    ->autoconfigure()
    ->bind('$kernel_root_dir', '%kernel.project_dir%')
    ->bind('$catrobat_translation_dir', '%catrobat.translations.dir%')
    ->bind('$catrobat_file_storage_dir', '%catrobat.file.storage.dir%')
    ->bind('$catrobat_file_extract_dir', '%catrobat.file.extract.dir%')
    ->bind('$program_finder', service('fos_elastica.finder.app_program'))
    ->bind('$user_finder', service('fos_elastica.finder.app_user'))
    ->bind('$refresh_token_ttl', '%lexik_jwt_authentication.token_ttl%')
    ->bind('$dkim_private_key_path', '%dkim.private.key%')
  ;

  $services->load('App\DB\EntityRepository\\', __DIR__.'/../src/DB/EntityRepository/*')
    ->public()
  ;

  $services->load('App\Api\Services\\', __DIR__.'/../src/Api/Services/*')
    ->public()
  ;

  $services->set(UserManager::class, UserManager::class)
    ->public()
  ;

  $services->alias(Sonata\UserBundle\Entity\UserManager::class, 'sonata.user.manager.user');

  $services->alias(UserProviderInterface::class, 'sonata.user.security.user_provider');

  $services->set(ProjectManager::class, ProjectManager::class)
    ->public()
  ;

  $services->set(ProjectExtensionManager::class, ProjectExtensionManager::class)
    ->public()
  ;

  $services->set(RemixManipulationProjectManager::class, RemixManipulationProjectManager::class)
    ->public()
  ;

  $services->set(RemixGraphManipulator::class, RemixGraphManipulator::class)
    ->public()
  ;

  $services->set(RemixSubgraphManipulator::class, RemixSubgraphManipulator::class)
    ->public()
  ;

  $services->load('App\Application\Controller\\', __DIR__.'/../src/Application/Controller')
    ->public()
  ;

  $services->load('App\Api_deprecated\Controller\\', __DIR__.'/../src/Api_deprecated/Controller')
    ->public()
  ;

  $services->set(ApkController::class, ApkController::class)
    ->public()
  ;

  $services->set(ReportedCommentsController::class, ReportedCommentsController::class)
    ->public()
  ;

  $services->set(MediaPackageCategoryController::class, MediaPackageCategoryController::class)
    ->public()
  ;

  $services->set(ApproveProjectsController::class, ApproveProjectsController::class)
    ->public()
  ;

  $services->set(ReportedProjectsController::class, ReportedProjectsController::class)
    ->public()
  ;

  $services->set(BroadcastNotificationController::class, BroadcastNotificationController::class)
    ->public()
  ;

  $services->load('App\Admin\Tools\Logs\Controller\\', __DIR__.'/../src/Admin/Tools/Logs/Controller')
    ->public()
  ;

  $services->set(MaintainController::class, MaintainController::class)
    ->public()
    ->arg('$file_storage_dir', '%catrobat.file.storage.dir%')
    ->arg('$apk_dir', '%catrobat.apk.dir%')
    ->arg('$log_dir', '%catrobat.logs.dir%')
  ;

  $services->set(SendMailToUserController::class, SendMailToUserController::class)
    ->public()
  ;

  $services->set(FeatureFlagManager::class, FeatureFlagManager::class)
    ->public()
  ;

  $services->set(FeatureFlagController::class, FeatureFlagController::class)
    ->public()
  ;

  $services->set(MaintenanceInformationController::class, MaintenanceInformationController::class)
    ->public()
  ;
  $services->set(ReportedUsersController::class, ReportedUsersController::class)
    ->public()
  ;

  $services->set(UserDataReportController::class, UserDataReportController::class)
    ->public()
  ;

  $services->load('App\Admin\DB_Updater\Controller\\', __DIR__.'/../src/Admin/DB_Updater/Controller')
    ->public()
  ;

  $services->load('App\Admin\Statistics\Translation\Controller\\', __DIR__.'/../src/Admin/Statistics/Translation/Controller')
    ->public()
  ;

  $services->load('App\System\Commands\\', __DIR__.'/../src/System/Commands')
    ->tag('console.command')
    ->exclude([__DIR__.'/../src/System/Commands/Helpers'])
  ;

  $services->set(JWTTokenRefreshService::class)
    ->public()
    ->args([service('gesdinet.jwtrefreshtoken')])
  ;

  $services->set(CatrobatFileSanitizer::class, CatrobatFileSanitizer::class)
    ->public()
  ;

  $services->set(NotificationManager::class, NotificationManager::class)
    ->public()
  ;

  $services->set(RemixManager::class, RemixManager::class)
    ->public()
  ;

  $services->set(AchievementManager::class, AchievementManager::class)
    ->public()
  ;

  $services->set(ScratchManager::class, ScratchManager::class)
    ->public()
  ;

  $services->set(StudioManager::class, StudioManager::class)
    ->public()
  ;

  $services->set(ScreenshotRepository::class, ScreenshotRepository::class)
    ->public()
  ;

  $services->set(ProgramFileRepository::class, ProgramFileRepository::class)
    ->public()
  ;

  $services->set(ExtractedFileRepository::class, ExtractedFileRepository::class)
    ->public()
  ;

  $services->set(ImageRepository::class, ImageRepository::class)
    ->public()
  ;

  $services->set(ApkRepository::class, ApkRepository::class)
    ->public()
  ;

  $services->set(CatrobatFileCompressor::class, CatrobatFileCompressor::class)
    ->public()
  ;

  $services->set(TokenGenerator::class, TokenGenerator::class)
    ->public()
  ;

  $services->set(CatrobatCodeParser::class, CatrobatCodeParser::class)
    ->public()
  ;

  $services->set(CanonicalFieldsUpdater::class, CanonicalFieldsUpdater::class)
    ->public()
  ;

  $services->set(RequestHelper::class, RequestHelper::class)
    ->public()
  ;

  $services->set(ElapsedTimeStringFormatter::class, ElapsedTimeStringFormatter::class)
    ->public()
  ;

  $services->set(JenkinsDispatcher::class, JenkinsDispatcher::class)
    ->args(['%jenkins%'])
    ->public()
  ;

  $services->set(ApiTokenAuthenticator::class, ApiTokenAuthenticator::class)
    ->public()
  ;

  $services->set(WebviewAuthenticator::class, WebviewAuthenticator::class)
    ->public()
  ;

  $services->set(WebviewJWTAuthenticator::class, WebviewJWTAuthenticator::class)
    ->public()
  ;

  $services->set(CookieService::class, CookieService::class)
    ->args(['%env(JWT_TTL)%', '%env(REFRESH_TOKEN_TTL)%'])
    ->public()
  ;

  $services->set(RefreshTokenService::class)
    ->args(['%env(REFRESH_TOKEN_TTL)%'])
    ->public()
  ;

  $services->set(OAuthService::class, OAuthService::class)
    ->public()
  ;

  $services->set(AsyncHttpClient::class, AsyncHttpClient::class)
    ->args([['timeout' => 8.0], ['max_number_of_total_requests' => 12], ['max_number_of_concurrent_requests' => 4]])
    ->public()
  ;

  $services->set(MyUuidGenerator::class, MyUuidGenerator::class)
    ->public()
  ;

  $services->set(CatrobatFileExtractor::class, CatrobatFileExtractor::class)
    ->args(['%catrobat.file.extract.dir%', '%catrobat.file.extract.path%'])
    ->public()
  ;

  $services->set(TwigExtension::class, TwigExtension::class)
    ->tag('twig.extension')
  ;

  $services->set(ProjectPostUpdateNotifier::class)
    ->tag('doctrine.orm.entity_listener', ['event' => 'postUpdate', 'entity' => Program::class])
  ;

  $services->set(UserPostPersistNotifier::class)
    ->tag('doctrine.orm.entity_listener', ['event' => 'postPersist', 'entity' => User::class])
  ;

  $services->set(UserPostUpdateNotifier::class)
    ->tag('doctrine.orm.entity_listener', ['event' => 'postUpdate', 'entity' => User::class])
  ;

  $services->set(ProjectDownloadEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(PasswordResetRequestedSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ExceptionEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(RefreshBearerCookieOnKernelResponseEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(LocaleEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ThemeRequestEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(NameValidatorEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(DescriptionValidatorEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(NotesAndCreditsValidatorEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(VersionValidatorEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ProgramXmlHeaderValidatorEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(LicenseUpdaterEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(RemixUpdaterEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ProgramFlavorEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ApkCleanupEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ProjectExtensionEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(CustomTranslationAchievementEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(MachineTranslationEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ProgramListSerializerEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(UploadExceptionEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set(ScratchProjectUpdaterEventSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services
    ->set(ApiExceptionSubscriber::class)
    ->tag('kernel.event_subscriber')
  ;

  $services->set('catroweb.oauth_success_handler', OAuthSuccessHandler::class);

  $services->set('monolog.formatter.catrobat_custom_formatter', LineFormatter::class)
    ->call('includeStacktraces', ["[%%datetime%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%stacktrace%%[Client IP: %%extra.client_ip%%, User Agent: %%extra.user_agent%%, Session User: %%extra.session_user%%]\n"])
  ;

  $services->set(LoggerProcessor::class)
    ->tag('monolog.processor')
  ;

  $services->set(MailerAdapter::class, MailerAdapter::class)
    ->public()
  ;

  $services->set(FeaturedProgramImageListener::class, FeaturedProgramImageListener::class)
    ->tag('doctrine.orm.entity_listener')
  ;

  $services->set(ExampleProgramImageListener::class, ExampleProgramImageListener::class)
    ->tag('doctrine.orm.entity_listener')
  ;

  $services->set(UuidGenerator::class, UuidGenerator::class)
    ->public()
  ;

  $services->set(VersionStrategy::class, VersionStrategy::class)
    ->args(['%env(string:APP_VERSION)%'])
  ;

  $services->set(BuildApkController::class)
    ->arg('$arr_jenkins_config', '%jenkins%')
  ;

  $services->set(MediaPackageController::class)
    ->arg('$catrobat_mediapackage_path', '%catrobat.mediapackage.path%')
  ;

  $services->set('admin.block.projects.overview', ProjectsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Projects Overview', 'show_mosaic_button' => false, 'default' => true, 'code' => null, 'model_class' => Program::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.projects.approve', ApproveProjectsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Approve Projects', 'code' => null, 'model_class' => Program::class, 'controller' => ApproveProjectsController::class])
    ->public()
  ;

  $services->set('admin.block.projects.reported', ReportedProjectsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Reported Projects', 'code' => null, 'model_class' => ProgramInappropriateReport::class, 'controller' => ReportedProjectsController::class])
    ->public()
  ;

  $services->set('admin.block.comments.overview', CommentsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Comments Overview', 'show_mosaic_button' => false, 'code' => null, 'model_class' => UserComment::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.comments.reported', ReportedCommentsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Reported Comments', 'code' => null, 'model_class' => UserComment::class, 'controller' => ReportedCommentsController::class])
    ->public()
  ;

  $services->set('admin.block.featured.program', FeaturedProgramAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Featured Projects', 'code' => null, 'model_class' => FeaturedProgram::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.example.program', ExampleProgramAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Example Projects', 'code' => null, 'model_class' => ExampleProgram::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.mediapackage.package', MediaPackageAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Packages', 'code' => null, 'model_class' => MediaPackage::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.mediapackage.category', MediaPackageCategoriesAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Categories', 'code' => null, 'model_class' => MediaPackageCategory::class, 'controller' => MediaPackageCategoryController::class])
    ->public()
  ;

  $services->set('admin.block.mediapackage.file', MediaPackageFileAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Files', 'code' => null, 'model_class' => MediaPackageFile::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.apk.pending', ApkPendingAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Pending', 'code' => null, 'model_class' => Program::class, 'controller' => ApkController::class])
    ->public()
  ;

  $services->set('admin.block.apk.list', ApkReadyAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Ready', 'code' => null, 'model_class' => Program::class, 'controller' => ApkController::class])
    ->public()
  ;

  $services->set('admin.block.users.overview', UserAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => User::class, 'label' => 'User Overview', 'show_mosaic_button' => false])
    ->args([service(UserManager::class), null, null])
  ;

  $services->set('admin.block.users.data_report', UserDataReportAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'User Data Report', 'code' => null, 'model_class' => User::class, 'controller' => UserDataReportController::class])
    ->public()
  ;

  $services->set('admin.block.users.reported', ReportedUsersAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Reported Users', 'pager_type' => 'simple', 'code' => null, 'model_class' => User::class, 'controller' => ReportedUsersController::class])
    ->public()
  ;

  $services->set('admin.block.survey', AllSurveysAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'All Surveys', 'pager_type' => 'simple', 'icon' => '<i class="fa fa-cogs"></i>', 'code' => null, 'model_class' => Survey::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.special_updater', SpecialUpdaterAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Special Updater', 'icon' => '<i class="fa fa-cogs"></i>', 'code' => null, 'model_class' => CronJob::class, 'controller' => SpecialUpdaterAdminController::class])
    ->public()
  ;

  $services->set('admin.block.cron_jobs', CronJobsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Cron Jobs', 'icon' => '<i class="fa fa-hourglass"></i>', 'code' => null, 'model_class' => CronJob::class, 'controller' => CronJobsAdminController::class])
    ->public()
  ;

  $services->set('admin.block.achievements', AchievementsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Achievements', 'code' => null, 'model_class' => Achievement::class, 'controller' => AchievementsAdminController::class])
    ->public()
  ;

  $services->set('admin.block.extensions', ExtensionsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Extensions', 'code' => null, 'model_class' => Extension::class, 'controller' => ExtensionsAdminController::class])
    ->public()
  ;

  $services->set('admin.block.tags', TagsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Tags', 'code' => null, 'model_class' => Tag::class, 'controller' => TagsAdminController::class])
    ->public()
  ;

  $services->set('admin.block.tools.maintain', MaintainAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Maintain', 'icon' => '<i class="fa fa-cogs"></i>', 'code' => null, 'model_class' => CronJob::class, 'controller' => MaintainController::class])
    ->public()
  ;

  $services->set('admin.block.tools.logs', LogsAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Logs', 'icon' => '<i class="fa fa-cogs"></i>', 'code' => null, 'model_class' => CronJob::class, 'controller' => LogsController::class])
    ->public()
  ;

  $services->set('admin.block.tools.broadcast', BroadcastNotificationAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Broadcast Notification', 'code' => null, 'model_class' => BroadcastNotification::class, 'controller' => BroadcastNotificationController::class])
    ->public()
  ;

  $services->set('admin.block.tools.mail', SendMailToUserAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Send Mail to User', 'code' => null, 'model_class' => CronJob::class, 'controller' => SendMailToUserController::class])
    ->public()
  ;

  $services->set('admin.block.tools.feature_flag', FeatureFlagAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Feature Flag', 'code' => null, 'model_class' => FeatureFlag::class, 'controller' => FeatureFlagController::class])
    ->public()
  ;
  $services->set('admin.block.tools.maintenance_information', MaintenanceInformationAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Maintenance Information', 'code' => null, 'model_class' => MaintenanceInformation::class, 'controller' => MaintenanceInformationController::class])
    ->public()
  ;
  $services->alias(SerializerInterface::class, 'open_api_server.service.serializer');

  $services->set('api.media-library', MediaLibraryApi::class)
    ->tag('open_api_server.api', ['api' => 'mediaLibrary'])
  ;

  $services->set('api.projects', ProjectsApi::class)
    ->tag('open_api_server.api', ['api' => 'projects'])
  ;

  $services->set('api.user', UserApi::class)
    ->tag('open_api_server.api', ['api' => 'user'])
  ;

  $services->set('api.authentication', AuthenticationApi::class)
    ->tag('open_api_server.api', ['api' => 'authentication'])
  ;

  $services->set('api.utility', UtilityApi::class)
    ->tag('open_api_server.api', ['api' => 'utility'])
  ;

  $services->set('api.search', SearchApi::class)
    ->tag('open_api_server.api', ['api' => 'search'])
  ;

  $services->set('api.notifications', NotificationsApi::class)
    ->tag('open_api_server.api', ['api' => 'notifications'])
  ;

  $services->set(OverwriteController::class)
    ->public()
  ;

  $services->set(ProjectsApi::class)
    ->public()
  ;

  $services->set(HwiOauthUserProvider::class, HwiOauthUserProvider::class)
    ->args([service(UserManager::class), ['google' => 'google_id', 'facebook' => 'facebook_id', 'apple' => 'apple_id']])
  ;

  $services->set(HwiOauthAccountConnector::class, HwiOauthAccountConnector::class)
    ->args([service(UserManager::class), ['google' => 'google_id', 'facebook' => 'facebook_id', 'apple' => 'apple_id']])
  ;

  $services->set(HwiOauthRegistrationFormHandler::class, HwiOauthRegistrationFormHandler::class);

  $services->set(HwiOauthRegistrationFormType::class, HwiOauthRegistrationFormType::class);

  $services->set(ItranslateApi::class, ItranslateApi::class)
    ->args([service('eight_points_guzzle.client.itranslate')])
  ;

  $services->set(GoogleTranslateApi::class, GoogleTranslateApi::class)
    ->arg('$client', service(TranslateClient::class))
    ->arg('$short_text_length', 20)
  ;

  $services->set(TranslateClient::class);

  $services->set(TranslationDelegate::class, TranslationDelegate::class)
    ->arg('$apis', [service(ItranslateApi::class), service(GoogleTranslateApi::class)])
  ;

  $services->set('admin.block.statistics.project_machine_translation', ProjectMachineTranslationAdmin::class)
    ->tag('sonata.admin', ['code' => null, 'model_class' => ProjectMachineTranslation::class, 'controller' => ProjectMachineTranslationAdminController::class, 'manager_type' => 'orm', 'label' => 'Project Machine Translation'])
    ->public()
  ;

  $services->set('admin.block.statistics.project_custom_translation', ProjectCustomTranslationAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Project Custom Translation', 'code' => null, 'model_class' => ProjectCustomTranslation::class, 'controller' => null])
    ->public()
  ;

  $services->set('admin.block.statistics.comment_machine_translation', CommentMachineTranslationAdmin::class)
    ->tag('sonata.admin', ['manager_type' => 'orm', 'label' => 'Comment Machine Translation', 'code' => null, 'model_class' => CommentMachineTranslation::class, 'controller' => CommentMachineTranslationAdminController::class])
    ->public()
  ;

  if ('test' === $_SERVER['APP_ENV']) {
    $containerConfigurator->import('services_test.php');
  }
};
