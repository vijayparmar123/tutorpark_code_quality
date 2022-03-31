<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Resources\ModuleResource;


class ModuleController extends Controller
{
    // Module List
    public function index()
    {
        try {
            $moduleData = Module::all();
            return ModuleResource::collection($moduleData)->additional([ "error" => false, "message" => 'Here is all modules data']);
        } catch (UserNotDefinedException $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
