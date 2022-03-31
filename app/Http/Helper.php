<?php

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\Course;
use App\Models\User;
use App\Models\Feedback;

function getUniqueStamp() {
    return (int)round(microtime(true) * 1000 * rand(100,999));
};

function getDateTime($date) {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y g:i a');
};

function getDateF($date) {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y');
};

function getTimeF($date) {
    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('g:i a');
};

function getDateFromDay($now, $end, $searchday) {

    $dateDay = $now;//use your date to get month and year
    $year = $dateDay->year;
    $month = $dateDay->month;
    $dateOfDay = $dateDay->day;
    $days = $end->diffInDays($dateDay);
    $days = $days+1;
    $mondays = [];
    $i=0;
    foreach (range($i, $days) as $day) {
        if($i==0){
            $date = \Carbon\Carbon::createFromDate($year, $month, $dateOfDay);
            $dayname = $date->dayName;
            if ($dayname === $searchday) {
                $mondays[] = (Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y'));
            }
        }
        else{
            $date= $date->addDays(1);
            $dayname = $date->dayName;
            if ($dayname === $searchday) {
                $mondays[] = (Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y'));
            }
        }
        $i++;
    }
    
    return $mondays;
};

function array_sort_by_column(&$array, $column, $direction = SORT_ASC) {

    $reference_array = array();
    $orignal = array();
    $sortArray = array();

    foreach($array as $key => $row) {

        $orignal[$key] = [
            'id' => $row->id,
            'start_time' => \Carbon\Carbon::parse($row->start_time)->format('g:i a'),
            'end_time' => \Carbon\Carbon::parse($row->end_time)->format('g:i a'),
            'day' => $row->day,
        ];

    }

    // foreach($array as $key => $row) {
    //     $reference_array[$key] = $row[$column];
    // }
    usort($orignal, function($a, $b) {
        //return new DateTime($a['day']) <=> new DateTime($b['day']);
        return Carbon::parse(strtoupper($a['day']))->format('d-m-Y') <=> Carbon::parse(strtoupper($b['day']))->format('d-m-Y');
      });
    foreach($orignal as $key => $value) {

        $temp = $value['day'];

        if($value['day'] == $temp){
            $sortArray[$value['day']][] = $value;
        }
        
    }
    //array_multisort($reference_array, $direction, $orignal);
    
    return $sortArray;
};


if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

function getTpCommission()
{
    return 10;
}

function getHost()
{
    $setting = Setting::first();
	return ($setting->host)?$setting->host:'';
}

function getUserTimezone() 
{
    return auth()->user()->timezone ?? 'Asia/Kolkata'; //UTC
}

function myStudents()
{
	$students = array();
	$myTuition = auth()->user()->my_tuitions;
	foreach($myTuition as $tuition)
	{
		$subscribedStudents = $tuition->students;
		foreach($subscribedStudents as $student)
		{
			if($student->user->hasRole('student'))
			{
				$user = $student->user->_id;
				$students[] = $user;
			}
		}
	}
	
	$myCourse = auth()->user()->courses;
	foreach($myCourse as $course)
	{
		$courseSubscription = $course->subscriptions;
		// dd($courseSubscription);
		foreach($courseSubscription as $subscription)
		{
			if($subscription->subscribedUser)
			{
				if($subscription->subscribedUser->hasRole('student'))
				{
					$subscribedStudent = $subscription->subscribedUser->_id;;
					$students[] = $subscribedStudent;
				}
			}
		}
	}
	$students = array_unique($students);
	return $students;
}

function getTutorAVGRating($tutor_id) 
{
	$tutor = User::find($tutor_id);
	$course_rating = $tutor->courses;
	
	$ratingValues = [];

    foreach ($course_rating as $rating) {
		if($rating->avg_ratings)
		{
			$ratingValues[] = (int)$rating->avg_ratings;
		}
    }
	
	$tuition_ids = $tutor->my_tuitions->pluck('_id');
	$tuition_rating = Feedback::whereIn('feedback_reference_id',$tuition_ids)->get(['total_ratings']);
	
	foreach ($tuition_rating as $rating) {
		if($rating->total_ratings)
		{
			$ratingValues[] = (int)$rating->total_ratings;
		}
    }
	if(count($ratingValues))
	{
		$avg_rating = collect($ratingValues)->sum() / count($ratingValues);
	}else{
		$avg_rating = 0;
	}
	
    return number_format($avg_rating, 1);
}