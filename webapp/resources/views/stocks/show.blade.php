@extends('layouts.app')

@section('content')
    <div class="container px-5">
        <div class="d-flex justify-content-between">
            <div>
                <h1 class="page-header" style="margin: 0 !important;">{{$data['quote']['name'] ?? 'Undefined'}} ({{$data['stock']->symbol}})</h1>
                <p style="font-size: 12px;">Currency in USD</p>
            </div>
            {!!Form::open(['action' => ['StocksController@destroy', $data['stock']->id], 'method' => 'POST'])!!}
                {{Form::hidden('_method', 'DELETE')}}
                {{Form::submit('Delete', ['class' => 'btn btn-danger'])}}
            {!!Form::close()!!}
        </div>

        <div>
            <p style="font-size: 12px; margin: 0 !important;">At close</p>
            <div class="d-flex">
                <div>
                    <span class="font-weight-bold" style="font-size: 24px;">{{$data['quote']['close']}}</span>
                    <span class="ml-1" style="font-size: 14px;">{{$data['quote']['change']}}</span>
                    <span style="font-size: 14px;">({{$data['quote']['percent_change']}}%)</span>
                </div>
                <div class="ml-4">
                    <span style="font-size: 24px;">{{$data['quote']['average_volume']}}</span>
                    <span style="font-size: 14px;">Volume</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="chart">
                </div>
            </div>
        </div>

        <div class="mt-4">

            <div class="d-flex justify-content-between">
                <div>
                    <span class="font-weight-bold" style="font-size: 24px;">{{$data['sentiment']['countTweets'] ?? '?'}}</span>
                    <span style="font-size: 14px;">Tweets</span>
                    <span class="ml-2" style="font-size: 14px;">(Last {{$data['tweet_period']}} days)</span>
                </div>
                <a class="btn btn-secondary" href="/stocks/{{$data['stock']->id}}/sentiment">
                    Details
                </a>
            </div>
        </div>

        <div class="row mt-3 mb-2">
            <div class="col-sm-6 col-md-3 col-lg-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <span style="font-size: 24px;">{{$data['sentiment']['averagePos'] ?? '?'}}%</span>
                        <span>Positive</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 col-lg-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <span style="font-size: 24px;">{{$data['sentiment']['averageNeg'] ?? '?'}}%</span>
                        <span>Negative</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 col-lg-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <span style="font-size: 24px;">{{$data['sentiment']['averageNeu'] ?? '?'}}%</span>
                        <span>Neutral</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 col-lg-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <span style="font-size: 24px">{{$data['sentiment']['averageMix'] ?? '?'}}%</span>
                        <span>Mixed</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('script')
    <script type="application/javascript" src="{{ asset('js/responsivefy.js') }}"></script>
    <script type="application/javascript" src="{{ asset('js/chart.js') }}"></script>
    <script type="application/javascript">
        margin = {top: 10, right: 50, bottom: 30, left: 20};
        width = 1000;
        height= 400;
        intraday = false;
        data = <?php echo $data['series']; ?>['values'];
        chart = new StockChart("chart", margin, width, height, intraday, data);
    </script>
@endsection