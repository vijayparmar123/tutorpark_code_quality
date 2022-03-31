<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Events\PusherEvent;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    // return $router->app->version();
    // $user = \App\Models\User::where('email','priyal@dasinfomedia.com')->first();
    return "This is API backend server. Try again";
});

$router->group([ 'prefix' => 'api'], function($router) {
    $router->post('/user/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->get('list', 'UserController@index');// List of all users
    $router->post('/user/emailVerification', 'UserController@VerifyEmail');// Verify User Email
	
	$router->post('/password/email', 'PasswordController@postEmail');
	// $router->post('/password/reset/{token}', 'PasswordController@postReset');
	$router->post('password/reset', ['as' => 'password.reset', 'uses' => 'PasswordController@postReset']);

    $router->group(['middleware' => 'auth'], function($router)
    {
        $router->post('/logout', 'AuthController@logout');
        $router->post('refresh','AuthController@refresh');
        $router->get('me', 'AuthController@me');// get Logged in user data

        $router->get('/pusher/{message}',function ($message){
            broadcast(new PusherEvent($message));
        });

        $router->post('broadcasting/auth', 'BroadcastController@authenticate');
        
        // $router->group(['middleware' => 'auth','prefix' => 'user'], function () use ($router) {
        /**
         * Course Module Api List,Add,Edit,Update,Delete,View,Dropdown
         */
        $router->group(['prefix' => 'user'/*, 'middleware' => 'ApplySchoolUser'*/], function () use ($router) {
            $router->get('list', 'UserController@index');// List of all users
            $router->get('userDropdown', 'UserController@userDropdown');// List of all users for dropdown use
            $router->get('friendsDropdown', 'UserController@friendsDropdown');// List of all users for dropdown use
            $router->post('userByRole', 'UserController@userByRole');// List of all users for dropdown use
            $router->post('dropdownByRole', 'UserController@dropdownByRole');// List of all users for dropdown use
            $router->post('store', 'UserController@store');// add new user in the database
            $router->post('update', 'UserController@update');// update User data from ID
            $router->post('updateOtherUser', 'UserController@updateOtherUser');// update User data from ID
            $router->post('delete', 'UserController@delete');// delete User data from ID
            $router->post('show','UserController@show');// view Course data from ID
            // $router->post('updateprofile','UserController@updateprofile');// view Course data from ID
            $router->post('userProfile','UserController@userProfile');// Add User Profile Details
            $router->get('all/profile','UserController@allProfiles'); // List User Profile Detils
            // $router->post('updateUserPrfile','UserController@updateUserPrfile');// Update User Profile Details
            $router->post('updateUserProfileDetils','UserController@updateUserProfileDetils');//Edit User Profile Details
            $router->post('profile','UserController@profile');// View User Profile Detils
            $router->post('friendprofile','UserController@friendprofile');// View User Profile Detils
            $router->post('impersonate','UserController@impersonate');
            $router->post('assignrole','UserController@assignrole');
            $router->get('earnings','UserController@earnings');
            $router->get('expenses','UserController@expenses');
            $router->post('update/ForSubject','UserController@updateForSubject');
            $router->post('invite','UserController@inviteUser');
            $router->post('myChilds','UserController@myChilds');
            $router->post('storeGeoLocation','UserController@storeGeoLocation');
            $router->post('requestForAccess','UserController@requestForAccess');
            $router->post('updateHideArea','UserController@updateHideArea');
            $router->post('testMail','UserController@testMail');
            $router->post('directVerify','UserController@directVerify');
        });

        $router->group(['prefix' => 'friend', 'middleware' => 'ApplySchoolUser'], function () use ($router) {
            $router->post('request/send','FriendController@requestSend');
            $router->get('request/list','FriendController@requestList');
            $router->post('accept','FriendController@accept');
            $router->post('reject','FriendController@reject');
            $router->get('list','FriendController@list');
            $router->post('block','FriendController@block');
            $router->post('request/spam','FriendController@markRequestAsSpam');
            $router->post('unfriend','FriendController@unfriend');
            $router->get('list/nonfriends','FriendController@nonFriendList');
        });

        /*Course Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'course'], function () use ($router) {
            $router->post('list', 'CourseController@index');//List of all Course
            $router->post('store', 'CourseController@store');// add new Course in the database
            // $router->post('edit','CourseController@edit');// get Course data from ID for edit
            $router->post('update','CourseController@update');// update Course data from ID
            $router->post('delete', 'CourseController@delete');// delete Course data from ID
            $router->post('show','CourseController@show');// view Course data from ID
            $router->get('subscribed/list','CourseController@subscribedCourseList');// view Course data from ID
            
            // $router->post('get','CourseController@get');//DropDown Api Id and Name Fatche
            $router->post('subscribe','CourseController@createSubscription');//New Created Subscription fro Course
            $router->post('completeSubscribeIntoCourse','CourseController@completeSubscribeIntoCourse');//Complete Course data form user Id and completed statuse
            $router->post('uncompleteSubscribeIntoCourse','CourseController@uncompleteSubscribeIntoCourse');//Uncomplete Course data form user Id and completed statuse
            $router->post('send/message','CourseController@sendMessage');// view Course data from ID
            $router->post('subscription/markcompeleted','CourseController@subscriptionMarkAsComplete');// view Course data from ID
        });

        /*Syllabus Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'syllabus'], function () use ($router) {
            $router->get('list', 'SyllabusController@index');//List of all Course
            $router->post('store','SyllabusController@store');// add new Course in the database
            $router->post('edit','SyllabusController@edit');// get Course data from ID for edit
            $router->post('update','SyllabusController@update');// update Course data from ID
            $router->post('delete','SyllabusController@delete');// delete Course data from ID
            $router->post('show','SyllabusController@show');// view Course data from ID
            $router->post('getSyllabusDropdownList','SyllabusController@getSyllabusDropdownList');// DropDown Api Id and Name Fatche
            
        });

        /*Subject Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'subject'], function () use ($router) {
            $router->post('list', 'SubjectController@index');//List of all Subject
            $router->get('alllist', 'SubjectController@list');//List of all Subject
            $router->post('store','SubjectController@store');// add new v in the database
            $router->post('edit','SubjectController@edit');// get Subject data from ID for edit
            $router->post('update','SubjectController@update');// update Subject data from ID
            $router->post('delete','SubjectController@delete');// delete Subject data from ID
            $router->post('show','SubjectController@show');// view Subject data from ID 
            $router->post('getSubjectDropdownList','SubjectController@getSubjectDropdownList');// DropDown Api Id and Name Fatche
        });

        /*Topic Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'topic'], function () use ($router) {
            $router->get('list', 'TopicController@index');//List of all Topic
            $router->post('store','TopicController@store');// add new Topic in the database
            $router->post('edit','TopicController@edit');// get Topic data from ID for edit
            $router->post('update','TopicController@update');// update Topic data from ID
            $router->post('delete','TopicController@delete');// delete Topic data from ID
            $router->post('show','TopicController@show');// view Topic data from ID 
            $router->post('getTopicDropdownList','TopicController@getTopicDropdownList');// DropDown Api Id and Name Fatche
        });

        /*Library Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'library'], function () use ($router) {
            $router->get('list', 'LibraryController@index');//List of all Library
            $router->get('libraryDropdown', 'LibraryController@libraryDropdown');//List of all Library
            $router->post('store','LibraryController@store');// add new Library in the database
            $router->post('edit','LibraryController@edit');// get Library data from ID for edit
            $router->post('update','LibraryController@update');// update Library data from ID
            $router->post('delete','LibraryController@delete');// delete Library data from ID
            $router->post('show','LibraryController@show');// view Library data from ID 
            $router->post('filter','LibraryController@filterList');// filter Library data 
            $router->post('addAttachmentImages','LibraryController@addAttachmentImages');
            $router->post('comment','LibraryController@comment');
            $router->post('shareLibrary','LibraryController@shareLibrary');
            $router->get('mySharedLibrary','LibraryController@mySharedLibrary');
        });

        /*Class Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'class'], function () use ($router) {
            $router->post('list', 'ClassController@index');//List of all Classes
            $router->post('store','ClassController@store');// add new Classes in the database
            $router->post('edit','ClassController@edit');// get Classes data from ID for edit
            $router->post('update','ClassController@update');// update Classes data from ID
            $router->post('delete','ClassController@delete');// delete Classes data from ID
            $router->post('show','ClassController@show');// view Classes data from ID 
            $router->post('getClassDropdownList','ClassController@getClassDropdownList');// DropDown Api Id and Name Fatche
        });

        /*Level Module Api List,Add,Edit,Update,Delete,View,Dropdown */
        $router->group(['prefix' => 'level'], function () use ($router) {
            $router->get('list', 'LevelController@index');//List of all Classes
            $router->post('store','LevelController@store');// add new Classes in the database
            $router->post('edit','LevelController@edit');// get Classes data from ID for edit
            $router->post('update','LevelController@update');// update Classes data from ID
            $router->post('delete','LevelController@delete');// delete Classes data from ID
            $router->post('show','LevelController@show');// view Classes data from ID 
            $router->post('getLevelDropdownList','LevelController@getLevelDropdownList');// DropDown Api Id and Name Fatche
        });

        /* Tutor Time Table Api */
        $router->group(['prefix' => 'tutor'], function () use ($router) {
            $router->post('store','TutorTimeTableController@store');//List of all Classes
            $router->get('list','TutorTimeTableController@index');// add new Classes in the database
            $router->get('find','TutorController@findTutor');
            $router->post('list/filter','TutorController@filterList');
            $router->get('myStudent','TutorController@myStudent');
        });

        $router->group(['prefix' => 'textbook'], function () use ($router) {
            $router->post('store','TextBookController@store');//List of all Classes
            $router->get('list','TextBookController@index');// add new Classes in the database
            $router->post('edit','TextBookController@edit');// get Classes data from ID for edit
            $router->post('update','TextBookController@update');// update Classes data from ID
            $router->post('delete','TextBookController@delete');// delete Classes data from ID
            $router->post('show','TextBookController@show');// view Classes data from ID 
            $router->post('list/filter','TextBookController@filterList');// get Classes data from ID for edit
        
        });

        $router->group(['prefix' => 'job'], function ($router) {
            $router->post('store','TpJobController@store');
            $router->post('list','TpJobController@index');
        });

        $router->group(['prefix' => 'notebook'], function () use ($router) {
            $router->post('store','NoteBookController@store');//List of all Notebook
            $router->get('list','NoteBookController@index');// add new Notebook in the database 
            $router->post('update','NoteBookController@update');// update Notebook data from ID       
            $router->post('delete','NoteBookController@delete');// Delete Notebook data from ID       
            $router->post('subjecttutor','NoteBookController@subjectTutors');// Get tutor pluck from subject id       
            //$router->post('addTimeline','NoteBookController@addTimeline');// update Notebook data from ID       
        });

        $router->group(['prefix' => 'questions'], function () use ($router) {
            $router->post('store','QuestionsController@store');//List of all Questions
            $router->get('list','QuestionsController@index');// add new Questions in the database 
            $router->post('update','QuestionsController@update');// update Questions data from ID             
            $router->post('addLikeDislike','QuestionsController@addLikeDislike');// update Questions data from ID             
            $router->post('delete','QuestionsController@delete');// delete Question from ID    
            $router->post('list/filter','QuestionsController@filterList');// get filtered list form syllabus, class and subject IDs
        });

        $router->group(['prefix' => 'answers'], function () use ($router) {
            //$router->get('list','AnswersController@index');//List of all Questions
            $router->post('store','AnswersController@store');//List of all Questions
            $router->post('update','AnswersController@update');// update Questions data from ID             
            $router->post('addBestAnswer','AnswersController@addBestAnswer');// update Questions data from ID             
        });

        $router->group(['prefix' => 'tuition'], function () use ($router) {
            $router->get('list','TutionsController@index');//List of all Tutions
            $router->post('store','TutionsController@store');//List of all Tutions
            $router->post('update','TutionsController@update');// update Tutions data from ID
            $router->post('delete','TutionsController@delete');// delete Classes data from ID             
            $router->post('tutorViewtution','TutionsController@tutorViewtution');// update Tutions data from ID             
            $router->post('studentViewtution','TutionsController@studentViewtution');// update Tutions data from ID
            $router->post('subscribe','TutionsController@subscribe');
            $router->post('unsubscribe','TutionsController@unsubscribe');
            $router->get('sessions/{type}','TutionsController@sessions');
            $router->post('sessions/complete','TutionsController@completeSession');
            $router->get('my/subscribed','TutionsController@mySubscribedTuitions');
            $router->post('add/student','TutionsController@bulkSubscribe');
            $router->post('students','TutionsController@getSubscribedStudents');
            $router->post('enable/student','TutionsController@enableStudent');
            $router->post('disable/student','TutionsController@disableStudent');
            $router->post('sessionStudents','TutionsController@sessionStudents');
            $router->post('takeAttendance','TutionsController@sessionAttendance');
            // $router->post('editAttendance','TutionsController@editAttendance');
        });

        $router->group(['prefix' => 'feedback'], function () use ($router) {
            $router->get('list','FeedbackController@index');//List of all Tutions
            $router->get('addedFeedbackList','FeedbackController@addedFeedbackList');//List of all Tutions
            $router->get('gotFeedbackList','FeedbackController@gotFeedbackList');//List of all Tutions
            $router->post('store','FeedbackController@store');//List of all Tutions
            //$router->post('update','TutionsController@update');// update Tutions data from ID                  
        });

        $router->group(['prefix' => 'student'], function () use ($router) {
            $router->post('filter/subject','StudentController@getStudents');
            $router->get('all/list','StudentController@getAllStudents');
        });

        $router->group(['prefix' => 'todos'], function () use ($router) {
            $router->get('list','TodoController@index');
            $router->post('store','TodoController@store');
            $router->post('markAsComplete','TodoController@markAsComplete');
            $router->post('markAsIncomplete','TodoController@markAsIncomplete');
            $router->post('delete','TodoController@delete');
        });

        $router->group(['prefix' => 'role'], function () use ($router) {
            $router->get('list',['as'=>'role_list','uses'=>'RolesController@index']);
            $router->post('store','RolesController@store');
            $router->post('update','RolesController@update');
            $router->post('delete','RolesController@delete');
        });

        $router->group(['prefix' => 'module'], function () use ($router) {
            $router->get('list',['as'=>'module_list','uses'=>'ModuleController@index']);
        });

        $router->group(['prefix' => 'message'], function () use ($router) {
            $router->get('fireEvent','MessageController@sendMessage');
        });

        $router->group(["prefix" => "chat"], function($router){
            $router->post('message/list','MessageController@memberMessageList');
            $router->post('message/send','MessageController@send');
            $router->post('message/deleteConversation','MessageController@deleteConversation');
            $router->post('group/create','MessageController@createGroup');
            $router->post('group/delete','MessageController@removeGroup');
            $router->post('message/history','MessageController@getMessageHistory');
            $router->post('message/mark/read','MessageController@markAsRead');
            $router->post('group/add/members','MessageController@addMember');
            $router->post('group/remove/members','MessageController@removeMember');
            $router->post('group/edit','MessageController@editGroup');
            $router->post('group/leave','MessageController@leaveGroup');
            $router->post('clear/messages','MessageController@clearMessages');
            $router->post('group_details','MessageController@groupDetails');
        });

        $router->group(['prefix' => 'event'], function () use ($router) {
            $router->post('list','EventController@index'); // Same route for Event List, Upcoming Events, Events History
            $router->post('store','EventController@store');
            $router->post('show','EventController@show');
            $router->post('update','EventController@update');
            $router->post('delete','EventController@destroy');
            $router->post('myEvents','EventController@myEvents');
            $router->post('attend','EventController@attendEvent');
            $router->post('makeEventFavourite','EventController@makeEventFavourite');
            $router->post('sendInvitation','EventController@sendInvitation');
            // $router->get('upComing','EventController@upComingEvents');
        });

        $router->group(['prefix' => 'timeline'], function () use ($router) {
            $router->get('list','TimelineController@index');
            $router->get('myTimeline','TimelineController@myTimeline');
            $router->get('all','TimelineController@all');
            $router->post('store','TimelineController@store');
            $router->post('show','TimelineController@show');
            $router->post('update','TimelineController@update');
            $router->post('delete','TimelineController@delete');
            $router->post('like','TimelineController@like');
            $router->post('favourite','TimelineController@favourite');
            $router->post('dislike','TimelineController@dislike');
            $router->post('abuse','TimelineController@abuseTimeline');
            $router->post('comment','TimelineController@comment');
            $router->post('repost','TimelineController@repost');
            $router->post('postToTimeline','TimelineController@postToTimeline');
        });

        $router->group(['prefix' => 'points'], function () use ($router) {
            $router->get('history','PointController@history');
            $router->post('transfer','PointController@transfer');
            $router->post('buy','PointController@buyPoints');
        });

        $router->group(['prefix' => 'questionBank'], function () use ($router) {
            $router->get('types','QuestionController@questionTypes');
            $router->post('store','QuestionController@store');
            $router->get('list','QuestionController@questionList');
            $router->post('filterQuestion','QuestionController@filterQuestion');
            $router->post('view','QuestionController@show');
            $router->post('delete','QuestionController@delete');
        });

        $router->group(['prefix' => 'assignment'], function () use ($router) {
            $router->get('section','AssignmentController@sections');
            $router->post('assignmentStudents','AssignmentController@assignmentStudents');
            $router->post('store','AssignmentController@store');
            $router->post('update','AssignmentController@update');
            $router->get('draftAssignment','AssignmentController@draftAssignment');
            $router->get('myAssignment','AssignmentController@myAssignment');
            $router->post('view','AssignmentController@view');
            $router->post('delete','AssignmentController@destroy');
            $router->post('publishAssignment','AssignmentController@publishAssignment');
            $router->post('submitAssignment','AssignmentController@submitAssignment');
            $router->post('studentSubmittedAssignment','AssignmentController@studentSubmittedAssignment');
            $router->post('assignmentAttemptRemainStudent','AssignmentController@assignmentAttemptRemainStudent');
            $router->post('viewSubmittedAssignment','AssignmentController@viewSubmittedAssignment');
            $router->post('deleteSubmittedAssignment','AssignmentController@deleteSubmittedAssignment');
            $router->post('assignmentMarking','AssignmentController@assignmentMarking');
        });

        $router->group(['prefix' => 'settings'], function () use ($router) {
            $router->get('get','SettingController@index');
            $router->post('store','SettingController@store');
            $router->group(["prefix" => "razorpay"], function($router){
                $router->post('store','SettingController@razorpayCredentialStore');
                $router->get('retrive','SettingController@retriveRazorpyaSetting');
            });
        });

        $router->group(["prefix" => "message"], function($router){
            $router->post('message/list','MessageController@memberMessageList');
            $router->post('message/send','MessageController@send');
            $router->post('message/deleteConversation','MessageController@deleteConversation');
            $router->post('group/create','MessageController@createGroup');
            $router->post('group/delete','MessageController@removeGroup');
            $router->post('message/history','MessageController@getMessageHistory');
            $router->post('message/mark/read','MessageController@markAsRead');
            $router->post('group/add/members','MessageController@addMember');
            $router->post('group/remove/members','MessageController@removeMember');
            $router->post('group/edit','MessageController@editGroup');
            $router->post('group/leave','MessageController@leaveGroup');
            $router->post('clear/messages','MessageController@clearMessages');
            $router->post('group_details','MessageController@groupDetails');
            $router->post('directMessage','MessageController@directMessage');
        });

        $router->group(["prefix" => "invoice"], function($router){
            $router->post('create','InvoiceController@create');
            $router->get('list','InvoiceController@allInvoices');
            $router->post('filter','InvoiceController@filter');
        });

        $router->group(["prefix" => "platform"], function($router){
            $router->get('switchAccount','ImporsonateController@switchAccount');
        });

        $router->group(["prefix" => "razorpay"], function($router){
            $router->post('createOrder','RazorpayPaymentController@createOrder');
        });

        /*############################## School FLow API ############################*/
        $router->group(['prefix' => 'school', 'middleware' => 'ApplySchoolUser'], function () use ($router) {
            $router->post('addSchool','SchoolController@addSchool');
            $router->get('list','SchoolController@index');
            $router->get('schoolCollaborationList','SchoolController@schoolCollaborationList');
            $router->get('schoolPlatformList','SchoolController@schoolPlatformList');
            $router->get('verifiedSchoolList','SchoolController@verifiedSchoolList');
            $router->post('verifySchool','SchoolController@verifySchool');
            $router->get('schoolDropdown','SchoolController@schoolDropdown');
            $router->post('joinSchool','SchoolController@joinSchool');

            $router->group(['prefix' => 'class'], function () use ($router) {
                $router->get('division','SchoolClassController@divisions');
                $router->post('createClass','SchoolClassController@createClass');
                $router->get('classList','SchoolClassController@classList');
                $router->post('classBySyllabus','SchoolClassController@classBySyllabus');
                $router->get('division/userSubjectsDropdown','SchoolClassController@userSubjectsDropdown'); // user subject list - if tutor own subject of all division, if student only division subjects
                $router->get('division/divisionList','SchoolClassController@divisionList');
                $router->post('division/divisionByClass','SchoolClassController@divisionByClass');
                $router->post('division/getUsersToAdd','SchoolClassController@getUsersToAdd');
                $router->post('division/addSubjectTeacher','SchoolClassController@addSubjectTeacher');
                $router->post('division/addDivisionStudent','SchoolClassController@addDivisionStudent');
                $router->post('division/addSubjectLeader','SchoolClassController@addSubjectLeader');
                $router->post('division/removeUser','SchoolClassController@removeUser');
                $router->post('division/enableDisableUser','SchoolClassController@enableDisableUser');
                $router->post('division/addDivisionTimetable','SchoolClassController@addDivisionTimetable');
                $router->post('division/editTimetable','SchoolClassController@editTimetable');
                $router->get('division/sessionList/{type}','SchoolClassController@sessionList');
                $router->post('division/filterSession','SchoolClassController@filterSession');
                $router->post('division/getAttedanceByDate','SchoolClassController@getAttedanceByDate');
                $router->post('division/takeSessionAttendance','SchoolClassController@takeSessionAttendance');
                $router->post('division/requestDivisionAccess','SchoolClassController@requestDivisionAccess');
            });

            $router->group(['prefix' => 'diary'], function () use ($router) {
                $router->post('divisionSubject','SchoolDiaryController@divisionSubject');
                $router->post('addDiary','SchoolDiaryController@addDiary');
                $router->post('updateDiary','SchoolDiaryController@updateDiary');
                $router->get('myDivisionBasicDetails','SchoolDiaryController@myDivisionBasicDetails');
                $router->post('myDiary','SchoolDiaryController@myDiary');
                $router->post('schoolDiary','SchoolDiaryController@schoolDiary');
                $router->post('shareInMessage','SchoolDiaryController@shareInMessage');
            });

        });
    });
});
