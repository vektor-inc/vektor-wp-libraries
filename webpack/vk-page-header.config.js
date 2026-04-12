const path = require('path');

module.exports = {
  mode: 'production',
  entry: [
    path.resolve(__dirname, '../vk-page-header/src/page-header-panel.js'),
  ],
  output: {
    path: path.resolve(__dirname, '../vk-page-header/package/js'),
    filename: 'vk-page-header-panel.min.js'
  },
  externals: {
    '@wordpress/plugins': 'wp.plugins',
    '@wordpress/edit-post': 'wp.editPost',
    '@wordpress/element': 'wp.element',
    '@wordpress/components': 'wp.components',
    '@wordpress/data': 'wp.data',
    '@wordpress/core-data': 'wp.coreData',
    '@wordpress/block-editor': 'wp.blockEditor',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: [
                '@babel/preset-env'
              ]
            }
          }
        ],
        exclude: /node_modules/,
      }
    ]
  }
};
