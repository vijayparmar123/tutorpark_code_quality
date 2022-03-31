<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\NoteBook;
use App\Models\Subject;
use App\Models\userDetails;
use App\Models\User;
use App\Http\Resources\NoteBookResource;
use App\Http\Resources\TutorDropdownResource;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
use Illuminate\Support\Facades\Validator;

class NoteBookController extends Controller
{
    /**
     * Note Book Record List.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $noteBookData = NoteBook::where('user_id',auth()->user()->id)->Orderby('created_at', 'desc')->get();
            return NoteBookResource::collection($noteBookData)->additional([ "error" => false, "message" => null]);
        } catch (Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Add New Data insert Note Book.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required', 'exists:classes,_id', new SyllabusExists],
            'subject_id' => ['required', 'exists:subjects,_id', new ClassExists],
            'tutor_id' => 'required',
            'notebook_name'=>'max:100',
            'description'=>'required',
            'image' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $request->request->add(['user_id' => auth()->user()->id]);
            $noteBookStore = NoteBook::create($request->except(['image']));
            
            if($request->has('image')){
                $image = $this->uploadFile($request->image,'images/notebook/');
                if($image != false){
                    $noteBookStore->image = $image;
                }
            }

            if($noteBookStore->save()) {
                //$this->_response['data'] = $noteBookStore;
                $this->setResponse(false, 'Notebook added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Update User Data */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'required|exists:syllabuses,_id',
            'class_id' => ['required', 'exists:classes,_id', new SyllabusExists],
            'subject_id' => ['required', 'exists:subjects,_id', new ClassExists],
            'notebook_name'=>'max:100',
            'description'=>'required',
            'image' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            $request->request->add(['user_id' => auth()->user()->id]);
            $notebookUpdate = NoteBook::find($request->id);
           
            $notebookUpdate->update($request->except(['image']));

            if($request->has('image')){
                $picName = 'images/notebook/' . getUniqueStamp() . '.' .$request->image->extension();
                $request->image->storeAs('public', $picName);
                $notebookUpdate->update(['image' => $picName]);
            }
            if($notebookUpdate){
                $this->_response['data'] = $notebookUpdate;
                $this->setResponse(false,'NoteBook data updated successfully in the database.');
                return response()->json($this->_response);
            }
 
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Delete Notebook Record */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            $notebookData = NoteBook::findOrfail($request->id);

            if(!empty($notebookData))
            {
                $notebookData->delete();
                $this->setResponse(false, 'Notebook deleted successfully from database.');
                return response()->json($this->_response);
            }

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /* Tutor list from subject id */
    public function subjectTutors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'filled||exists:subjects,_id'
        ]);
 
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
         
        try {
            if($request->has('subject_id'))
            {
                $subjectTutorIds = Subject::select('tutors_can_teach')->where(["_id"=>$request->subject_id])->first();
                $canTeach = (!empty($subjectTutorIds->tutors_can_teach)? $subjectTutorIds->tutors_can_teach : array());
                $tutorData = User::whereHas('details', function ($query) use($canTeach) {
                    $query->whereIn('_id', $canTeach);
                })->get();
            }else{
                $tutorData = User::role('tutor')->get();
            }
            
            if(!empty($tutorData))
            {
                return TutorDropdownResource::collection($tutorData)->additional([ "error" => false, "message" => null]);
            }

        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /*Update Timeline Data */
    // public function addTimeline(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'timeline_data' => 'required',
    //     ]);
 
    //     if ($validator->fails()) {
    //         $this->setResponse(true, $validator->errors()->all());
    //         return response()->json($this->_response, 400);
    //     }
         
    //     try {

    //         $timeline = $request->has('timeline_data') ? $request->timeline_data : [];
    //         if(!empty($timeline))
    //         {
    //             foreach($timeline as $timeline){
    //                 $notebookUpdate = NoteBook::find($request->id);
    //                 if($notebookUpdate){
    //                     $notebookUpdate->timeline()->create($timeline);
    //                 }
    //             }
    //         }
    //         $notebookUpdate->save();
    //         $this->_response = $notebookUpdate;
    //         $this->setResponse(false, 'Updated Successfully.');
    //         return response()->json($this->_response);
 
    //     } catch (\Exception $e) {
    //         $this->setResponse(true, $e->getMessage());
    //         return response()->json($this->_response, 500);
    //     }
    // }

}
