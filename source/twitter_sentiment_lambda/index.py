# Adapted from:
# [1] "amazon-archives/ai-driven-social-media-dashboard", GitHub, 2020. [Online]. Available: https://github.com/amazon-archives/ai-driven-social-media-dashboard. [Accessed: 21- May- 2020].

import json
import boto3
import os

s3 = boto3.resource('s3')
comprehend = boto3.client('comprehend')
translate = boto3.client('translate')
firehose = boto3.client('firehose')

def lambda_handler(event, context):
    print(event)
    
    for record in event['Records']:
        s3_bucket = record['s3']['bucket']['name']
        s3_key = record['s3']['object']['key']
        
        obj = s3.Object(s3_bucket, s3_key)
        tweets_as_string = obj.get()['Body'].read().decode('utf-8') 
        
        tweets = tweets_as_string.split('\n')
        for tweet_string in tweets:
            
            if len(tweet_string) < 1:
                continue
            
            tweet = json.loads(tweet_string)
            
            if tweet['lang'] != 'en':
                response = translate.translate_text(
                    Text=tweet['text'],
                    SourceLanguageCode=tweet['lang'],
                    TargetLanguageCode='en')
                comprehend_text = response['TranslatedText']
            else:
                comprehend_text = tweet['text']
            
            sentiment_response = comprehend.detect_sentiment(
                    Text=comprehend_text,
                    LanguageCode='en'
                )
            print(sentiment_response)

            sentiment_record = {
                'tweetid': tweet['id'],
                'creationdate': tweet['created_at'],
                'timestamp_ms': tweet['timestamp_ms'],
                'stocksymbol': tweet['stock_symbol'],
                'stocktopic': tweet['stock_topic'],
                'text': comprehend_text,
                'originaltext': tweet['text'],
                'sentiment': sentiment_response['Sentiment'],
                'sentimentposscore': sentiment_response['SentimentScore']['Positive'],
                'sentimentnegscore': sentiment_response['SentimentScore']['Negative'],
                'sentimentneuscore': sentiment_response['SentimentScore']['Neutral'],
                'sentimentmixedscore': sentiment_response['SentimentScore']['Mixed']
            }
            
            response = firehose.put_record(
                DeliveryStreamName=os.environ['SENTIMENT_STREAM'],
                Record={
                    'Data': json.dumps(sentiment_record) + '\n'
                }
            )
            
    return 'true'