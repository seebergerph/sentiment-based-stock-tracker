@extends('layouts.app')

@section('content')
<div class="container px-5">
    {!! Form::open(['action' => 'SettingsController@update', 'method' => 'POST']) !!}
        <div class="d-flex justify-content-between">
            <h1 class="page-header">Settings</h1> 
            <button type="submit" class="btn btn-success">
                Save
            </button>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-6">
                    {{Form::label('tweet_period', 'Period (days)')}}
                    {{Form::input('number', 'tweet_period', $config['tweet_period'], ['class' => 'form-control', 
                                                                                      'min' => 1])}}
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-6">
                    {{Form::label('tweet_limit', 'Tweet Limit (amount)')}}
                    {{Form::input('number', 'tweet_limit', $config['tweet_limit'], ['class' => 'form-control', 
                                                                                    'min' => 100])}}
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</div>
@endsection