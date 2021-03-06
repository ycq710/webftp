var webpack = require('webpack');
var version = require('./package.json').version;
const path = require('path')

module.exports = {
    entry: {
        'dialog': './dialog.js',
        'dialog-plus':'./dialog-plus.js'
    },
    output: {
        path:path.resolve(__dirname, './dist'),
        filename: '[name].js',
        library: `dialog`,
        libraryTarget: 'umd'
    },
    plugins: [
        new webpack.BannerPlugin('art-dialog@' + version + ' | https://github.com/aui/artDialog')
    ],
    externals: {
        jquery: 'jQuery'
    },
    module: {
        loaders: [
            { test: /\.css$/, loader: 'style-loader!css-loader' }
        ]
    }
};