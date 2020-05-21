@extends('layouts.app')

@section('content')
    <div class="container px-5">
        <div class="d-flex justify-content-between">
        <h1 class="page-header">Sentiment Details ({{$data['stock']->symbol ?? '?'}})</h1> 
            <a class="btn btn-secondary" href="/stocks/{{$data['stock']->id}}">
                Back
            </a>
        </div>

        @if (count($data) > 0)
            @foreach($data['topics'] as $item)
                <div class="mt-2">
                    <span class="font-weight-bold" style="font-size: 24px;">{{$item['name'] ?? '?'}}</span>
                    <span class="ml-2" style="font-size: 14px;">({{$item['sentiment']['countTweets'] ?? '?'}} Tweets)</span>
                </div>

                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{$item['sentiment']['averagePos'] ?? 0}}%;" aria-valuenow="{{$item['sentiment']['averagePos'] ?? 0}}" aria-valuemin="0" aria-valuemax="100">
                        <span style="font-size: 16px;">{{$item['sentiment']['averagePos'] ?? 0}}%</span>
                    </div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$item['sentiment']['averageNeg'] ?? 0}}%;" aria-valuenow="{{$item['sentiment']['averageNeg'] ?? 0}}" aria-valuemin="0" aria-valuemax="100">
                        <span style="font-size: 16px;">{{$item['sentiment']['averageNeg'] ?? 0}}%</span>
                    </div>
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{$item['sentiment']['averageNeu'] ?? 0}}%;" aria-valuenow="{{$item['sentiment']['averageNeu'] ?? 0}}" aria-valuemin="0" aria-valuemax="100">
                        <span style="font-size: 16px;">{{$item['sentiment']['averageNeu'] ?? 0}}%</span>
                    </div>
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{$item['sentiment']['averageMix'] ?? 0}}%;" aria-valuenow="{{$item['sentiment']['averageMix'] ?? 0}}" aria-valuemin="0" aria-valuemax="100">
                        <span style="font-size: 16px;">{{$item['sentiment']['averageMix'] ?? 0}}%</span>
                    </div>
                </div>

                @if (count($item['tweets']) > 0)
                @foreach($item['tweets'] as $tweet)
                    @if ($tweet['sentiment'] == 'POSITIVE')
                        <div class="card mt-1 border-success border-top-0 border-right-0 border-bottom-0" style="border-width: 5px !important;"> 
                            <div class="card-body">
                                {{$tweet['text']}}
                            </div>
                        </div>
                    @endif
                    @if ($tweet['sentiment'] == 'NEGATIVE')
                        <div class="card mt-1 border-danger border-top-0 border-right-0 border-bottom-0" style="border-width: 5px !important;"> 
                            <div class="card-body">
                                {{$tweet['text']}}
                            </div>
                        </div>
                    @endif
                @endforeach
                @else
                    No tweets available.
                @endif
            @endforeach
        @else
            You have no tracked topics for this stock
        @endif      

    </div>
@endsection