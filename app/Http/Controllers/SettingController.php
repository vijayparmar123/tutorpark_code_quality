<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Http\Resources\SettingResource;
use App\Http\Resources\RazorpaySettingResource;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $setting = Setting::first();
            return (new SettingResource($setting))->additional([ "error" => false, "message" => 'Here is all settings data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_point' => 'required|array',
            'student_point.*' => 'required|numeric',
            'tutor_point' => 'required|array',
            'tutor_point.*' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $setting = Setting::firstOrNew();
            $setting->student_point = $request->student_point;
            $setting->tutor_point = $request->tutor_point;
            $setting->save();

            $this->setResponse(false,'Settings updated successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function razorpayCredentialStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:live,test',
            'test_key_id' => 'required_if:mode,test',
            'test_secret' => 'required_if:mode,test',
            'live_key_id' => 'required_if:mode,live',
            'live_secret' => 'required_if:mode,live',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $setting = Setting::firstOrNew();
            $setting->update($request->all());

            $this->setResponse(false,'Settings updated successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function retriveRazorpyaSetting(Request $request)
    {
        try{
            $setting = Setting::first();
            return (new RazorpaySettingResource($setting))->additional([ "error" => false, "message" => 'Here is all settings data']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
