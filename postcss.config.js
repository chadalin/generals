export default {
    plugins: {
        'postcss-import': {},
        'postcss-nested': {},
        'postcss-preset-env': {
            features: { 'nesting-rules': false }
        },
        'autoprefixer': {},
    },
}