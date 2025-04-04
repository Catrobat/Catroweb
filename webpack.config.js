const Encore = require('@symfony/webpack-encore')
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin')
const { BugsnagSourceMapUploaderPlugin } = require('webpack-bugsnag-plugins')
const ESLintPlugin = require('eslint-webpack-plugin')
const dotenv = require('dotenv')
const glob = require('glob-all')
const path = require('path')
const webpack = require('webpack')
const noop = require('noop-webpack-plugin')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}
Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')

  // overwriting the css versioning until the transition to webpack is full done
  .configureFilenames({
    css: 'css/[name].css', // -[contenthash] to be used once styles are only imported!
    js: 'js/[name]-[chunkhash].js',
  })

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  .copyFiles([
    // Images
    { from: './assets/images', to: '../images/[path][name].[ext]' },

    // Favicon
    { from: './assets/images', pattern: /favicon\.ico/, to: '../[name].[ext]' },

    // Catblocks
    { from: './assets/catblocks', to: '../catblocks/[path][name].[ext]' },

    // Fonts
    { from: './assets/Fonts', to: '/fonts/[path][name].[ext]' },

    // Remix graph (deprecated!) - Complete rework needed
    { from: './assets/Legacy', to: '../js/[path][name].[ext]' },
    { from: './node_modules/vis/dist/', to: '../vis/[path][name].[ext]' },
    {
      from: './node_modules/jquery/dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/jquery-ui-dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/jquery-contextmenu/dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/jquery-contextmenu/dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/jquery-contextmenu/dist/',
      pattern: /\.css$/,
      to: '../css/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/animate.css/',
      pattern: /\.css$/,
      to: '../css/modules/[path][name].[ext]',
    },
  ])

  /*
   * ENTRY CONFIG
   *
   * Add 1 entry for each "page" of your app
   * (including one that's included on every page - e.g. "app")
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry('base_layout', './assets/Layout/Base.js')
  .addEntry('layout_language_menu', './assets/Layout/LanguageMenu.js')
  .addStyleEntry('layout_multi_column_article', './assets/Layout/MultiColumnArticle.scss')

  .addEntry('index_page', './assets/Index/IndexPage.js')

  .addEntry('login_page', './assets/Security/LoginPage.js')
  .addEntry('registration_page', './assets/Security/RegistrationPage.js')
  .addEntry('password_reset_page', './assets/Security/PasswordResetPage.js')
  .addEntry('request_to_reset_password_page', './assets/Security/RequestPasswordResetPage.js')

  .addEntry('legacy_search_page', './assets/Search/LegacySearchPage.js')
  .addEntry('search_page', './assets/Search/SearchPage.js')

  .addEntry('project_page', './assets/Project/ProjectPage.js')
  .addEntry('project_comments_page', './assets/Project/ProjectCommentsPage.js')
  .addEntry('project_code_view', './assets/Project/CodeView.js')
  .addStyleEntry('project_legacy_code_view', './assets/Project/LegacyCodeView.scss')
  .addEntry('project_code_statistics', './assets/Project/CodeStatistics.js')

  .addEntry('user_achievements_page', './assets/User/AchievementsPage.js')
  .addEntry('user_notifications_page', './assets/User/NotificationsPage.js')
  .addEntry('user_my_profile_page', './assets/User/MyProfilePage.js')
  .addEntry('user_profile_page', './assets/User/ProfilePage.js')

  .addEntry('media_library_package_detail_page', './assets/MediaLibrary/PackageDetailPage.js')

  .addEntry('studio_detail_page', './assets/Studio/StudioDetailPage.js')
  .addEntry('studios_page', './assets/Studio/StudiosPage.js')
  .addEntry('studio_create_page', './assets/Studio/CreatePage.js')
  .addEntry('studio_add_project_page', './assets/Studio/AddProject.js')

  .addStyleEntry('twig_bundle_error', './assets/bundles/TwigBundle/Exception/error.scss')

  .addEntry('user_follower_overview', './assets/User/FollowerOverview.js')

  // Admin interface
  .addEntry('admin_standard_layout', './assets/Admin/Layout.js')
  .addEntry('admin_user_communication_broadcast_notification', './assets/Admin/UserCommunication/BroadcastNotification.js')
  .addEntry('admin_user_communication_send_mail', './assets/Admin/UserCommunication/SendMail.js')
  .addEntry('admin_user_communication_maintenance_information_icon_list', './assets/Admin/UserCommunication/MaintenanceInformation/IconList.js')
  .addEntry('admin_system_management_logs', './assets/Admin/SystemManagement/Logs.js')
  .addEntry('admin_system_management_maintain', './assets/Admin/SystemManagement/Maintain.js')
  .addStyleEntry('admin_system_management_db_updater_achievements', './assets/User/Achievements.scss')
  .addEntry('admin_statistics_machine_translation', './assets/Admin/Statistics/MachineTranslation.js')

  // Themes
  .addStyleEntry('pocketcode', './assets/Theme/pocketcode.scss')
  .addStyleEntry('arduino', './assets/Theme/arduino.scss')
  .addStyleEntry('create@school', './assets/Theme/create@school.scss')
  .addStyleEntry('embroidery', './assets/Theme/embroidery.scss')
  .addStyleEntry('luna', './assets/Theme/luna.scss')
  .addStyleEntry('phirocode', './assets/Theme/phirocode.scss')
  .addStyleEntry('pocketalice', './assets/Theme/pocketalice.scss')
  .addStyleEntry('pocketgalaxy', './assets/Theme/pocketgalaxy.scss')
  .addStyleEntry('mindstorms', './assets/Theme/mindstorms.scss')

  // enables the Symfony UX Stimulus bridge (used in assets/js/bootstrap.js)
  .enableStimulusBridge('./assets/controllers.json')

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps()
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties')
  })

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage'
    config.corejs = 3
  })

  // enables Sass/SCSS support
  .enableSassLoader()

  // integrity="..." attributes on your script & link tags
  // .enableIntegrityHashes(Encore.isProduction())

  // Post CSS processing; E.g. auto vendor prefixing, px to rem, ...
  .enablePostCssLoader()

  /*
   * Plugins
   */
  .addPlugin(
    new PurgeCSSPlugin({
      paths: glob.sync([
        path.join(__dirname, 'templates/**/*.html.twig'),
        path.join(__dirname, 'assets/**/*.js'),
        path.join(__dirname, 'assets/**/*.svg'),
      ]),
      content: ['**/*.twig', '**/*.js'],
      safelist: {
        standard: [/^swal2/, /^modal/, /^mdc/],
      },
      defaultExtractor: (content) => {
        return content.match(/[\w-/:]+(?<!:)/g) || []
      },
    }),
  )

  .addPlugin(
    (() => {
      const log = (result) => {
        console.log('Loaded .env vars', result)
        return result
      }

      return new webpack.DefinePlugin(
        log(
          Encore.isProduction()
            ? [
              dotenv.config({ path: '.env', override: true }),
              dotenv.config({ path: '.env.prod', override: true }),
            ]
            : [
              dotenv.config({ path: '.env', override: true }),
              dotenv.config({ path: '.env.dev', override: true }),
            ],
        ),
      )
    })(),
  )

  .addPlugin(
    process.env.BUGSNAG_API_KEY
      ? new BugsnagSourceMapUploaderPlugin({
        apiKey: process.env.BUGSNAG_API_KEY,
        appVersion: process.env.APP_VERSION,
        overwrite: true,
      })
      : noop(),
  )

  .addPlugin(
    // Allow eslint to execute in the background during the webpack build process. No additional run necessary
    new ESLintPlugin({
      extensions: ['js', 'jsx'],
      emitWarning: !Encore.isProduction(),
      failOnError: Encore.isProduction(),
      files: 'assets/',
    }),
  )

module.exports = Encore.getWebpackConfig()
