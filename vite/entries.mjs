export const jsEntries = {
  base_layout: './assets/Layout/Base.js',
  color_scheme: './assets/Layout/ColorScheme.js',
  layout_language_menu: './assets/Layout/LanguageMenu.js',

  index_page: './assets/Index/IndexPage.js',

  login_page: './assets/Security/LoginPage.js',
  registration_page: './assets/Security/RegistrationPage.js',
  password_reset_page: './assets/Security/PasswordResetPage.js',
  request_to_reset_password_page: './assets/Security/RequestPasswordResetPage.js',

  search_page: './assets/Search/SearchPage.js',

  project_page: './assets/Project/ProjectPage.js',
  project_comments_page: './assets/Project/ProjectCommentsPage.js',
  project_code_statistics_inline: './assets/Project/CodeStatisticsInline.js',
  project_code_view_inline: './assets/Project/CodeViewInline.js',
  projects_browse_page: './assets/Project/ProjectsBrowsePage.js',
  project_upload_page: './assets/Project/ProjectUploadPage.js',

  user_achievements_page: './assets/User/AchievementsPage.js',
  user_notifications_page: './assets/User/NotificationsPage.js',
  user_reports_page: './assets/User/ReportsPage.js',
  user_my_profile_page: './assets/User/MyProfilePage.js',
  user_profile_page: './assets/User/ProfilePage.js',
  user_follower_overview: './assets/User/FollowerOverview.js',
  user_profile_studios: './assets/User/ProfileStudios.js',

  media_library_overview_page: './assets/MediaLibrary/OverviewPage.js',
  media_library_category_detail_page: './assets/MediaLibrary/CategoryDetailPage.js',

  studio_detail_page: './assets/Studio/StudioDetailPage.js',
  studios_page: './assets/Studio/StudiosPage.js',
  studio_create_page: './assets/Studio/CreatePage.js',

  admin_standard_layout: './assets/Admin/Layout.js',
  admin_user_communication_broadcast_notification:
    './assets/Admin/UserCommunication/BroadcastNotification.js',
  admin_user_communication_send_mail: './assets/Admin/UserCommunication/SendMail.js',
  admin_user_communication_maintenance_information_icon_list:
    './assets/Admin/UserCommunication/MaintenanceInformation/IconList.js',
  admin_system_management_logs: './assets/Admin/SystemManagement/Logs.js',
  admin_system_management_maintain: './assets/Admin/SystemManagement/Maintain.js',
  admin_statistics_machine_translation: './assets/Admin/Statistics/MachineTranslation.js',
}

export const cssEntries = {
  layout_multi_column_article: './assets/Layout/MultiColumnArticle.scss',
  twig_bundle_error: './assets/bundles/TwigBundle/Exception/error.scss',
  admin_system_management_db_updater_achievements: './assets/User/Achievements.scss',

  // Themes — selected at runtime via request_theme.
  pocketcode: './assets/Theme/pocketcode.scss',
  arduino: './assets/Theme/arduino.scss',
  'create@school': './assets/Theme/create@school.scss',
  embroidery: './assets/Theme/embroidery.scss',
  luna: './assets/Theme/luna.scss',
  phirocode: './assets/Theme/phirocode.scss',
  pocketalice: './assets/Theme/pocketalice.scss',
  pocketgalaxy: './assets/Theme/pocketgalaxy.scss',
  mindstorms: './assets/Theme/mindstorms.scss',
}
