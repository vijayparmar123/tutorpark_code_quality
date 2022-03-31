<?php

namespace App\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;

trait ImpersonateUser
{

    public function actingAs($email)
    {
        $user = $this->where('email', $email)->first();
        
        return JWTAuth::fromUser($user);
    }

    // public function actingAsUser()
    // {
    //     $password = 'some-password';
        // $token = JWTAuth::getToken()); /// to get current user logged in user token

    //     // every generated e-mail will be accepted
    //     $user = factory(User::class)->create([
    //         'password' => bcrypt($password)
    //     ]);

    //     $token = auth('api')->attempt([
    //         'email' => $user->email,
    //         'password' => $password
    //     ]);

    //     $this->withHeaders(
    //         array_merge([
    //             $this->defaultHeaders,
    //             ['Authorization' => 'Bearer ' . $token]
    //         ])
    //     );

    //     return $this;
    // }
    
}
