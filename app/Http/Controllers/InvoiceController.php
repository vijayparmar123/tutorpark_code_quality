<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\School;
use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;

class InvoiceController extends Controller
{
    /**
     * Create Invoice
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $rules = [
            'invoice_for' => 'required|in:school,user',
            'date' => 'required|date_format:Y-m-d',
            'payment_method' => 'required',
            'payment_status' => 'required|in:paid,unpaid',
            'transaction_id' => 'nullable',
            'amount' => 'required|numeric',
        ];
		
		if ($request->invoice_for == "school") {
            $rules['invoice_for_id'] = 'required|exists:schools,_id';
        }
		
		if ($request->invoice_for == "user") {
            $rules['invoice_for_id'] = 'required|exists:users,_id';
            $rules['mode_of_tuition'] = 'required';
            $rules['course_tuition_name'] = 'required';
            $rules['session_taken'] = 'required|numeric';
        }
		
		$validator = Validator::make($request->all(), $rules);
		
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            if($request->invoice_for == 'school')
            {
                $school = School::find($request->invoice_for_id);
                $invoice = $school->invoice()->create([
                    'date' => $request->date,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->payment_status,
                    'transaction_id' => $request->transaction_id,
                    'amount' => $request->amount,
                    'purpose' => 'Invoice for school'
                ]);
            }else{
                $user = User::find($request->invoice_for_id);
                $invoice = $user->invoice()->create([
                    'date' => $request->date,
                    'payment_method' => $request->payment_method,
                    'payment_status' => $request->payment_status,
                    'transaction_id' => $request->transaction_id,
                    'amount' => $request->amount,
                    'mode_of_tuition' => $request->mode_of_tuition,
                    'course_tuition_name' => $request->course_tuition_name,
                    'session_taken' => $request->session_taken,
                    'purpose' => 'Invoice for tuition'
                ]);
            }

            $this->setResponse(false,'Invoice created successfully.');
            return response()->json($this->_response);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function allInvoices(Request $request)
    {
        try{
            $invoices = Invoice::Orderby('created_at', 'desc')->get();

            return InvoiceResource::collection($invoices)->additional([ "error" => false, "message" => 'Here is all invoice data.']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_for_id' => 'required|exists:invoices,invoicable_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $invoices = Invoice::where(['invoicable_id' => $request->invoice_for_id])->Orderby('created_at', 'desc')->get();

            return InvoiceResource::collection($invoices)->additional([ "error" => false, "message" => 'Here is all invoice data.']);

        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
