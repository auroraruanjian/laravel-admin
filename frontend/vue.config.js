const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
    productionSourceMap:false,
    devServer:{
        //watchContentBase: true,
        watchOptions: {
            poll: true
        },
        writeToDisk: true,
        disableHostCheck:true
    },
    outputDir:'public',
    pages:{
        index:{
            entry:'resources/assets/main.js',
            template: 'resources/template/index.html',
            filename: (process.env.NODE_ENV === 'production') ?'../resources/views/index.blade.php':'../public/index.html',
        }
    },
    chainWebpack: config => {
        //别名
        config.resolve.alias
            .set('res', path.resolve(__dirname, 'resources'))
            .set('@', path.resolve(__dirname, 'resources/assets/'));

        config.plugins.delete('copy');

        config
            .plugin('clean')
            .use(CleanWebpackPlugin, [{
                cleanOnceBeforeBuildPatterns: ['!favicon.ico','!index.php','!robots.txt','!.htaccess','*.json','*.html','css/','js/','img/'],
            }])
    },
    configureWebpack: {
        plugins: [
        ]
    }
};
