var StockTopics = require('./stock_topics')
module.exports = class DynamicTwitterStream {

    constructor(twit, checker, firehose, config) {
        this._twit = twit;
        this._checker = checker;
        this._firehose = firehose;
        this._config = config;
        this._checker.onUpdate = this._updateStream.bind(this);
        this._stream;
        this._stocktopics = [];
    }

    startStream() {
        if (!this._checker.isRunning) {
            this._checker.startChecking();
        }
        
        var topics = [];
        for(var i=0; i<this._stocktopics.length; i++) {
            topics = topics.concat(Array.from(this._stocktopics[i].topics));
        }

        if (topics.length > 0) {
            this._stream = this._twit.stream('statuses/filter', 
            { track: topics, language: this._config.languages });
        
            console.log('DynamicTwitterStream: Start streaming...');

            var recordParams = {};
            this._stream.on('tweet', function (tweet) {

                var context = this._searchStocktopics(tweet);

                if(context != -1) {

                    // Set stock context to corresponding tweet.
                    tweet['stock_symbol'] = context.symbol;
                    tweet['stock_topic'] = context.topic;

                    var tweetString = JSON.stringify(tweet);
                    recordParams = {
                        DeliveryStreamName: this._config.kinesis_delivery,
                        Record: {
                            Data: tweetString +'\n'
                        }
                    };
                    this._firehose.putRecord(recordParams, function(err, data) {
                        if (err) {
                            console.log(err);
                        }
                    });

                }

            }.bind(this));
        }
        else {
            console.log('DynamicTwitterStream: Waiting for stock topics...')
        }
    }

    stopStream() {
        console.log("DynamicTwitterStream: Stop streaming...");

        if (this._stream != undefined) {
            this._stream.stop();
        }
    }

    _updateStream(stocktopics) {
        this.stopStream();

        console.log('DynamicTwitterStream: Update stream with new topics.');
        this._stocktopics = stocktopics;

        this.startStream();
    }

    _searchStocktopics(tweet) {
        var stocktopics = this._stocktopics;
        for(var i = 0; i < stocktopics.length; i++) {
            var topics = Array.from(stocktopics[i].topics);
            for(var j = 0; j < topics.length; j++) {
                var topic = topics[j];
                if(tweet.text.toLowerCase().search(topic.toLowerCase()) != -1) {
                    return {'symbol': stocktopics[i].symbol,
                            'topic': topic};
                }
            }
        }

        return -1;
    }
}