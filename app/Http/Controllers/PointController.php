<?php

namespace App\Http\Controllers;

use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PointResource;
use App\Jobs\PostPayment;


class PointCOntroller extends Controller
{
    /**
     * Transfer points to other user
     * 
     * @return void
     */
    public function history()
    {
        try {
            $point = Point::where(['user_id'=>auth()->id()])->first();
            // dd($point);
            if($point)
            {
                return (new PointResource($point))->additional([ "error" => false, "message" => 'Here is all point history data.']);
            }else{
                $this->_response['data'] = array();
                $this->setResponse(false,'Here is all point history data.');
                return response()->json($this->_response);
            }
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Transfer points to other user
     * 
     * @return void
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_email' => 'required|email|exists:users,email',
            'points' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            if(auth()->user()->balance() < $request->points )
            {
                $this->setResponse(false, "You don't have sufficient points to transfer.");
                return response()->json($this->_response, 200);
            }

            $transferData = [
                'receiver_email' => $request->receiver_email,
                'points' => $request->points,
                'comment' => $request->comment
            ];

            if(auth()->user()->transferPoints($transferData))
            {
                $this->setResponse(false, "Points transfer successfully.");
                return response()->json($this->_response, 200);
            }

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/**
     * Buy points
     * 
     * @return void
     */
    public function buyPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|numeric',
			'payment_type' => 'required'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            auth()->user()->points()->firstOrCreate()->increment('balance', (int)$request->points);
                
			$amount = (int)$request->points / 10;
			
			$pointDetail = auth()->user()->points->history()->create([
				'comment' => 'buy a point',
				'transaction_type' => 'received',
				'source_of_point' => 'buy',
				'points' => (int)$request->points,
				'payment_mode' => $request->payment_type,
				'amount' => $amount
			]);
			
            if($request->has('razorpay_order_id'))
            {
                // Online payment entry
                $payment = [
                    'user_id' => auth()->user()->_id,
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature,
                    'created_by' => auth()->user()->id
                ];
                
                /** Post payment **/
                dispatch(new PostPayment($pointDetail,$payment));
            }

			$this->setResponse(false, "Points buy successfully.");
            return response()->json($this->_response, 200);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
