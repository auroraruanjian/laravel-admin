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

if( mix.inProduction()){
    mix.version([]);
    mix.sourceMaps();
}

mix.extract([
    'vue',
    'vuex',
    'vue-router',
    // 'vue-cookie',
    'axios',
    'nprogress',
    'screenfull',
],'vendor');

// mix.extract([
//     'element-ui'
// ],'element');

mix.js('resources/js/app.js','');

mix.styles([
    'node_modules/nprogress/nprogress.css',
    'node_modules/element-ui/lib/theme-chalk/index.css',
    'node_modules/element-ui/packages/theme-chalk/src/index',
    'node_modules/font-awesome/css/font-awesome.css',
    'node_modules/vue-particle-line/dist/vue-particle-line.css',
    // '../sass/index.scss',
], 'public/css/app.css');

mix.options({
    //extractVueStyles: true
});

const webpack_config = {
    externals: {
        vue: 'Vue',
        element: 'ElementUI',
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
            '/element': 'js/element.js'
        }),
        new BundleAnalyzerPlugin()
    ],
    optimization:{},
};

Mix.listen('configReady', (webpackConfig) => {
    // Create SVG sprites
    webpackConfig.module.rules.unshift({
        test: /\.svg$/,
        loader: 'svg-sprite-loader',
        include: [path.resolve(__dirname,'resources/js/icons/svg')],
        options: {
            symbolId: 'icon-[name]'
        }
    });

    // Exclude 'svg' folder from font loader
    let fontLoaderConfig1 = webpackConfig.module.rules.find(rule => String(rule.test) === String(/(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/));
    fontLoaderConfig1.exclude = path.resolve('resources/js/icons/svg');

    let fontLoaderConfig2 = webpackConfig.module.rules.find(rule => String(rule.test) === String(/(\.(woff2?|ttf|eot|otf)$|font.*\.svg$)/));
    fontLoaderConfig2.exclude = path.resolve('resources/js/icons/svg');///(resources\/js\/icons\/svg)/;
});

mix.copy('resources/css/weui.css', 'public/css/weui.css');
mix.copyDirectory('node_modules/babel-polyfill/dist/polyfill.min.js', 'public/js');
mix.copyDirectory('node_modules/element-ui/lib/theme-chalk/fonts', 'public/css/fonts');

mix.webpackConfig(webpack_config);
