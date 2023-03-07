const helper = require("./wp-scripts.helper");
const path = require("path");
module.exports = helper({
    "blocks": "./gutenberg/src/index.js"
}, {
    path: path.resolve(__dirname, "../assets"),
    filename: '[name].js'
}, process.env.NODE_ENV);