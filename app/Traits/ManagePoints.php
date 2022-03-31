<?php

namespace App\Traits;

use App\Models\Point;
use App\Models\PointTransferHistory;
use App\Models\Setting;
use App\Models\User;

trait ManagePoints
{
    /**
     * Function to retrive user points balance
     *
     * @return void
     */
    public function balance()
    {
        return ($this->points) ? $this->points->balance : 0;
    }

    /**
     * Function to add points in user balance for specific action like signup, post job
     *
     * @return void
     */
    public function signupPoints($data)
    {
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);

            $points = Setting::first();

            if ($user) {
                if ($user->hasRole('student') || $user->hasRole('school-student')) {
                    $role = 'student_point';
                } else {
                    $role = 'tutor_point';
                }
            }
            $action = "'" . $data['source_of_point'] . "'";

            $point = isset($points[$role][$action]) ? $points[$role][$action] : 0;

            $data['points'] = $point;
            
            return $this->increasePoints($user, $data);
        }

    }

    /**
     * Function to add points in user balance for specific action like signup, post job
     *
     * @return void
     */
    public function availPoints($data)
    {
        $point = $this->getActionPoint($data['source_of_point']);
        $data['points'] = $point;
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);
            return $this->increasePoints($user, $data);
        } else {
            return $this->increasePoints($this, $data);
        }

    }

    /**
     * Function to transfer points to another user
     *
     * @return void
     */

    public function transferPoints($data)
    {
        // Manage points for receiver
        $receiver = User::where(['email' => $data['receiver_email']])->first();
        $receiverPointData = [
            'comment' => 'received points from someone',
            'transaction_type' => 'received',
            'source_of_point' => 'transferred',
            'points' => (float) $data['points'],
            'transferred_from' => $this->id,
            'transferred_to' => $receiver->id,
        ];

        $receiverRecord = $this->increasePoints($receiver, $receiverPointData);

        // Manage points for sender
        $senderPointData = [
            'comment' => $data['comment'],
            'transaction_type' => 'spent',
            'source_of_point' => 'transferred',
            'points' => $data['points'] * -1,
            'transferred_from' => $this->id,
            'transferred_to' => $receiver->id,
        ];
        $senderRecord = $this->increasePoints($this, $senderPointData);

        // Point transfer history
        $transferHistoryData = [
            'send_by' => $this->id,
            'send_to' => $receiver->id,
            'points' => $data['points'],
            'comment' => $data['comment'],
        ];

        $transferHistory = PointTransferHistory::create($transferHistoryData);
        // $transferHistory->pointHistory()->attach([$receiverRecord->id,$senderRecord->id]);

        return $transferHistory;
    }

    /**
     * Function will be called from availPoints, and transferPoints function to manage add points centrally
     *
     * @return void
     */
    public function increasePoints(User $user, $data)
    {
        $record = $user->points()->firstOrCreate()->increment('balance', (int) $data['points']);

        return $this->createHistory($user, $data);
    }

    /**
     * Function to create points history
     *
     * @return void
     */
    private function createHistory(User $user, $data)
    {
        return $user->points->history()->create($data);
    }

    /**
     * Function to retrive specific action points
     *
     * @return void
     */
    private function getActionPoint($action)
    {
        $points = Setting::first();

        // if(auth()->user())
        // {
        //     if(auth()->user()->hasRole('student') || auth()->user()->hasRole('school-student'))
        //     {
        //         $role = 'student_point';
        //     }else{
        //         $role = 'tutor_point';
        //     }
        // }else{
        //     $role = 'student_point';
        // }
        if ($this) {
            if ($this->hasRole('student') || $this->hasRole('school-student')) {
                $role = 'student_point';
            } else {
                $role = 'tutor_point';
            }
        }
        $action = "'" . $action . "'";

        // $points = array(
        //     'signup' => 50,
        //     'friend_request' => 10,
        //     'refer_student' => 20,
        //     'refer_tutor' => 30,
        //     'rate_session' => 5,
        //     'complete_assignment' => 10,
        //     'mass_referral' => 300,
        //     'post_question' => -1,
        //     'post_job' => -1,
        //     'hired_tutor' => 10,
        //     'post_course' => -50,
        //     'find_job' => -2,
        //     'demo_class' => 20,
        //     'answered_question' => 10,
        //     'axis_class' => -50,
        //     'create_timetable' => 5,
        //     'post_diary' => 5
        // );

        return isset($points[$role][$action]) ? $points[$role][$action] : 0;
    }

}
