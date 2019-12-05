/* global module,require*/
// This module is dependent on a valid data API.
let logger = null;
let dataApi = null;

let ip = function(req) {
    return req.headers["x-forwarded-for"] || req.connection.remoteAddress;
};

// eslint-disable-next-line new-cap
let router = require("express").Router();

// Setup REST handlers
// Get Search Results
let getSearchResults = function(req, res) {
    logger.info(ip(req) + " Retrieving documents from Fusion.");
    if(req.query.q) {
        dataApi.search(req.query.q, req.query.product, req.query.version).catch(function(err) {
            logger.error(ip(req) + " Error getting documents from Fusion.", err);
            res.status(500).send(err);
        }).then(function(data) {
            logger.debug("Result data", data);
            logger.info(ip(req) + " Documents retrieved successfully.");
            var response = {
                count: data.response.numFound,
                results: []
            };
            data.response.docs.forEach((doc) => {
                response.results.push({
                    url: doc.id,
                    title: doc.title,
                    product: doc.productName,
                    version: doc.productVersion,
                    exerpt: doc.body
                });
            });
            res.send(response);
        });
    } else {
        res.status(500).send("Invalid query.");
    }
};
router.get("/search", getSearchResults);

// Get Typeahead Results
let getTypeahead = function(req, res) {
    logger.info(ip(req) + " Retrieving results from Fusion.");
    dataApi.typeahead(req.query).then(function(data) {
        logger.debug("Result data", data);
        logger.info(ip(req) + " Results retrieved successfully.");
        res.send(data);
    }, function(err) {
        logger.error(ip(req) + " Error getting results from Fusion.", err);
        res.status(500).send(err);
    });
};
router.get("/typeahead", getTypeahead);

module.exports = function(log, db) {
    logger = log;
    dataApi = db;

    return router;
};
