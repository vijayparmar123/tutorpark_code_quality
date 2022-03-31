<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\PermissionResource;
use DateTime;
use App\Models\User;
use App\Jobs\UpdateTutorStatus;
use App\Mail\VerifyUser;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{    
    /** 
     * @param $request
     * validate params and create user 
     *  @return user resource
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:tutor,student,clerk,admin,school-tutor,school-student',
            'password' => 'required|confirmed|min:8',
            'address' => 'required',
            'gender' => 'required',
            'city' => 'required',
            'state' => 'required',
            'birth_date' => 'date',
            // 'pincode' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            
            $user = User::create($request->except('role','gender'));

            $user->details()->create([
                "gender" => $request->gender,
                "address" => $request->address,
                "area" => $request->address,
                "state" => $request->state,
                "city" => $request->city,
                "country" => $request->has('country') ? $request->country : 'India',
                "nationality" => $request->has('nationality') ? $request->nationality : 'Indian',
                "pincode" => $request->pincode,
                "phone" => $request->phone,
                "aadhar_id" => $request->aadhar_id,
                "birth_date" =>  new DateTime($request->birth_date),
            ]);

            $user->assignRole($request->role);

            $pointData = [
                'comment' => 'received points to signup at tutorpark',
                'type' => 'received',
                'source_of_point' => 'signup',
				'user_id' => $user->_id
            ];

            if($user->hasRole('student') || $user->hasRole('tutor') || $user->hasRole('school-student') || $user->hasRole('school-tutor'))
            {
                $user->signupPoints($pointData);
            }
            
            $mailData = [
                'verify_token' => $user->verify_token,
                'host' => getHost(),
            ];
            Mail::to($request->email)->queue(new VerifyUser($mailData));

            $friendData = FriendRequest::where([ ['to',$request->email], ['is_invited', true] ])->first();
            if(!empty($friendData)){

                //$friendData = FriendRequest::find($friendData->id);
                if (!$friendData->receiver->isFriend($friendData->sender->id))
                {
                    $friendData->receiver->addAsFriend($friendData->sender);
					
                    if($friendData->receiver->hasRole('student'))
					{
                        if($friendData->sender->hasRole('student'))
                        {
                            $source_of_point = 'refer_student';
                        }

                        if($friendData->sender->hasRole('tutor'))
                        {
                            $source_of_point = 'refer_tutor';
                        }

                        $pointData = [
                            'comment' => 'received points to refer a user',
                            'type' => 'received',
                            'source_of_point' => $source_of_point,
                            'user_id' => $friendData->receiver->_id
                        ];

                        $friendData->receiver->availPoints($pointData);
                    }

                    // Update tutor status on base of invite
					if($friendData->receiver->hasRole('tutor'))
					{
						if($friendData->sender->hasRole('student'))
						{
							$data = [
								'type' => 'student_added',
								'tutor_id' => $friendData->receiver->_id
							];
							// Update tutor status
							dispatch(new UpdateTutorStatus($data));
						}
						
						if($friendData->sender->hasRole('tutor'))
						{
							$data = [
								'type' => 'tutor_added',
								'tutor_id' => $friendData->receiver->_id
							];
							// Update tutor status
							dispatch(new UpdateTutorStatus($data));
						}
					}

                    // Associate school to newly registered user if user is from school and inviter has school associated
					if($friendData->sender->hasRole('school-student') || $friendData->sender->hasRole('school-tutor') ||$friendData->sender->hasRole('school-admin'))
					{
						if($friendData->receiver->hasSchool())
						{
							$school = $friendData->receiver->school;
                            $friendData->sender->assignSchool($school);
						}
					}
                }
                $friendData->delete();
                
            }
            
            // return $this->login($request);
            $this->_response['data'] = '';
            $this->setResponse(false, 'Registration successfully.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true,  $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

     /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        
        if (!$token = auth()->attempt($credentials)) {
            $this->setResponse(true, 'Invalid credentials.','401');
            return response()->json($this->_response, 200);
        }

        if(!auth()->user()->is_verified)
        {
            Auth::logout();
            $this->setResponse(true, 'Email '. request('email') .' not verified yet, Please check your email to verify it','401');
            return response()->json($this->_response, 200);
        }

        return $this->respondWithToken($token);
    }


     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

     /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
            // 'expires_in' => auth()->factory()->getTTL() * 60,
            'expires_in' => 14400,
            'message' => "Logged in successfully."
        ]);
    }
}
