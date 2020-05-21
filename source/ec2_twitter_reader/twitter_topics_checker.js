var mysql = require('mysql');
var StockTopics = require('./stock_topics');

module.exports = class TwitterTopicsChecker {

    constructor(host, port, user, password, database, period=10000) {
        this._sql = 'SELECT stocks.symbol, topics.name FROM stocks INNER JOIN topics ON stocks.id=topics.stock_id';
        this._stocktopics = [];
        this._onUpdate = undefined;
        this._topicsChanged = false;
        this._period = period;
        this._isRunning = false;
        this._con = mysql.createConnection({
            host: host,
            port: port,
            user: user,
            password: password,
            database: database
          });
    }

    get stocktopics() {
        return this._stocktopics;
    }

    get isRunning() {
        return this._isRunning;
    }

    set onUpdate(callback) {
        this._onUpdate = callback;
    }

    startChecking() {

        this._con.connect(function(err) {
            if (err) {
                throw err;
            }
            console.log("StockTopicsChecker: Connected to database.");
        });

        // Start checking of stocktopics periodically.
        this._isRunning = true;
        this._update();
    }

    _checkStocktopics(callback) {
        var self = this;

        self._con.query(self._sql, function (err, result) {
            if (err) {
                throw err;
            }

            // Create the topics with their corresponding stock symbols.
            var newStocktopics = {};
            for(var i = 0; i<result.length; i++) {
                var symbol = result[i].symbol
                var topic = result[i].name;

                // Check if stock symbol is already included.
                if (!(symbol in newStocktopics)) {
                    newStocktopics[symbol] = new StockTopics(symbol);
                }

                newStocktopics[symbol].addTopic(topic);
            }

            // Check if something within the topic set has changed.
            self._topicsChanged = !self._equalSet(self._stocktopics, Object.values(newStocktopics));
            if (self._topicsChanged) {
                self._stocktopics = Object.values(newStocktopics);
            }

            // Notify update and set timeout.
            callback();
        });
    }

    _equalSet(topicsA, topicsB) {
        var as = new Set();
        var bs = new Set();

        // Check if any stocks added or removed.
        if (topicsA.length != topicsB.length) {
            return false;
        }

        // Create topic A sets.
        for(var i=0; i<topicsA.length; i++) {
            topicsA[i].topics.forEach(topic => as.add(topic));
        }

        // Create topic B sets.
        for(var i=0; i<topicsB.length; i++) {
            topicsB[i].topics.forEach(topic => bs.add(topic));
        }

        // Check if any topic added or removed.
        if (as.size !== bs.size) {
            return false;
        }

        // Check if the same content of the two sets.
        for (var a of as) 
        {
            if (!bs.has(a)) {
                return false;
            }
        }
        return true;
    }

    _update(){
        var self = this;

        // Check if stocktopics has changed and notify via callback.
        if (self._topicsChanged && self._onUpdate != undefined) {
            self._onUpdate(self._stocktopics);
            self._topicsChanged = false;
        }

        // Check the stocktopics periodically.
        setTimeout(function(){
            self._checkStocktopics(self._update.bind(self));
        }.bind(self), self._period);
    }
}