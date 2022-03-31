<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $_response = ["data" => [], "error" => false, "message" => null, "error_code" => null];

    protected function setResponse( $status = true, $message = null, $errorCode = null)
    {
        $this->_response['error'] = $status;
        $this->_response['message'] = $message;
        $this->_response['error_code'] = $errorCode;
    }

    protected function registerUser($email)
    {
        $user = User::create(["email" => $email]);
        $imageName = 'user_images/' . getUniqueStamp() . '.png';
        $path = 'public/' . $imageName;
        // $img = CreateDPWithLetter::create($email);
        // Storage::put($path, $img->encode());
        $user->image = $imageName;
        $user->is_verified = false;        
        $user->save();
        return $user;
    }
    
    protected function addFileAttachments($attachments, $path = "no-path/attachments/")
    {
        $this->resetFiles();
        $path = Str::finish($path ,'/');
        if (!empty($attachments)) {
            foreach ($attachments as $key => $attachment) {
                if (!empty($attachment)) {
                    $filePath = $this->uploadSingleFile($attachment, $path, $key);
                    $this->_files[] = $filePath;
                }
            }
        }

        return $this->_files;
    }
    
    private function uploadSingleFile($file,  $path = "no-path/attachments/" , $key = null)
    {
        $fileFullName = $file->getClientOriginalName();
        $fileName = str_replace(' ','_',pathinfo($fileFullName,PATHINFO_FILENAME));
        $filePath = $path . $fileName . '-' . getUniqueStamp() . $key .'.' . $file->extension();
        $file->storeAs('public', $filePath);
        return $filePath;
    }

    protected function removeFileAttachment(string $fileUrls)
    {
        $filesDeleted = [];
        $deletableFiles = explode(',',$fileUrls);
        if(!empty($deletableFiles)){
            foreach ($deletableFiles as $fileUrl)
            {
                $file = $this->removeSingleFile($fileUrl);
                $filesDeleted[] = $file;
            }
        }

        return $filesDeleted;
    }
    
    private function removeSingleFile(string $url)
    {
        $file = str_replace(url('storage').'/', '', $url);
        if(Storage::disk('public')->delete('public/'.$file) || Storage::delete('public/'.$file)){
            return $file;
        }
    }

    protected function resetFiles()
    {
        $this->_files = [];
    }

    protected function uploadFile($file,  $path = "no-path/attachments/")
    {
        $path = Str::finish($path ,'/');

        if(!empty($file))
        {
            return $this->uploadSingleFile($file, $path);
        }

        return false;
    }

    protected function removeFile($file)
    {
        if($file){
            return $this->removeSingleFile($file);
        }

        return false;
    }
}