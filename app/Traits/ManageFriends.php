<?php

namespace App\Traits;

use App\Models\User;

trait ManageFriends
{
    /**
     * Get all friends of current user
     * 
     * @return collection
     */
    public function friends()
    {
        return User::whereIn('_id', $this->details->friend_ids)->where(['is_verified'=>1])->get();
    }

    /**
     * Add as a friend
     * 
     * @return void
     */
    public function addAsFriend(User $sender)
    {
        /** Add friend to current user ***/
        $this->details->push('friend_ids', $sender->id);

        /** Add current user as friend to sender user ***/
        $sender->details->push('friend_ids', $this->id);
    }

    /**
     * Unfriend as user
     * 
     * @return void
     */
    public function unFriend(User $endUser)
    {
        /** Un-friend end user ***/
        $this->details->pull('friend_ids', $endUser->id);

        /** Un-friend current user ***/
        $endUser->details->pull('friend_ids', $this->id);
    }

    public function isFriend($userId)
    {
        return in_array($userId,$this->details->friend_ids);
    }
    
}
