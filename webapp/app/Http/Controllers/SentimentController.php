<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Stock;
use App\Http\Helpers\Sentiment\AwsClient;
use App;

class SentimentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the applicaton sentiment details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $period = $user->tweet_period_days;
        $limit = $user->tweet_limit;
        
        $stock = Stock::find($id);

        $awsCredentials = new \Aws\Credentials\Credentials($_ENV['AWS_ACCESS_KEY'], 
                                                           $_ENV['AWS_SECRET_ACCESS_KEY'], 
                                                           $_ENV['AWS_SESSION_TOKEN']);
        
        $athena = new AwsClient($awsCredentials, $_ENV['AWS_GLUE_DB_NAME']);

        // Create sentiment data for each topic.
        $topics = array();
        foreach($stock->topics as $topic) {
            try {
                $sentiment = $athena->getDetailedSentiment($stock->symbol, array($topic->name), $period, $limit);
                $tweets = $athena->getLastTweets($stock->symbol, array($topic->name), $period);
                array_push($topics, array('name' => $topic->name,
                                          'sentiment' => $sentiment,
                                          'tweets' => $tweets));
            } catch (Exception $e) {
                redirect('');
            }
        }

        $data = array(
            'stock' => $stock,
            'topics' => $topics
        );

        return view('sentiment')->with('data', $data);
    }
}