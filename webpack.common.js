const path = require('path');
const webpack = require('webpack');
const {WebpackManifestPlugin} = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const {VueLoaderPlugin} = require('vue-loader');

module.exports = {
  module: {
    rules: [
      {
        test: /\.ts(x?)$/,
        exclude: /node_modules\/(?!@riddled)/,
        use: [
          {
            loader: 'babel-loader',
          },
          {
            loader: 'ts-loader',
            options: {
              appendTsSuffixTo: [/\.vue$/],
              transpileOnly: true,
            },
          },
        ],
      },
      {
        test: /\.svg$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[hash].[ext]',
            },
          },
        ],
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
            ts: 'babel-loader!ts-loader',
          },
        },
      },
      {
        test: /\.(sass|scss|css)$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: true,
              url: false,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: {
                  "@tailwindcss/postcss": {},
                },
              },
            },
          },
          'sass-loader',
        ],
      },
      {
        test: /\.js$/,
        exclude: /node_modules\/(?!@riddled)/,
        use: {
          loader: 'babel-loader',
          options: {
            cacheDirectory: true,
          },
        },
      },
    ],
  },
  output: {
    path: path.join(__dirname, 'public'),
    filename: 'js/[name]-[contenthash].js',
    chunkFilename: 'js/[name]-[contenthash].js',
    publicPath: '/',
  },
  optimization: {
    runtimeChunk: "single",
    splitChunks: {
      cacheGroups: {
        // split async vendor modules to async chunks
        async: {
          test: /[\\/]node_modules[\\/]/,
          chunks: "async",
          minChunks: 1,
          minSize: 20000,
          priority: 2,
        },
        // all vendors (except async) go to vendor.js
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendor",
          chunks: "all",
          priority: 1,
        },
        // all common code across entry points
        common: {
          test: /\.(s?js|vue|ts)$/,
          minChunks: 2,
          name: "common",
          chunks: "all",
          priority: 0,
          enforce: true,
        },
        default: false, // overwrite default settings
      },
    },
  },
  resolve: {
    mainFields: ['main', 'module'],
    extensions: ['.ts', '.js'],
    alias: {
      vue: 'vue/dist/vue.esm-bundler',
    },
  },
  context: path.join(__dirname, 'resources'),
  entry: {
    stylesEager: './sass/core.scss',
    stylesLazyApp: './sass/app.scss',
    stylesLazyAdm: './sass/pages/adm.scss',
    stylesTailwind: './sass/tailwind.css',
    app: './js/app.js',
    legacy: './js/legacy.js',
    forum: './js/pages/forum.ts',
    chat: './js2/chat/chat.ts',
    wiki: './js/pages/wiki.js',
    job: './js/pages/job.js',
  },
  plugins: [
    new VueLoaderPlugin(),
    new webpack.DefinePlugin({
      __VUE_OPTIONS_API__: true, // notifications library relies on optionsApi
      __VUE_PROD_DEVTOOLS__: false,
      __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    }),
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ['js/*.*', 'css/*.*', '!*/.gitignore'],
    }),
    // @see https://webpack.js.org/guides/caching/#module-identifiers
    new webpack.ids.HashedModuleIdsPlugin(),

    new MiniCssExtractPlugin({
      filename: "css/[name]-[contenthash].css",
    }),

    new WebpackManifestPlugin({
      fileName: 'manifest.json',
    }),
  ],
};
