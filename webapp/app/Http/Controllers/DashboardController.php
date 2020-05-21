<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Stock;
use App\Http\Helpers\Finance\TDClient;
use App\Http\Helpers\Finance\TDContext;
use App\Http\Helpers\Finance\Api\TDApi;
use App\Http\Helpers\Sentiment\AwsClient;
use App;

class DashboardController extends Controller
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
     * Show the applicaton dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $period = $user->tweet_period_days;
        $limit = $user->tweet_limit;
        $stocks = $user->stocks;

        $context = new TDContext($_ENV['TWELVE_API_KEY']);
        $td_client = new TDClient($context);

        $awsCredentials = new \Aws\Credentials\Credentials($_ENV['AWS_ACCESS_KEY'], 
                                                           $_ENV['AWS_SECRET_ACCESS_KEY'], 
                                                           $_ENV['AWS_SESSION_TOKEN']);
    
        $athena = new AwsClient($awsCredentials, $_ENV['AWS_GLUE_DB_NAME']);

        // Create stock data for each dashboard tile.
        $data = array();
        foreach($stocks as $stock) {
            $quote = $td_client->timeseries()->quote($stock->symbol);

            $topics_plain = array();
            foreach($stock->topics as $topic) {
                array_push($topics_plain, $topic->name);
            }

            if(isset($quote['name']))
            {
                $name = $quote['name'];
            }
            else
            {
                $name = 'Undefined';
            }

            $price = number_format((float)$quote['close'], 2, '.', '');
            $change = number_format((float)$quote['change'], 2, '.', '');
            $changePercentage = number_format((float)$quote['percent_change'], 2, '.', '');

            try {
                $sentiment = $athena->getAverageSentiment($stock->symbol, $topics_plain, $period, $limit);
            } catch (Exception $e) {
                redirect('');
            }

            $info = array(
                'name' => $name,
                'price' => $price,
                'change' => $change,
                'changePercentage' => $changePercentage
            );
            array_push($data, array('stock' => $stock, 'stockInfo' => $info, 'sentiment' => $sentiment));
        }

        return view('dashboard')->with('data', $data);
    }
}
