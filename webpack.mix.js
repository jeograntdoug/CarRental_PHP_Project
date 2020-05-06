const mix = require('laravel-mix')
const path = require('path')
const tailwindcss = require('tailwindcss')

/**
 * Mix Asset Manager
 * -----------------
 * Laravel Mix is a wrapper around webpack for easy hook into
 * the webpack build steps/life sycle
 */

 mix.sass('resources/sass/app.scss', 'resources/styles')
 .options({
     processCssUrls: false,
     postCss: [ tailwindcss(\'./tailwind.config.js')]
 })

 // Compile : 
 // node_modules/.bin/webpack --config=node_modules/laravel-mix/setup/webpack.config.js