<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;

class BroadcastController extends Controller {

    public function authenticate(Request $request)
    {
		// dd(Broadcast::auth($request));
        return Broadcast::auth($request);
		//  $user = auth()->user();
        // $socket_id = $request['socket_id'];
        // $channel_name =$request['channel_name'];
        // $key = getenv('PUSHER_APP_KEY');
        // $secret = getenv('PUSHER_APP_SECRET');
        // $app_id = getenv('PUSHER_APP_ID');

        // if ($user) {
     
        //     $pusher = new Pusher($key, $secret, $app_id);
        //     $auth = $pusher->socket_Auth($channel_name, $socket_id);

        //     return response($auth, 200);

        // } else {
        //     header('', true, 403);
        //     echo "Forbidden";
        //     return;
        // }
    }
}