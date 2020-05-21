// Adapted from:
// [1] "amazon-archives/ai-driven-social-media-dashboard", GitHub, 2020. [Online]. Available: https://github.com/amazon-archives/ai-driven-social-media-dashboard. [Accessed: 21- May- 2020].


var AWS = require('aws-sdk');
var config = require('./config');
var producer = require('./twitter_stream_producer');

var kinesis_firehose = new AWS.Firehose({apiVersion: '2015-08-04', region: config.region});
producer(kinesis_firehose).run();