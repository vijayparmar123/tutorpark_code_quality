<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Resources\PermissionResource;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class ImporsonateController extends Controller
{
    public function switchAccount()
    {
        try{
            // $token = auth()->user()->actingAs($request->email);
            $user = auth()->user();
            if($user->linked_email == '' )
            {
                $this->setResponse(true, "You don't have any account linked.");
                return response()->json($this->_response, 200);
            }

            $otherAccount = User::where(['email' => $user->linked_email])->first();
            
            /* Logout user and then imporsonate using anoher user 
                                        OR
            Logout imporsonated user and login with actual user */

            Auth::logout();
            
            $token = Auth::login($otherAccount);
            return $this->respondWithToken($token);
           
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'data' => (new UserResource(auth()->user())),
            'access_token' => $token,
            'permissions' => PermissionResource::collection(auth()->user()->getAllPermissions()),
            'token_type' => 'bearer',
            'error' => false,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'message' => "Logged in successfully."
        ]);
    }
}
