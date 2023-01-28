const path = require('path');

module.exports = [
    {
        entry: path.resolve(__dirname, './src/instant_search/instant_search_dropdown.js'),
        name: 'instant_search_dropdown',
        mode: 'development',
        module: {
            rules: [
                {
                    test: /\.(js|jsx)$/,
                    use: ['babel-loader'],
                },
            ],
        },
        resolve: {
            extensions: ['*', '.js', '.jsx'],
        },
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './instant_search_dropdown.min.js',
        },
    },
    {
        entry: path.resolve(__dirname, './src/instant_search/instant_search_results.js'),
        name: 'instant_search_results',
        mode: 'development',
        module: {
            rules: [
                {
                    test: /\.(js|jsx)$/,
                    use: ['babel-loader'],
                },
            ],
        },
        resolve: {
            extensions: ['*', '.js', '.jsx'],
        },
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './instant_search_results.min.js',
        },
    },
    {
        entry: path.resolve(__dirname, './src/typesense_dashboard/typesense_dashboard.js'),
        name: 'typesense_dashboard',
        mode: 'development',
        module: {
            rules: [
                {
                    test: /\.(js|jsx)$/,
                    use: ['babel-loader'],
                },
            ],
        },
        resolve: {
            extensions: ['*', '.js', '.jsx'],
        },
        output: {
            path: path.resolve(__dirname, '.'),
            filename: './zc_plugins/InstantSearch/v3.0.1/admin/typesense_dashboard.min.js',
        },
    },
];
