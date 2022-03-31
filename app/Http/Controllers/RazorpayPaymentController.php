<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Http\Resources\RazorpayOrderResource;

class RazorpayPaymentController extends Controller
{
    /**
     * Create order to process payment from frontend.
     *
     * @return \Illuminate\Http\Response
     */
    public function createOrder(request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            
            $receipt =md5(uniqid(mt_rand(), true));
            
            $order = $api->order->create(array('receipt' => $receipt, 'amount' => $request->amount * 100, 'currency' => 'INR'));
            
            if($order->id)
            {
                return (new RazorpayOrderResource($order))->additional([ "error" => false, "message" => 'Payment Order details']);
            }else{
                $this->setResponse(false, "Order doesn't created by payment provider, try again.");
                return response()->json($this->_response, 200);
            }

        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
