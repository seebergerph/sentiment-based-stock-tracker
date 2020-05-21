<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class SettingsController extends Controller
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
     * Show the applicaton settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $period = $user->tweet_period_days;
        $limit = $user->tweet_limit;

        $config = array(
            'tweet_period' => $period,
            'tweet_limit' => $limit
        );

        return view('settings')->with('config', $config);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'tweet_period' => 'required|numeric|min:1',
            'tweet_limit' => 'required|numeric|min:100'
        ]);

        $user_id = auth()->user()->id;

        // Update user settings
        $user = User::find($user_id);
        $user->tweet_period_days = $request->input('tweet_period');
        $user->tweet_limit = $request->input('tweet_limit');
        $user->save();

        return redirect('dashboard');
    }
}