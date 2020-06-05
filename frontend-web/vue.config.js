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
