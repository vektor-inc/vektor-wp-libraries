const path = require('path');

module.exports = {
  mode: 'production',
  entry: [
    path.resolve(__dirname, '../vk-mobile-nav/package/js/vk-mobile-nav.js'),
  ],
  output: {
    path: path.resolve(__dirname, '../vk-mobile-nav/package/js'),
    filename: 'vk-mobile-nav.min.js'
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
