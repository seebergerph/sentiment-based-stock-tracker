@extends('layouts.app')

@section('content')
    <div class="stocksgrid">
        @if (count($data) > 0)
            @foreach($data as $item)
                <a class="link-card" href="stocks/{{$item['stock']->id}}">
                    <div class="card">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between">
                                <div class="font-weight-bold" style="font-size: 20px;">{{$item['stock']->symbol}}</div>
                                <div class="font-weight-bold" style="font-size: 20px;">${{$item['stockInfo']['price'] ?? '?'}}</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>{{$item['stockInfo']['name'] ?? 'Undefined'}}</div>
                                <div>(${{$item['stockInfo']['change']}} | {{$item['stockInfo']['changePercentage'] ?? '?'}}%)</div>
                            </div>
                        </div>
                        <div class="card-body" style="margin: 0; padding: 0;">
                            <div style="padding: 20px;">
                                <div class="d-flex justify-content-between">
                                    <div class="font-weight-bold" style="font-size: 16px;">Sentiment (pos.)</div>
                                    <div class="font-weight-bold" style="font-size: 16px;">Tweets</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div style="font-size: 16px;">{{$item['sentiment']['average'] ?? '?'}}%</div>
                                    <div style="font-size: 16px;">{{$item['sentiment']['countTweets'] ?? '?'}}</div>
                                </div>
                            </div>
                            <div class="progress mt-3 mb-1" style="margin-top: 14px !important; margin-left: 4px !important; margin-right: 4px !important;">
                                <div class="progress-bar bg-success" 
                                    role="progressbar" 
                                    style="width: {{$item['sentiment']['average'] ?? 0}}%; height: 30px;" 
                                    aria-valuenow="{{$item['sentiment']['average'] ?? 0}}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        @else
            You have no tracked stocks
        @endif      
    </div>
@endsection
