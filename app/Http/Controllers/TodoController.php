<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\Todo;
use App\Models\User;
use App\Http\Resources\TodoResource;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TodoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $todosData = auth()->user()->todos()->orderBy('created_at','desc')->get();
            return TodoResource::collection($todosData)->additional([ "error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required:max:100'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $user = auth()->user();
            $todosData = $user->todos()->create($request->all());
            $todosData->is_completed = false;
            $todosData->save();

            if($todosData){

                $this->setResponse(false, 'Todos added successfully in the database.');
                return response()->json($this->_response); 

            }
                       
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function markAsComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:todos,_id'
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            $todosUpdate = Todo::find($request->id);
            $todosUpdate->is_completed = true;
            $todosUpdate->mark_date = Carbon::now();
            $todosUpdate->save();

            if($todosUpdate){
                //$this->_response['data'] = $todosUpdate;
                $this->setResponse(false,'Todos data updated successfully in the database.');
                return response()->json($this->_response);
            }
 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function markAsIncomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:todos,_id'
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            $todosUpdate = Todo::find($request->id);
            $todosUpdate->is_completed = false;
            $todosUpdate->mark_date = Carbon::now();
            $todosUpdate->save();

            if($todosUpdate){
                //$this->_response['data'] = $todosUpdate;
                $this->setResponse(false,'Todos data updated successfully in the database.');
                return response()->json($this->_response);
            }
 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:todos,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $todosDelete =  Todo::find($request->id);
            if ($todosDelete) {
                $todosDelete->delete();
                $this->_response['data'] = $todosDelete;
                $this->setResponse(false, 'Todos deleted from database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
