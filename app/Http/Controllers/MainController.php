<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;
use Auth;
use Carbon\Carbon;
use Image;

class MainController extends Controller
{
    /*
    Auth:Amar
    Created:25/11/2019
    Objective:Authentication
    */

    //function for register
    public function register(Request $request)
    {
    	$request->validate([
    		'name'=>'required|string',
    		'email'=>'required|string|email|unique:users',
    		'password'=>'required|string|confirmed'
    	]);

    	$user = new User();
    	$user->name = $request->name;
    	$user->email = $request->email;
    	$user->password = bcrypt($request->password);
    	$user->save();
    	return response(array(
    		'success'=>1,
    	));
    }
    

    //function for login
    public function login(Request $request)
    {
    	$request->validate([
    		'email'=>'required|string|email',
    		'remember_me'=>'boolean'
    	]);

    	$email = $request->email;
    	$password = $request->password;
    	if(!Auth::attempt(['email'=>$email,'password'=>$password]))
    	{
    		return response(array(
    			'success'=>0,
    			'message'=>'unauthorized',
    		));
    	}
    	
    	$user = $request-> user();
    	$tokenResult = $user->createToken('User personal access token');
    	$token = $tokenResult->token;
    	if($request->remember_me)
    	{
    		$token->expires_at = Carbon::now()->addDays(2);
    	}
    	$token->save();
    	return response(array(
    		'access_token'=>$tokenResult->accessToken,
    		'token_type'=>'Bearer',
    		'expires_at'=>Carbon::parse($tokenResult->token->expires_at)->toDateString(),
    	));
    }
    
    //function for logout
    public function logout(Request $request)
    {
    	if($request->user()->token()->revoke())
        {
    	   return response(array(
           'success'=>1,
    	   'message'=>'logout successfully',
    	   ));
        }
        
    }

    //function for multiple upload
    public function multipleUpload(Request $request)
    {
        request()->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($request->hasFile('file'))
        {
            foreach($request->file('file') as $file){
 
            //get filename with extension
            $filenamewithextension = $file->getClientOriginalName();
            // dd($filenamewithextension);
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
             //dd($filename);
            //get file extension
            $extension = $file->getClientOriginalExtension();
            //dd($extension);
            //filename to store
            $filenametostore = $filename.'_'.uniqid().'.'.$extension;
            // dd($filenametostore);
            Storage::put('public/file/'. $filenametostore, fopen($file, 'r+'));
            Storage::put('public/file/thumbnail/'. $filenametostore, fopen($file, 'r+'));
 
            //Resize image here
            $thumbnailpath = public_path('storage/file/thumbnail/'.$filenametostore);
            $img = Image::make($thumbnailpath)->resize(150, 150, function($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($thumbnailpath);
        }
        return response(array(
            'success'=>1,
        ));

        }
        else
        {
            return response(array(
                'success'=>0,
            ));
        }
    }
}
