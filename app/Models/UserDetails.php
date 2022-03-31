<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class UserDetails extends Eloquent
{
    protected $collection = 'user_details';

    protected $fillable = [
        'user_id', 'gender', 'phone', 'birth_date', 'country', 'nationality', 'aadhar_id', 'address', 'area', 'city', 'district', 'state', 'pincode', 'latitude', 'longitude', 'geo_location', 'marital_status', 'education_details', 'languages', 'professional_details', 'employment_status', 'total_ratings', 'avg_ratings', 'tp_points_balance', 'verified_status_percentage', 'verified_status', 'fb_url', 'li_url', 'tw_url', 'insta_url', 'online_cost_per_hour', 'offline_cost_per_hour', 'institute_cost_per_hour', 'tutor_home_cost_per_hour', 'student_home_cost_per_hour', 'discount_limit', 'preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics', 'mode_of_classes', 'request_received', 'request_sent', 'friends_id', 'my_students_ids', 'my_school_id', 'tutor_experience', 'tutor_commission', 'notifications', 'parent_ids', 'subscribed_tuition_ids', 'tp_id', 'hide_area', 'student_added_count', 'tutor_added_count', 'course_completed_count', 'given_answer_count', 'tutor_verified_status',
    ];

    protected $dates = [
        "birth_date",
    ];

    protected static function booted()
    {
        static::creating(function ($details) {
            $details->friend_ids = [];
            $details->my_students_ids = [];
            $details->tp_points_balance = 0;
            $details->tp_id = $details->generateTPID(request()->role);
            $details->hide_area = false;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_ids');
    }

    public function topics()
    {
        return $this->belongsTo(Topic::class, 'topic');
    }

    public function education()
    {
        return $this->embedsMany(Education::class);
    }

    public function experience()
    {
        return $this->embedsMany(Experience::class);
    }

    // 'preferred_boards', 'preferred_classes', 'preferred_subjects', 'preferred_topics'
    public function preferredBoards()
    {
        return $this->belongsToMany(Syllabus::class, null, "tutors_can_teach", "preferred_boards");
    }

    public function preferredClasses()
    {
        return $this->belongsToMany(TpClass::class, null, "tutors_can_teach", "preferred_classes");
    }

    public function preferredSubjects()
    {
        return $this->belongsToMany(Subject::class, null, "tutors_can_teach", "preferred_subjects");
    }

    public function preferredTopics()
    {
        return $this->belongsToMany(Topic::class, null, "tutors_can_teach", "preferred_topics");
    }

    public function subscribed_tuitions()
    {
        return $this->belongsToMany(Tuition::class, null, 'student_ids', 'subscribed_tuition_ids');
    }

    public function generateTPID($role)
    {
        $data = $this->withoutGlobalScopes()->select(['tp_id'])->orderBy('created_at', 'desc')->first();

        if($role)
        {
            if (!empty($data->tp_id)) {
                $prefix = 'TP-';
                switch ($role) {
                    case "tutor":
                        $prefix = "TP-T";
                        break;
                    case "school-tutor":
                        $prefix = "TP-ST";
                        break;
                    case "student":
                        $prefix = "TP-S";
                        break;
                    case "school-student":
                        $prefix = "TP-SS";
                        break;
                    case "parent":
                        $prefix = "TP-P";
                        break;
                    case "guardian":
                        $prefix = "TP-G";
                        break;
                    case "school-admin":
                        $prefix = "TP-SA";
                        break;
                    case "admin":
                        $prefix = "TP-A";
                        break;
                    default:
                        $prefix = "TP-";
                }

                $split = explode("-", $data->tp_id);
                $find = sizeof($split) - 1;
                $last_id = $split[$find];
                // $last_id = substr($last_id, 1);
                $last_id = preg_replace("/[^0-9]/", "", $last_id );
                $number = intval($last_id) + 1;
                $new_no = sprintf('%06d', $number);
                $tpID = $prefix . $new_no;
                return $tpID;
            } else {
                switch ($role) {
                    case "tutor":
                        $prefix = "TP-T";
                        break;
                    case "school-tutor":
                        $prefix = "TP-ST";
                        break;
                    case "student":
                        $prefix = "TP-S";
                        break;
                    case "school-student":
                        $prefix = "TP-SS";
                        break;
                    case "parent":
                        $prefix = "TP-P";
                        break;
                    case "guardian":
                        $prefix = "TP-G";
                        break;
                    case "school-admin":
                        $prefix = "TP-SA";
                        break;
                    case "admin":
                        $prefix = "TP-A";
                        break;
                    default:
                        $prefix = "TP-";
                }
                return $prefix . '000001';
            }
        }
        else{
            
            $role = $this->user->getRole();
            if($role)
            {
                if (!empty($data->tp_id)) {
                    $prefix = 'TP-';
                    switch ($role) {
                        case "tutor":
                            $prefix = "TP-T";
                            break;
                        case "school-tutor":
                            $prefix = "TP-ST";
                            break;
                        case "student":
                            $prefix = "TP-S";
                            break;
                        case "school-student":
                            $prefix = "TP-SS";
                            break;
                        case "parent":
                            $prefix = "TP-P";
                            break;
                        case "guardian":
                            $prefix = "TP-G";
                            break;
                        case "school-admin":
                            $prefix = "TP-SA";
                            break;
                        case "admin":
                            $prefix = "TP-A";
                            break;
                        default:
                            $prefix = "TP-";
                    }

                    $split = explode("-", $data->tp_id);
                    $find = sizeof($split) - 1;
                    $last_id = $split[$find];
                    // $last_id = substr($last_id, 1);
                    $last_id = preg_replace("/[^0-9]/", "", $last_id );
                    $number = intval($last_id) + 1;
                    $new_no = sprintf('%06d', $number);
                    $tpID = $prefix . $new_no;
                    return $tpID;
                }
            }
        }
    }
}
