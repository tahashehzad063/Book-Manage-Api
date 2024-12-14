<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
       
        $token = $user->createToken('LaravelAuthApp')->accessToken;
 
        return response()->json(['token' => $token], 200);
    }
    public function loginUser(Request $request) 
    {
        $input = $request->all();
       $user = Auth::attempt($input);
       $user = Auth::user();
       $token = $user->createToken('example')->accessToken;
// dd($token);
return response()->json(['token' => $token], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getUserDetail()
    {
        $user = Auth::guard('api')->user();
        return response(['data' => $user],200);
    }

    /**
     * Display the specified resource.
     */
    public function userLogout()
    {
        $accessToken = Auth::guard('api')->user()->token();
        
        \DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);
    
        $accessToken->revoke();
        // dd($accessToken);
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
    
    
    /**
     * Update the specified resource in storage.
     */
  
}
