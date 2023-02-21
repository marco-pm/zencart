const path = require('path');

module.exports = [
    {
        name: 'instant_search_dropdown',
        entry: path.resolve(__dirname, './src/instant_search/instant_search_dropdown.tsx'),
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './instant_search_dropdown.min.js',
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
            ],
        },
        resolve: {
            extensions: ['.tsx', '.ts', '.js', '.jsx']
        }
    },
    {
        name: 'instant_search_results',
        entry: path.resolve(__dirname, './src/instant_search/instant_search_results.tsx'),
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './instant_search_results.min.js',
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
            ],
        },
        resolve: {
            extensions: ['.tsx', '.ts', '.js', '.jsx']
        }
    },
    {
        name: 'typesense_dashboard',
        entry: path.resolve(__dirname, './src/typesense_dashboard/typesense_dashboard.tsx'),
        output: {
            path: path.resolve(__dirname, './zc_plugins/Typesense/v1.0.0/admin/'),
            filename: 'typesense_dashboard.min.js',
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
            ],
        },
        resolve: {
            extensions: ['.tsx', '.ts', '.js', '.jsx']
        }
    },
];