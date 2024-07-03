const Encore = require('@symfony/webpack-encore')
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin')
const { BugsnagSourceMapUploaderPlugin } = require('webpack-bugsnag-plugins')
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
    // Bootstrap (deprecated!)
    { from: './node_modules/bootstrap', to: '../bootstrap/[path][name].[ext]' },

    // VIS (used in remix graph) (deprecated!)
    // We need the whole dist folder because the css references multiple images
    { from: './node_modules/vis/dist/', to: '../vis/[path][name].[ext]' },

    // Fonts (deprecated!)
    { from: './assets/fonts', to: '/fonts/[path][name].[ext]' },
    {
      from: './node_modules/material-icons/',
      to: '../material-icons/[path][name].[ext]',
    },

    // Images
    { from: './assets/images', to: '../images/[path][name].[ext]' },

    // Favicon
    { from: './assets/images', pattern: /favicon\.ico/, to: '../[name].[ext]' },

    // Catblocks
    { from: './assets/catblocks', to: '../catblocks/[path][name].[ext]' },

    // JS (deprecated!)
    { from: './assets/js/custom', to: '../js/[path][name].[ext]' },
    {
      from: './node_modules/clipboard/dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/bootstrap/dist/js',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/sweetalert2/dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/jquery/dist/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/textfilljs/dist/',
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
      from: './node_modules/lazysizes/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },
    {
      from: './node_modules/jwt-decode/build/',
      pattern: /\.js$/,
      to: '../js/modules/[path][name].[ext]',
    },

    // CSS (deprecated!)
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
  .addEntry('base', './assets/js/base.js')
  .addEntry('index', './assets/js/index.js')
  .addEntry('achievements_overview', './assets/js/achievements_overview.js')
  .addEntry('notifications_overview', './assets/js/notifications_overview.js')
  .addEntry('follower_overview', './assets/js/follower_overview.js')
  .addEntry('my_profile', './assets/js/my_profile.js')
  .addEntry('profile', './assets/js/profile.js')
  .addEntry('project_js', './assets/js/project.js')
  .addEntry('project_comments', './assets/js/custom/program_comments.js')
  .addEntry('search_old', './assets/js/search_old.js')
  .addEntry('search', './assets/js/search.js')
  .addEntry('media_library', './assets/js/media_library.js')
  .addEntry('login', './assets/js/login.js')
  .addEntry('register', './assets/js/register.js')
  .addEntry('request', './assets/js/request.js')
  .addEntry('reset', './assets/js/reset.js')
  .addEntry('check_email', './assets/js/check_email.js')
  .addEntry('studio_detail', './assets/js/studio_detail.js')
  .addEntry('studios_overview', './assets/js/studios_overview.js')
  .addEntry('studios_new', './assets/js/studio_new.js')
  .addEntry('studio_add_new_project', './assets/js/studio_add_new_project.js')
  .addEntry('code_view', './assets/js/code_view.js')
  .addEntry('medialib_content', './assets/js/medialib_content.js')
  .addEntry('language_menu', './assets/js/custom/languageMenu.js')

  // SCSS to CSS
  .addStyleEntry('achievements', './assets/styles/components/achievements.scss')
  .addStyleEntry('project_scss', './assets/styles/custom/program.scss')
  .addStyleEntry(
    'multi_column_article',
    './assets/styles/custom/multi_column_article.scss',
  )
  .addStyleEntry('old_code_view', './assets/styles/custom/old_code_view.scss')
  .addStyleEntry(
    'code_statistics',
    './assets/styles/components/code_statistics.scss',
  )

  .addStyleEntry(
    'maintenance_slider',
    './assets/styles/custom/maintenance_information_admin_slider.scss',
  )
  .addStyleEntry(
    'maintenance',
    './assets/styles/custom/maintenance_information_view.scss',
  )
  .addStyleEntry('project_list', './assets/styles/components/project_list.scss')
  .addStyleEntry('user_list', './assets/styles/components/user_list.scss')
  .addStyleEntry('profile_styles', './assets/styles/custom/profile.scss')
  .addStyleEntry('remixgraph', './assets/styles/custom/remixgraph.scss')
  .addStyleEntry('error', './assets/styles/error.scss')
  .addStyleEntry(
    'language_menu_styles',
    './assets/styles/custom/language_menu.scss',
  )

  // Themes
  .addStyleEntry('pocketcode', './assets/styles/themes/pocketcode.scss')
  .addStyleEntry('arduino', './assets/styles/themes/arduino.scss')
  .addStyleEntry('create@school', './assets/styles/themes/create@school.scss')
  .addStyleEntry('embroidery', './assets/styles/themes/embroidery.scss')
  .addStyleEntry('luna', './assets/styles/themes/luna.scss')
  .addStyleEntry('phirocode', './assets/styles/themes/phirocode.scss')
  .addStyleEntry('pocketalice', './assets/styles/themes/pocketalice.scss')
  .addStyleEntry('pocketgalaxy', './assets/styles/themes/pocketgalaxy.scss')
  .addStyleEntry('mindstorms', './assets/styles/themes/mindstorms.scss')

  // enables the Symfony UX Stimulus bridge (used in assets/js/bootstrap.js)
  .enableStimulusBridge('./assets/js/controllers.json')

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

  // uncomment if you're having problems with a jQuery plugin
  .autoProvidejQuery()

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

module.exports = Encore.getWebpackConfig()
