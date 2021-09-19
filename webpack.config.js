const Encore = require('@symfony/webpack-encore')
const PurgeCssPlugin = require('purgecss-webpack-plugin')
const glob = require('glob-all')
const path = require('path')

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  .configureFilenames({
    css: 'css/[name].css', // -[contenthash] to be used once styles are only imported!
    js: 'js/[name]-[chunkhash].js'

  })
  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()
  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()
  // emptying the build/ directory each time we build
  .cleanupOutputBeforeBuild()

  .copyFiles([
    // Bootstrap
    { from: './node_modules/bootstrap', to: '../bootstrap/[path][name].[ext]' },

    // VIS (used in remix graph)
    // We need the whole dist folder because the css references multiple images
    { from: './node_modules/vis/dist/', to: '../vis/[path][name].[ext]' },

    // Material
    { from: './node_modules/@material/', to: '../@material/[path][name].[ext]' },

    // Fonts
    { from: './assets/fonts', to: '/fonts/[path][name].[ext]' },
    { from: './node_modules/@fortawesome/', to: '../@fortawesome/[path][name].[ext]' },
    { from: './node_modules/@fortawesome/fontawesome-free/webfonts', to: '../webfonts/[path][name].[ext]' },
    { from: './node_modules/material-icons/', to: '../material-icons/[path][name].[ext]' },

    // Images
    { from: './assets/images', to: '../images/[path][name].[ext]' },

    // Favicon
    { from: './assets/images', pattern: /favicon\.ico/, to: '../[name].[ext]' },

    // Catblocks
    { from: './assets/catblocks', to: '../catblocks/[path][name].[ext]' },

    // JS
    { from: './assets/js/custom', to: '../js/[path][name].[ext]' }, // Deprecated!
    { from: './assets/js/analytics', to: '../js/[path][name].[ext]' },
    { from: './node_modules/clipboard/dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/bootstrap/dist/js', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/sweetalert2/dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/jquery/dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/textfilljs/dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/jquery-ui-dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/jquery-contextmenu/dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/jquery-contextmenu/dist/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },
    { from: './node_modules/lazysizes/', pattern: /\.js$/, to: '../js/modules/[path][name].[ext]' },

    // CSS
    { from: './node_modules/jquery-contextmenu/dist/', pattern: /\.css$/, to: '../css/modules/[path][name].[ext]' },
    { from: './node_modules/animate.css/', pattern: /\.css$/, to: '../css/modules/[path][name].[ext]' }
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
  .addEntry('sidebar', './assets/js/sidebar.js')
  .addEntry('login', './assets/js/login.js')
  .addEntry('register', './assets/js/register.js')
  .addEntry('request', './assets/js/request.js')
  .addEntry('reset', './assets/js/reset.js')
  .addEntry('program_comments', './assets/js/program_comments.js')
  .addEntry('program_description', './assets/js/program_description.js')
  .addEntry('report', './assets/js/report.js')
  .addEntry('footer', './assets/js/footer.js')
  .addEntry('profile', './assets/js/profile.js')
  .addEntry('achievements_overview', './assets/js/achievements_overview.js')

  // SCSS to CSS
  // toDo: replace with imports: e.g.: "import './styles/app.css'" ;
  .addEntry('achievements', './assets/styles/custom/achievements.scss')
  .addEntry('program', './assets/styles/custom/program.scss')
  .addEntry('project_list', './assets/styles/custom/project_list.scss')
  .addEntry('index', './assets/styles/custom/index.scss')
  .addEntry('medialib', './assets/styles/custom/medialib.scss')
  .addEntry('notifications', './assets/styles/custom/notifications.scss')
  .addEntry('multi_column_article', './assets/styles/custom/multi_column_article.scss')
  .addEntry('old_code_view', './assets/styles/custom/old_code_view.scss')
  .addEntry('modal', './assets/styles/custom/modal.scss')
  .addEntry('card', './assets/styles/custom/card.scss')
  .addEntry('profile_styles', './assets/styles/custom/profile.scss')
  .addEntry('remixgraph', './assets/styles/custom/remixgraph.scss')
  .addEntry('search', './assets/styles/custom/search.scss')
  .addEntry('studio', './assets/styles/custom/studio.scss')
  .addEntry('login_styles', './assets/styles/custom/login.scss')
  // Themes
  .addEntry('pocketcode', './assets/styles/themes/pocketcode.scss')
  .addEntry('arduino', './assets/styles/themes/arduino.scss')
  .addEntry('create@school', './assets/styles/themes/create@school.scss')
  .addEntry('embroidery', './assets/styles/themes/embroidery.scss')
  .addEntry('luna', './assets/styles/themes/luna.scss')
  .addEntry('phirocode', './assets/styles/themes/phirocode.scss')
  .addEntry('pocketalice', './assets/styles/themes/pocketalice.scss')
  .addEntry('pocketgalaxy', './assets/styles/themes/pocketgalaxy.scss')
  .addEntry('mindstorms', './assets/styles/themes/mindstorms.scss')

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())

  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage'
    config.corejs = 3
  })

  // enables Sass/SCSS support
  .enableSassLoader()

  // integrity="..." attributes on your script & link tags
  .enableIntegrityHashes(Encore.isProduction())

  // uncomment if you're having problems with a jQuery plugin
  .autoProvidejQuery()

  /*
   * Plugins
   */
  // .addPlugin(new PurgeCssPlugin({
  //   paths: glob.sync([
  //     path.join(__dirname, 'templates/**/*.html.twig'),
  //     path.join(__dirname, 'assets/**/*.js')
  //   ]),
  //   content: ['**/*.twig', '**/*.js'],
  //   defaultExtractor: (content) => {
  //     return content.match(/[\w-/:]+(?<!:)/g) || []
  //   }
  // }))

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

module.exports = Encore.getWebpackConfig()
