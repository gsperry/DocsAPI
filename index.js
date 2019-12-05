/* global require, process */
let config = require("config");
let logger = require("./api/logger");

// Sometimes things to awry.
process.on("uncaughtException", function(err) {
    logger.emerg("uncaughtException:", err.message);
    logger.emerg(err.stack);
    process.exit(1);
});

// require middleware packages
let express = require("express");
let cookieParser = require("cookie-parser");
let bodyParser = require("body-parser");
let http = require("http");
let app = express();

// setup app server
app.set("port", config.get("api.port"));
app.use(cookieParser("the quick brown fox"));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({extended: false}));

// serve up rest API
let server = http.createServer(app);

server.listen(app.get("port"), function() {
    logger.info("Express server listening on port " + app.get("port"));
});

let dataApiType = config.get("api.dataApi");
let dataConfig = config.get("api.dataConfig");

let dataApi = require(dataApiType)(dataConfig, logger);
logger.info("Data API Type: " + dataApi.type);
logger.info("Data API Version: " + dataApi.version);

dataApi.init().then(function() {
    // Fusion is good to go.
    logger.info("Fusion is ready.");
}, function(err) {
    logger.error("Unable to check for active meeting.");
});

app.use("/", require("./api/rest-api")(logger, dataApi));
