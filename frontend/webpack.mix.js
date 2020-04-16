const mix = require('laravel-mix');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const ChunkRenamePlugin = require("webpack-chunk-rename-plugin");
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer-sunburst').BundleAnalyzerPlugin;

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js','')
    .js('resources/js/app-m.js','');

mix.sourceMaps();

if( mix.inProduction()){
    mix.version([]);
}

mix.extract([
    //'vue',
    'vuex',
    'vue-router',
    // 'element-ui',
    'axios'
],'vendor');

const webpack_config = {
    externals: {
        vue: 'Vue',
        element: 'ElementUI',
        vant: 'Vant',
    },
    resolve: {
        alias: {
            'res': path.resolve(__dirname, 'resources'),
            '@': path.resolve(__dirname, 'resources/js'),
        }
    },
    output: {
        filename: (chunkData) => {
            return 'js/' + chunkData.chunk.name.replace(/\//g, '') + '.js';
        },
        chunkFilename: 'js/[name].bundle.js?[chunkhash]',
    },
    optimization: {
        splitChunks: {
            //chunks: 'all'
        }
    },
    module: {
        rules: [
        ]
    },
    plugins: [
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: ['!favicon.ico','!index.php','!robots.txt','!.htaccess','*.js','*.css'],
            cleanAfterEveryBuildPatterns: ['!favicon.ico','!index.php','!robots.txt','!.htaccess','*.js','*.css'],
        }),
        new ChunkRenamePlugin({
            initialChunksWithEntry: true,
            '/vendor': 'js/vendor.js',
            '/vendor-m': 'js/vendor-m.js'
        }),
        //new BundleAnalyzerPlugin()
    ],
    optimization:[],
};

mix.webpackConfig(webpack_config);

mix.copyDirectory('node_modules/babel-polyfill/dist/polyfill.min.js', 'public/js');
mix.copyDirectory('node_modules/element-ui/lib/theme-chalk/fonts', 'public/css/fonts');

/**
 * WEB配置
 */
mix.styles([
    'resources/js/assets/css/reset.css',
    'resources/js/assets/css/animate.css',
    'node_modules/normalize.css/normalize.css',
    // 'node_modules/element-ui/lib/theme-chalk/index.css',
    'node_modules/nprogress/nprogress.css',
    'node_modules/font-awesome/css/font-awesome.css',
    'node_modules/element-ui/lib/theme-chalk/index.css',
    'node_modules/element-ui/packages/theme-chalk/src/index',
], 'public/css/vendor.css');

/**
 * 手机端配置
 */
// mix.styles([
//     'node_modules/vant/lib/index.css'
// ], 'public/m/css/vendor.css');
