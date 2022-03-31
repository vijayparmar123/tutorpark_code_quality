<?php

namespace App\Http\Controllers;

use Exception;
use Maklad\Permission\Models\Role;
use Maklad\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
    // Roles List
    public function index()
    {
        try {
            $roleData = Role::all()->load('permissions');
            return RoleResource::collection($roleData)->additional([ "error" => false, "message" => 'Here is all roles data']);
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Add new role and assign permissions
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permissions' => 'required|array', 
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {

            $role = Role::create(['name' => $request->name]);

            $role->syncPermissions($request->permissions);
            
            if($role) {
                $this->_response['data'] = $role;
                $this->setResponse(false,'Role added successfully in the database.');
                return response()->json($this->_response);
            } 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

     // Delete Role 
     public function delete(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'id' => 'required',
         ]);
         
         if ($validator->fails()) {
             $this->setResponse(true, $validator->errors()->all());
             return response()->json($this->_response, 400);
         }
         try {
             $roleDelete =  Role::find($request->id);
             if($roleDelete) {
                 $roleDelete->delete();
                 $this->setResponse(false, 'Role deleted successfully from database.');
                 return response()->json($this->_response);
             } 
             
         } catch (\Exception $e) {
             $this->setResponse(true, $e->getMessage());
             return response()->json($this->_response, 500);
         }
     }

     // Add new role and assign permissions
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'permissions' => 'required|array|min:1', 
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try {

            $role = Role::findOrFail($request->id);
            
            $role->syncPermissions($request->permissions);
            
            if($role) {
                $this->_response['data'] = $role;
                $this->setResponse(false,'Permission updated successfully in the database.');
                return response()->json($this->_response);
            } 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
