const mix = require('laravel-mix');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const ChunkRenamePlugin = require("webpack-chunk-rename-plugin");

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
    'vue',
    'vuex',
    'vue-router',
    'element-ui',
    'axios'
],'vendor');

const webpack_config = {
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
            '/vendor': 'js/vendor.js'
        }),
    ],
    optimization:[],
};

mix.webpackConfig(webpack_config);

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
], 'public/css/vendor.css');

/**
 * 手机端配置
 */
mix.styles([
    // // onsenui自带的无字体核心css文件（没有捆绑字体）
    // 'node_modules/onsenui/css/onsenui-core.css',
    //
    // // 手动增加字体包
    // 'node_modules/onsenui/css/ionicons/css/ionicons.css',
    'vant/lib/index.css'

], 'public/m/css/vendor.css');
