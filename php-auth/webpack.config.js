module.exports = {
    mode: process.env.NODE_ENV || "development",
    entry: ["./src/js/index.js"],
    output: {
        filename: "bundle.js",
        path: __dirname + "/public/dist"
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: ['style-loader', 'css-loader']
            }
        ]
    }
};