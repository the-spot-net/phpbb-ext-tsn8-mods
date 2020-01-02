const glob = require('glob');
const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Directory Variables
const directories = {
  dist: path.resolve(__dirname, '../styles/all/theme'),
  js_lint_exclusions: [
    path.resolve(__dirname, 'node_modules/')
  ],
  js_compile_exclusions: [
    // path.resolve(__dirname, 'node_modules/jquery')
  ],
  watchIgnore: [
    path.resolve(__dirname, 'node_modules/')
  ]
};

// File sets
const files = {
  tsn_scripts: [
    'jquery',
    // TODO Include any material scripts here
    ...glob.sync(path.resolve(__dirname, 'js/**/*.js'))
  ],

  tsn_theme: [
    path.resolve(__dirname, 'scss/style.scss')
  ]
};

module.exports = function (env = {}) {
  const { development } = env;
  const isDev = development === true;

  return {
    // Strip out the empty ones
    entry: [Object.keys(files).forEach((key) => (files[key].length === 0) && delete files[key]), files][1],
    mode: 'production',
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'sass-loader',
            {
              loader: 'sass-loader',
              options: {
                sassOptions: {
                  includePaths: glob.sync('node_modules').map((d) => path.join(__dirname, d))
                }
              }
            }
          ]
        },
        {
          test: /\.(jpg|gif|jpeg|png|woff(2)?|eot|ttf|svg)(\?[\w=.]+)?$/,
          loader: 'url-loader',
          options: {
            limit: 100000
          }
        },
        {
          // Lint-check the javascript
          test: /\.js$/,
          enforce: 'pre',
          loader: 'eslint-loader',
          exclude: directories.js_lint_exclusions,
          options: {
            emitWarning: true,
            fix: true
          }
        },
        {
          // Compile the javascript
          test: /\.js$/,
          loader: 'babel-loader',
          exclude: directories.js_compile_exclusions
        }
      ]
    },
    output: {
      path: directories.dist,
      filename: 'js/[name].js'
    },
    performance: { hints: false },
    plugins: [
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery'
      }),
      new MiniCssExtractPlugin({
        filename: 'css/[name].css'
      })
    ],
    stats: {
      all: false,
      modules: true,
      maxModules: 0,
      errors: true,
      warnings: true,
      moduleTrace: true,
      errorDetails: true,
      builtAt: true,
      timings: true
    },
    watch: isDev,
    watchOptions: {
      ignored: directories.watchIgnore
    }
  };
};
