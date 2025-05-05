const mix = require('laravel-mix');
const webpack = require('webpack');

mix.js('resources/js/app.js', 'public/js')
    .webpackConfig({
        plugins: [
            new webpack.DefinePlugin({
                'process.env': {
                    VITE_REVERB_APP_KEY: JSON.stringify(process.env.VITE_REVERB_APP_KEY),
                    VITE_REVERB_HOST: JSON.stringify(process.env.VITE_REVERB_HOST),
                    VITE_REVERB_PORT: JSON.stringify(process.env.VITE_REVERB_PORT),
                }
            })
        ]
    });
