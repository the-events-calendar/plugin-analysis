/**
 * The Laravel Mix configuration for the {{Plugin Starter}} MU plugin.
 *
 * @link https://laravel-mix.com
 */
 const mix = require('laravel-mix');
 const webpack = require('webpack');
 const banner = 'This file is part of the {{Plugin Starter}} MU plugin and was generated automatically';

 require('laravel-mix-eslint');

 // Customize Mix options.
 mix.setPublicPath(process.env.MIX_BUILD_DIR || 'plugin-starter')
     .options({
         manifest: false,
         terser: {
             extractComments: false,
             terserOptions: {
                 format: {
                     comments: false,
                     preamble: `/** ${ banner } */`,
                 },
             },
         },
         postCss: [
             require('postcss-preset-env'),
         ],

		 // Disable CSS rewrites.
		processCssUrls: false,
     });

 // Customize the Webpack configuration.
 mix.webpackConfig({
     plugins: [
         new webpack.BannerPlugin(banner),
     ],
     externals: {
         '@wordpress/element': 'wp.element',
         '@wordpress/components': 'wp.components',
         '@wordpress/hooks': 'wp.hooks',
         '@wordpress/i18n': 'wp.i18n',
     },
     resolve: {
         alias: {
             '@stellarwp': __dirname + '/plugin-starter/vendor/stellarwp/plugin-framework',
         },
     },
 });

 /**
  * Note: These may or may not be needed based on the plugin needs, but are here as an example.
  */

 // Bundle CSS.
 mix.css('resources/css/styles.css', 'assets/css')
     .sourceMaps(false);

 // Bundle JavaScript.
 mix.js('resources/js/scripts.js', 'assets/js')
     .sourceMaps(false)
     .eslint()
     .react();
