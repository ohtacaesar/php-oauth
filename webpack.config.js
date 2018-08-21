const ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
  mode: process.env.NODE_ENV || "development",
  entry: ["./src/js/index.js"],
  output: {
    filename: "bundle.js",
    path: __dirname + "/public/dist"
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: ExtractTextPlugin.extract({use: 'css-loader'})
      }
    ]
  },
  plugins: [
    new ExtractTextPlugin('style.css')
  ]
};