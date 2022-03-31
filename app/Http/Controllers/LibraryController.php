<?php

namespace App\Http\Controllers;

use App\Models\Library;
use App\Models\SharedLibraryItem;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Jobs\PostComment;
use App\Http\Controllers\Controller;
use App\Http\Resources\LibraryResource;
use App\Http\Resources\LibraryDropdownResource;
use App\Http\Resources\SharedLibraryResource;
// use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Validator;

class LibraryController extends Controller
{
    /**
     * Disply Library Record List
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $libraryData = Library::Orderby('created_at', 'desc')->get();
            return LibraryResource::collection($libraryData)->additional([ "error" => false, "message" => 'Here is all Library data']);
        } catch (Exception $e) {
        $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/**
     * Disply Library Record List
     *
     * @return \Illuminate\Http\Response
     */
    public function libraryDropdown()
    {
        try {
            $libraryData = Library::Orderby('created_at', 'desc')->get();
            return LibraryDropdownResource::collection($libraryData)->additional([ "error" => false, "message" => 'Here is all Library data']);
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
     * New Library insert.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required|min:3',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'class_id' => 'required|exists:classes,_id',
            'description'=>'required|min:10|max:500',
            'image'=>'required|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'attachment'=>'nullable|mimes:jpg,bmp,png,jpeg,svg,mp4,mp3,pdf,doc,csv,xlsx,xls,docx,ppt,odt,ods,odp|max:100000',

        ]);
        // dd($validator->errors()->all());
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
             $libraryStore= Library::create($request->except(['image','attachment']));

            if ($request->has('image')) {

                $libraryImage = $this->uploadFile($request->image, 'library/image');
                if ($libraryImage != false) {
                    $libraryStore->image = $libraryImage;
                }
            }

            if ($request->has('attachment')) {

                $libraryAttachment = $this->uploadFile($request->attachment, 'library/attachment');
                if ($libraryAttachment != false) {
                    $libraryStore->attachment = $libraryAttachment;
                }
            }

            if($libraryStore->save()) {
                $this->_response['data'] = $libraryStore;
                $this->setResponse(false, 'Library added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    public function addAttachmentImages($attachments, $libraryStore)
    {
        if (!empty($attachments)) {
            foreach ($attachments as $key => $attachments) {
                if (!empty($attachments)) {
                    $filePath = 'attachments/library' . getUniqueStamp() . $key . '.' . $attachments->extension();
                    $attachments->storeAs('public',$filePath);
                    $libraryStore->push('attachments', $filePath);
                }
            }
        }
    }

    /**
     * View Library.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:libraries,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $library =  Library::find($request->id);
            
            if($library) {
                return (new LibraryResource($library))->additional(["error" => false, "message" => 'Library data for Show']);
            } else {
                $this->setResponse(true, 'Library Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Edit Library.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:libraries,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $library =  Library::find($request->id);
            
            if($library) {
                return (new LibraryResource($library))->additional(["error" => false, "message" => 'Library data for edit']);
            } else {
                $this->setResponse(true, 'Library Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Library.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:libraries,_id',
            'name'=>'required|min:3',
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'subject_id' => 'required|exists:subjects,_id',
            'class_id' => 'required|exists:classes,_id',
            'description'=>'required|min:10|max:500',
            'image'=>'nullable|mimes:jpg,bmp,png,jpeg,svg|max:100000',
            'attachment'=>'nullable|mimes:jpg,bmp,png,jpeg,svg,mp4,mp3,pdf,doc,csv,xlsx,xls,docx,ppt,odt,ods,odp|max:100000',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $libraryStore = Library::find($request->id);
            $libraryStore->update($request->except(['image','attachment']));

            if ($request->has('image')) {

                $libraryImage = $this->uploadFile($request->image, 'library/image');
                if ($libraryImage != false) {
                    $libraryStore->image = $libraryImage;
                }
            }

            if ($request->has('attachment')) {

                $libraryAttachment = $this->uploadFile($request->attachment, 'library/attachment');
                if ($libraryAttachment != false) {
                    $libraryStore->attachment = $libraryAttachment;
                }
            }

            if($libraryStore->update()) {
                $this->_response['data'] = $libraryStore;
                $this->setResponse(false, 'Library data updated successfully in the database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Delete Library
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:libraries,_id',
        ]);
        
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $libraryDelete =  Library::find($request->id);
            if($libraryDelete) {
                $libraryDelete->delete();
                $this->_response['data'] = $libraryDelete;
                $this->setResponse(false, 'Library deleted from database.');
                return response()->json($this->_response);
            } 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
    // Filter Library
    public function filterList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'exists:syllabuses,_id',
            'subject_id' => 'exists:subjects,_id',
            'class_id' => 'exists:classes,_id',
            'created_by' => 'exists:users,_id'
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        
        try{
            $syllabusID = $request->syllabus_id;
            $classID = $request->class_id;
            $subjectID = $request->subject_id;
            $createdBy = $request->created_by;

            $libraryData = Library::when($syllabusID, function($query, $syllabusID){
                    return $query->where(["syllabus_id"=>$syllabusID]);
                })
                ->when($classID, function($query, $classID){
                    return $query->where(["class_id"=>$classID]);
                })
                ->when($subjectID, function($query, $subjectID){
                    return $query->where(["subject_id"=>$subjectID]);
                })
                ->when($createdBy, function($query, $createdBy){
                    return $query->where(["created_by"=>$createdBy]);
                })
                ->get();
            
            return LibraryResource::collection($libraryData)->additional([ "error" => false, "message" => null]);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }

    }

    /**
     * Comments for specific Library
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:libraries,_id',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            $library =  Library::find($request->id);
            
            $comment = [
                'body' => $request->comment,
                'datetime' => Carbon::now(),
                'commented_by' => auth()->user()->id
            ];
            
            /** Post comment **/
            dispatch(new PostComment($library,$comment));

            $this->setResponse(false, 'Comment posted successfully.');
            return response()->json($this->_response, 200);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
    
	/**
     * Share Library item with friends
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function shareLibrary(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'library_id' => 'required|exists:libraries,_id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try {
            
			foreach($request->user_ids as $userId)
			{
				$share = SharedLibraryItem::create([
					'library_id' => $request->library_id,
					'share_to' => $userId,
					'share_by' => auth()->user()->_id,
					'created_by' => auth()->user()->_id,
				]); 
			}

            $this->setResponse(false, 'Library item shared successfully.');
            return response()->json($this->_response, 200);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
	
	/**
     * List of library items shared with me
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function mySharedLibrary(Request $request)
    {
        try {
            
			$sharedLibraryList = SharedLibraryItem::where(['share_to' => auth()->user()->_id])->get();
            return SharedLibraryResource::collection($sharedLibraryList)->additional([ "error" => false, "message" => 'List of all library shared with me.']);

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }
}