const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const path = require('path');

const outputPath = '_dist';

const localDomain = 'http://flexiclasses.test/';
const entryPoints = {
  'bogopx-public': './custom/bogopx-public.js',
  'bogopx-admin': './custom/bogopx-admin.js',
  'bogopx-editor': './custom/bogopx-editor.js',
};

module.exports = {
  entry: entryPoints,
  output: {
    publicPath: '',
    path: path.resolve(__dirname, outputPath),
    filename: '[name].js',
  },
  plugins: [
    new BrowserSyncPlugin({
      proxy: localDomain,
      files: [`${outputPath}/*.css`],
      injectCss: true,
    }, { reload: false }),

    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),

    new DependencyExtractionWebpackPlugin({
      injectPolyfill: true,
    }),
  ],
  module: {
    rules: [
      {
        test: /\.s?[c]ss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader',
        ],
      },
      {
        test: /\.sass$/i,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              sassOptions: { indentedSyntax: true },
            },
          },
        ],
      },
      {
        test: /\.(jpg|jpeg|png|gif|woff|woff2|eot|ttf)$/i,
        use: 'url-loader?limit=1024',
      },
      {
        test: /\.(svg|svgz)(\?.+)?$/,
        oneOf: [
          {
            loader: 'url-loader',
            options: {
              limit: 2048,
              name: 'svg/[name].[ext]',
            },
          },
        ],
      },
      {
        test: /\.jsx$/i,
        use: [
          require.resolve('thread-loader'),
          {
            loader: require.resolve('babel-loader'),
            options: {
              cacheDirectory: process.env.BABEL_CACHE_DIRECTORY || true,
              babelrc: false,
              configFile: false,
              presets: [
                require.resolve('@wordpress/babel-preset-default'),
              ],
            },
          },
        ],
      },
    ],
  },
  resolve: {
    alias: {
      '@lib': path.resolve(__dirname, '_lib/'),
    },
  },
};
