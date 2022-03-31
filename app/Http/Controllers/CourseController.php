<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Course;
use App\Models\SubscribeCourse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseListResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\SubscribeCourseResource;
use App\Jobs\AddEarningTransactionJob;
use App\Models\CourseSubscription;
use App\Models\User;
use App\Jobs\UpdateTutorStatus;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
// use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\PostPayment;

class CourseController extends Controller
{

    /**
     * Display a listing of the Course.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'exists:syllabuses,_id',
            'class_id' => 'exists:classes,_id',
            'subject_id' => 'exists:subjects,_id',
            'city' => 'filled|max:50',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            // $courses = Course::all();
            $courses = Course::whereSyllabus($request->syllabus_id)->whereClass($request->class_id)->whereSubject($request->subject_id)->Orderby('created_at', 'desc')->get();

            $user_id = auth()->id();
            $courses = $courses->filter(function($course) use($user_id){
                if(!$course->subscriptions->contains('user_id',$user_id))
                {
                    return true;
                }
                return false;
             });

            if($request->has('city')){
                $city = $request->city;
                $courses = $courses->filter(function($course) use($city){
                    return $course->isCity($city);
                });
            }
            
            return CourseListResource::collection($courses)->additional(["error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a Course Data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:50',
            'description' => 'required|max:500',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required', 'exists:classes,_id', new SyllabusExists],
            'subject_id' => ['required', 'exists:subjects,_id', new ClassExists],
            'mode_of_teaching' => 'required',
            'type' => 'required',
            'cost' => 'required|numeric',
            'number_of_people_attending' => 'required|numeric',
            'course_topics' => 'required',
            //'start_date' => 'required|date',
            //'end_date' => 'required|date',
            'demo_video' => 'filled|mimes:mp4,mov,wmv,mkv,avi|max:102400',
            // 'sample_images' => 'filled|array',
            // 'sample_images.*' => 'filled|image|mimes:jpeg,png,jpg|max:2048',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'course_type' => 'required',
            'course_video' => 'required_if:course_type,Recorded|mimes:mp4,mov,wmv,mkv,avi|max:102400',
            'library_id' => 'filled|exists:libraries,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true,  $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
		
        try {
            $courseStore = Course::create($request->except(['course_video', 'logo', 'sample_images', 'demo_video']));
           
            /* Defult Value Set */
            // $imageName = 'images/course/default.png';
            // $videoName = 'video/course/default.mp4';

            /*Demo Video Upload Code  */
            if ($request->has('demo_video')) {

                $videoUrl = $this->uploadFile($request->demo_video, 'video/course');
                if ($videoUrl != false) {
                    $courseStore->demo_video = $videoUrl;
                }
                // $mediaName = time() . '.' .$request->demo_video->extension();
                // $videoName = 'video/course/'. $mediaName;
                // $request->demo_video->storeAs('public', $videoName);
                // $courseStore->demo_video = $videoName;

            }

            /* Course Video Upload Code  */
            if ($request->has('course_video')) {

                $courseVideoUrl = $this->uploadFile($request->course_video, 'video/course');
                if ($videoUrl != false) {
                    $courseStore->course_video = $courseVideoUrl;
                }
            }
            // $courseStore->demo_video = $videoName;

            //add sample image
            // $this->addSampleImages($request->sample_images, $courseStore);
            // $courseImages = $this->addFileAttachments($request->sample_images, 'images/course');
            // $courseStore->sample_images = $courseImages;

            if($request->has('logo')) {

                $courseLogo = $this->uploadFile($request->logo, 'course/logos');
                if($courseLogo != false){
                    $courseStore->logo = $courseLogo;
                }
            }

            /* Image Upload Code */
            // if($request->has('sample_images')){
            //     $picName = time() . '.' .$request->sample_images->extension();
            //     $imageName = 'images/course/' . $picName;
            //     $request->sample_images->storeAs('public', $imageName);
            //     $courseStore->sample_images = $imageName;
            // }
            // $courseStore->sample_images = $imageName;

            $courseStore->save();

            // Avail point for post a course
            $pointData = [
                'comment' => 'spent points for post a course',
                'transaction_type' => 'spent',
                'source_of_point' => 'post_course'
            ];
            
            auth()->user()->availPoints($pointData);

            $this->setResponse(false, 'Course created successfully.');
            return response()->json($this->_response);
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // add sample images
    protected function addSampleImages($sampleImages, $courseStore)
    {
        if (!empty($sampleImages)) {
            foreach ($sampleImages as $key => $sampleImage) {
                if (!empty($sampleImage)) {
                    $filePath = 'images/course/' . getUniqueStamp() . $key . '.' . $sampleImage->extension();
                    $sampleImage->storeAs('public', $filePath);
                    $courseStore->push('sample_images', $filePath);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $course =  Course::find($request->id);

            if ($course) {
                return (new CourseResource($course))->additional(["error" => false, "message" => 'Course data for Show']);
            } else {
                $this->setResponse(true, 'Course Data not found.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Course id Fetch Data  .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $course =  Course::find($request->id);

            if ($course) {
                return (new CourseResource($course))->additional(["error" => false, "message" => 'Course data for edit']);
            } else {
                $this->setResponse(true, 'Course Data not found.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Course Record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'title' => 'required|max:50',
            'description' => 'required|max:500',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required', 'exists:classes,_id', new SyllabusExists],
            'subject_id' => ['required', 'exists:subjects,_id', new ClassExists],
            'mode_of_teaching' => 'required',
            'type' => 'required',
            'cost' => 'required|numeric|min:4',
            //'start_date' => 'required|date',
            //'end_date' => 'required|date',
            'demo_video' => 'nullable|mimes:mp4,mov,wmv,mkv,avi|max:102400',
            // 'sample_images' => 'filled|array',
            // 'sample_images.*' => 'filled|image|mimes:jpeg,png,jpg|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'course_type' => 'required',
            'course_video' => 'nullable:course_type,Recorded|mimes:mp4,mov,wmv,mkv,avi|max:102400',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {

            $course = Course::find($request->id);
            $course->update($request->except(['course_video', 'logo', 'sample_images', 'demo_video']));

            /*Demo Video Upload Code  */
            if ($request->has('demo_video')) {

                $videoUrl = $this->uploadFile($request->demo_video, 'video/course');
                if ($videoUrl != false) {
                    $course->demo_video = $videoUrl;
                }
            }

             /* Course Video Upload Code  */
             if ($request->has('course_video')) {

                $courseVideoUrl = $this->uploadFile($request->course_video, 'video/course');
                if ($videoUrl != false) {
                    $course->course_video = $courseVideoUrl;
                }
            }
            
            if($request->has('logo')) {
                $courseLogo = $this->uploadFile($request->logo, 'course/logos');
                if($courseLogo != false){
                    $course->logo = $courseLogo;
                }
            }

            if ($course->update()) {
                $this->_response['data'] = $course;
                $this->setResponse(false, 'Course data updated successfully in the database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Delete Course Record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
            $courseDelete =  Course::find($request->id);
            if ($courseDelete) {
                $courseDelete->delete();
                $this->_response['data'] = $courseDelete;
                $this->setResponse(false, 'Course deleted from database.');
                return response()->json($this->_response);
            }
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // get Mycourse list 
    /* Createdby filed Record User table id Get*/
    public function subscribedCourseList()
    {
        try {
            $courses =  Course::with('subscriptions')->whereHas('subscriptions', function($query){
                $query->where('user_id', auth()->id());
            })->get();
            
            return (CourseListResource::collection($courses))->additional(["error" => false, "message" => null]);
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // Subscription Into Course Insert New Record 
    public function createSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $course = Course::find($request->course_id);

            $subscription = CourseSubscription::create($request->except('email'));

            $subscription->user_id  = auth()->id(); 
            $subscription->start_date  = new \DateTime();
            $subscription->end_date  = null;
            $subscription->status  = 'in-progress';
            $subscription->save();

            if($course) {

                $transaction = [
                    'paid_to' => $course->created_by,
                    'paid_from' => auth()->id(),
                    'date' => Carbon::now(),
                    'payment_mode' => ($request->has('razorpay_order_id'))?'Razorpay':'cash',
                    'transaction_id' => (string) Str::uuid(),
                    'amount' => $course->cost,
                    'payment_status' => 'paid',
                    'model' => get_class($course),
                    'model_id' => $course->id,
                    "mode_of_teaching" => ucwords(str_replace('_',' ',$course->mode_of_teaching))
                ];
    
                /** add payment transaction and commission & final amount **/
                dispatch(new AddEarningTransactionJob($subscription,$transaction));

                if($request->has('razorpay_order_id'))
                {
                    // Online payment entry
                    $payment = [
                        'user_id' => auth()->user()->_id,
                        'razorpay_order_id' => $request->razorpay_order_id,
                        'razorpay_payment_id' => $request->razorpay_payment_id,
                        'razorpay_signature' => $request->razorpay_signature,
                        'created_by' => auth()->user()->id
                    ];
                    
                    /** Post payment **/
                    dispatch(new PostPayment($subscription,$payment));
                }

                if($course->author)
                {
                    //Add student in network
                    $course->author->addAsFriend(auth()->user());
                }
            }

            $this->setResponse(false, 'Subscription created successfully.');
            return response()->json($this->_response);
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    //get Completed Course
    public function completeSubscribeIntoCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        $userID = $request->user_id;
        try {

            // for student
            // $studentSubscriptionData = SubscribeCourse::where('student_id', $userID)->get();

            // for tutor
            $subscriptionCourse = SubscribeCourse::where('completed_status', 'yes')->with('course')->whereHas('course', function ($query) use ($userID) {
                $query->where("created_by", $userID);
            })->get();

            return SubscribeCourseResource::collection($subscriptionCourse)->additional(["error" => false, "message" => 'Courese Completed']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    //get UnCompleted Course
    public function uncompleteSubscribeIntoCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        $userID = $request->user_id;
        try {
            // for student
            // $studentSubscriptionData = SubscribeCourse::where('student_id', $userID)->get();

            // for tutor
            $subscriptionCourse = SubscribeCourse::where('completed_status', 'no')->with('course')->whereHas('course', function ($query) use ($userID) {
                $query->where("created_by", $userID);
            })->get();

            return SubscribeCourseResource::collection($subscriptionCourse)->additional(["error" => false, "message" => 'No Subscribe Course completed']);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,_id',
            'message' => 'required|max:100',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $course = Course::find($request->course_id);
            $course->messages()->create([
                "sender_id" => auth()->id(),
                "message" => $request->message
            ]);

            $this->setResponse(false, 'Message sent successfully.');
            return response()->json($this->_response, 200);

        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    public function subscriptionMarkAsComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $today = Carbon::now();
            $subscription = CourseSubscription::where([['course_id',$request->course_id],['user_id',auth()->id()]])->first();
            $subscription->status  = 'compeleted';
            $subscription->end_date  = $today->format('d-m-Y H:i');
            $subscription->save();
			if(auth()->user()->hasRole('tutor'))
			{
				$data = [
					'type' => 'course_completed',
					'tutor_id' => auth()->user()->_id
				];
				// Update tutor status
				dispatch(new UpdateTutorStatus($data));
			}
            $this->setResponse(false, 'Subscription Completed.');
            return response()->json($this->_response);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}
