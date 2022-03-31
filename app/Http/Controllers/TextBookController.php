<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\TextBook;
use App\Http\Resources\TextBookResource;
use App\Rules\ClassExists;
use App\Rules\SyllabusExists;
use Illuminate\Support\Facades\Validator;

class TextBookController extends Controller
{
    /**
     * Text Book Record List.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $textBookData = TextBook::Orderby('created_at', 'desc')->get();
            return TextBookResource::collection($textBookData)->additional([ "error" => false, "message" => "Textbook list retrived successfully"]);
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
     * Add New Data insert Text Book.
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
            'book_name'=>'required|max:100',
            'description'=>'required|max:300',
            'resource_type'=>'required',
            // 'external_link'=>'required_if:resource_type,==,external_link',
            'external_link'=>'required_if:resource_type,external_link',
            'attachment'=>'required_if:resource_type,==,attachment|mimes:pdf|max:2048',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);
        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            // $textBookStore = TextBook::create($request->all());
            $textBookStore = TextBook::create($request->except(['attachment', 'image']));
            
            if($request->has('attachment')){
                // $attachmentName = 'attachments/textbook/' . time() . '.' .$request->attachment->extension();
                // $request->attachment->storeAs('public', $attachmentName);
                $file = $this->uploadFile($request->attachment,'attachments/textbook/');
                if($file != false){
                    $textBookStore->attachment = $file;
                }
            }
            if($request->has('image')){
                // $picName = 'images/textbook/' . time() . '.' .$request->image->extension();
                // $request->image->storeAs('public', $picName);
                // $textBookStore->image = $picName;
                $image = $this->uploadFile($request->image,'images/textbook/');
                if($image != false){
                    $textBookStore->image = $image;
                }
            }

            if($textBookStore->save()) {
                $this->_response['data'] = $textBookStore;
                $this->setResponse(false, 'Textbook added successfully in the database.');
                return response()->json($this->_response); 
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * View Text Book Record.
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
            $textBookView = TextBook::find($request->id);
            
            if($textBookView) {
                return (new  TextBookResource($textBookView))->additional(["error" => false, "message" => 'Text Book View data for Show ']);
            } else {
                $this->setResponse(true, 'TextBook Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * .Edit Text Book Record 
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
            $textBookEdit =  TextBook::find($request->id);
            if($textBookEdit) {
                return (new TextBookResource($textBookEdit))->additional(["error" => false, "message" => 'Text Book data for edit']);
            } else {
                $this->setResponse(true, 'Text Book Data not found.');
                return response()->json($this->_response);
            }
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Update Record Text Book .
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'subject_id'=>'required',
            'syllabus_id'=>'required',
            'class_id'=>'required',
            'book_name'=>'required',
            'description'=>'required',
            'resource_type'=>'required',
            // 'external_link'=>'filled',
            // 'attachment'=>'filled|mimes:doc,jpg,jpeg,png,docx,csv,pdf,txt,ppt,pptm,pptx,xls,xlsx|max:2048',
            // 'image' => 'filled|image|mimes:jpg,jpeg,png|max:2048'
            'external_link'=>'required_if:resource_type,external_link',
            'attachment'=>'nullable:resource_type,attachment|mimes:pdf|max:2048',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'

        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }
        try {
            $textBookUpdate = TextBook::find($request->id);
            $textBookUpdate->update($request->except(['attachment', 'image']));

            if($request->has('attachment')){
                // $attachmentName = 'attachments/textbook/' . time() . '.' .$request->attachment->extension();
                // $request->attachment->storeAs('public', $attachmentName);
                // $textBookUpdate->update(['attachment' => $attachmentName]); 
                
                $file = $this->uploadFile($request->attachment,'attachments/textbook/');
                if($file != false){
                    $textBookUpdate->attachment = $file;
                }
            }
            if($request->has('image')){
                // $picName = 'images/textbook/' . time() . '.' .$request->image->extension();
                // $request->image->storeAs('public', $picName);
                // $textBookUpdate->update(['image' => $picName]);
                $image = $this->uploadFile($request->image,'images/textbook/');
                if($image != false){
                    $textBookUpdate->image = $image;
                }
            }
            if($textBookUpdate->update()){
                $this->setResponse(false,'TextBook data updated successfully in the database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    /**
     * Text Book Delete Record.
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
            $textBookDelete = TextBook::find($request->id);
            if($textBookDelete) {
                $textBookDelete->delete();
                $this->setResponse(false, 'TextBook deleted from database.');
                return response()->json($this->_response);
            } 
            
        } catch (\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }
    }

    // textbook filter
    public function filterList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'syllabus_id' => 'exists:syllabuses,_id',
            'class_id' => 'exists:classes,_id',
            'subject_id' => 'exists:subjects,_id',
        ]);

        if ($validator->fails()) {
            $this->setResponse(true, $validator->errors()->all());
            return response()->json($this->_response, 400);
        }

        try{
            $bookName = trim($request->book_name);
            // $externalLink = trim($request->external_link);
            $textBooks = TextBook::whereSyllabus($request->syllabus_id)
                        ->whereClass($request->class_id)
                        ->whereSubject($request->subject_id)
                        ->when($bookName,function($query, $bookName){
                            return $query->where('book_name','like','%' . $bookName .'%');
                        })
                        // ->when($externalLink,function($query, $externalLink){
                        //     return $query->where('external_link','like','%' . $externalLink .'%');
                        // })
                        ->Orderby('created_at', 'desc')->get();
            return TextBookResource::collection($textBooks)->additional([ "error" => false, "message" => "Textbook list retrived successfully"]);
            
        } catch(\Exception $e) {
            $this->setResponse(true, $e->getMessage());
            return response()->json($this->_response, 500);
        }

    }
}
