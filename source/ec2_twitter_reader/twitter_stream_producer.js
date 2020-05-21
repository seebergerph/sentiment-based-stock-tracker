// Adapted from:
// [1] "amazon-archives/ai-driven-social-media-dashboard", GitHub, 2020. [Online]. Available: https://github.com/amazon-archives/ai-driven-social-media-dashboard. [Accessed: 21- May- 2020].

var config = require('./config');
var twitter_config = require('./twitter_reader_config.js');
var util = require('util');
var Twit = require('twit');
var DynamicTwitterStream = require('./dynamic_twitter_stream')
var TwitterTopicsChecker = require('./twitter_topics_checker')


function twitterStreamProducer(firehose) {
    var T = new Twit(twitter_config.twitter);
    var checker = new TwitterTopicsChecker(twitter_config.database.host, 
                                           twitter_config.database.port, 
                                           twitter_config.database.user, 
                                           twitter_config.database.password,
                                           twitter_config.database.name);
    var streamer = new DynamicTwitterStream(T, checker, firehose, twitter_config);
    var waitBetweenPutRecordsCallsInMilliseconds = config.waitBetweenPutRecordsCallsInMilliseconds;

    function _sendToFirehose() {
        streamer.startStream();
    }

    return {
        run: function() {
          console.log(util.format('Configured wait between consecutive PutRecords call in milliseconds: %d',
              waitBetweenPutRecordsCallsInMilliseconds));
            _sendToFirehose();
          }
      }
}

module.exports = twitterStreamProducer;