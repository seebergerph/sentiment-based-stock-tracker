<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Stock;
use App\Topic;
use App\Http\Helpers\Finance\TDClient;
use App\Http\Helpers\Finance\TDContext;
use App\Http\Helpers\Finance\Api\TDApi;
use App\Http\Helpers\Sentiment\AwsClient;
use App;

class StocksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect('dashboard');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $context = new TDContext($_ENV['TWELVE_API_KEY']);
        $td_client = new TDClient($context);
        $symbols = $td_client->references()->stocks();

        return view('stocks.create')->with('symbols', $symbols);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'symbol' => 'required',
            'topics' => 'required'
        ]);

        $user_id = auth()->user()->id;

        // Create Stock
        $stock = new Stock;
        $stock->symbol = $request->input('symbol');
        $stock->user_id = $user_id;
        $stock->save();
        
        // Create Topics
        foreach($request->input('topics') as $t) {
            $topic = new Topic;
            $topic->name = $t;
            $topic->stock_id = $stock->id;
            $topic->save();
        }

        return redirect('dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $period = $user->tweet_period_days;
        $limit = $user->tweet_limit;
        $stock = Stock::find($id);

        // Get and create stock data.
        $context = new TDContext($_ENV['TWELVE_API_KEY']);   
        $td_client = new TDClient($context);
        $symbol = $stock->symbol;
        $quote = $td_client->timeseries()->quote($symbol);
        $currentDate = date('Y-m-d');
        $date = \DateTime::createFromFormat('Y-m-d', $currentDate);
        $date->modify('-3 month');
        $series = json_encode($td_client->timeseries()->series($symbol, '1day', 
                                                               ["start_date" => $date->format('Y-m-d'),
                                                                "end_date" => $currentDate]));

        // Adapt values for visualisation.
        $quote['close'] = number_format((float)$quote['close'], 2, '.', '');
        $quote['change'] = number_format((float)$quote['change'], 2, '.', '');
        $quote['percent_change'] = number_format((float)$quote['percent_change'], 2, '.', '');

        // Create sentiment data for stock.
        $topics_plain = array();
        foreach($stock->topics as $topic) {
            array_push($topics_plain, $topic->name);
        }

        $awsCredentials = new \Aws\Credentials\Credentials($_ENV['AWS_ACCESS_KEY'], 
                                                           $_ENV['AWS_SECRET_ACCESS_KEY'], 
                                                           $_ENV['AWS_SESSION_TOKEN']);

        $athena = new AwsClient($awsCredentials, $_ENV['AWS_GLUE_DB_NAME']);

        try {
            $sentiment = $athena->getDetailedSentiment($symbol, $topics_plain, $period, $limit);
        } catch (Exception $e) {
            redirect('');
        }

        $data = array(
            'stock' => $stock,
            'quote' => $quote,
            'series' => $series,
            'sentiment' => $sentiment,
            'tweet_period' => $user->tweet_period_days
        );

        return view('stocks.show')->with('stock', $stock)->with('data', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('stocks.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Delete Stock
        $stock = Stock::find($id);
        $stock->topics()->delete();
        $stock->delete();
        
        return redirect('dashboard');
    }
}