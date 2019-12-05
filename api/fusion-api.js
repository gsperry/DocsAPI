/* global module,require*/

let logger = null;
let config = null;
var client = null;

/**
 * verify connection to Fusion
 * @return {Promise}
 */
function init() {
    return new Promise(function(fulfill, reject) {
        // eslint-disable-next-line new-cap
        client = require("node-rest-client-promise").Client(config.clientConfig);
        fulfill();
    });
}

/**
 * Run query against Fusion.
 * @param {String} query
 * @param {String} productName
 * @param {String} productVersion
 * @return {Promise}
 */
function search(query, productName, productVersion) {
    return new Promise(function(fulfill, reject) {
        // Base URL
        let url = config.url + "/api/apps/"+ config.app + "/query/" + config.queryProfile;
        // Add query
        url += "?q=" + encodeURIComponent(query);
        // Add product if necessary
        if(productName) {
            url += "&fq=productName:" + encodeURIComponent(productName);
        }
        // Add version if necessary
        if(productVersion) {
            url += "&fq=productVersion:" + encodeURIComponent(productVersion);
        }
        client.getPromise(url).catch(function(err) {
            reject(err);
        }).then(function(data) {
            // parsed response body as js object
            if(data) {
                if(data.data.highlighting) {
                    data.data.response.docs.forEach((doc) => {
                        if(data.data.highlighting[doc.id]) {
                            doc.body = data.data.highlighting[doc.id].body;
                        }
                    });
                }
                logger.debug(data);
                fulfill(data.data);
            }
        });
    });
}

/**
 * Run typeahead query against Fusion.
 * @param {String} query
 * @return {Promise}
 */
function typeahead(query) {
    return new Promise(function(fulfill, reject) {
        client.search(query, function(err, obj) {
            if(err) {
                logger.error(err);
                reject(err);
            } else {
                logger.info(obj.response.docs);
                fulfill(obj.response.docs);
            }
        });
    });
}

module.exports = function(cfg, log) {
    logger = log;
    config = cfg;

    return {
        version: "1.0",
        type: "Lucidworks Fusion",
        init: init,
        search: search,
        typeahead: typeahead
    };
};
