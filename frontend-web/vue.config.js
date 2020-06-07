const path = require('path');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer-sunburst').BundleAnalyzerPlugin;

module.exports = {
    productionSourceMap:false,

    devServer:{
        proxy: {
            '/api': {
                target: 'http://frontend-api.laravel_admin.me',
                ws: true,
                changeOrigin: true,
                pathRewrite: {'^/api' : ''}
            },
        }
    },

    pages: {
        index: {
            // page 的入口
            entry: 'src/main-web.js',
            // 模板来源
            template: 'public/index.html',
            // 在 dist/index.html 的输出
            filename: 'index.html',
            // 当使用 title 选项时，
            // template 中的 title 标签需要是 <title><%= htmlWebpackPlugin.options.title %></title>
            title: 'Index Page',
            // 在这个页面中包含的块，默认情况下会包含
            // 提取出来的通用 chunk 和 vendor chunk。
            //chunks: ['chunk-vendors', 'chunk-common', 'index']
        },
        index_m: {
            // page 的入口
            entry: 'src/main-h5.js',
            // 模板来源
            template: 'public/index.html',
            // 在 dist/index.html 的输出
            filename: 'index_m.html',
            // 当使用 title 选项时，
            // template 中的 title 标签需要是 <title><%= htmlWebpackPlugin.options.title %></title>
            title: 'M Index Page',
            // 在这个页面中包含的块，默认情况下会包含
            // 提取出来的通用 chunk 和 vendor chunk。
            //chunks: ['chunk-vendors', 'chunk-common', 'index-m']
        },
    },

    css:{
    },

    chainWebpack: config => {

    },

    configureWebpack: {
        plugins: [
            // new BundleAnalyzerPlugin({analyzerPort: 8889,})
        ]
    },

    lintOnSave: false
};
