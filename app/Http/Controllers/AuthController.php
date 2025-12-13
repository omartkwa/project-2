<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{ 

public function register(Request $request)
{
    $data = $request->validate([
        'first_name'     => 'required|string|max:100',
        'last_name'      => 'required|string|max:100',
        'role'           => 'required|in:landlord,tenant,admin',
        'birthdate'      => 'nullable|date',
        'mobile'         => 'required|string|unique:users,mobile',
        'password'       => 'required|min:8',
        'profile_photo'  => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        'id_photo'       => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        'address'        => 'required|string',
        'card_type'      => 'required|in:visa,master,amex,discover',
        'card_number'    => 'required|string',
        'security_code'  => 'required|string',
        'expiry_date'    => 'required|date',
    ]);
    

    if(User::where('mobile', $data['mobile'])->first()){
        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'User already exists'
        ], 403);
    }

    $data['password'] = Hash::make($data['password']);

    if ($request->hasFile('profile_photo')) {
        $data['profile_photo'] = base64_encode(file_get_contents($request->file('profile_photo')->getRealPath()));
    }

    if ($request->hasFile('id_photo')) {
        $data['id_photo'] = base64_encode(file_get_contents($request->file('id_photo')->getRealPath()));
    }

    $user = User::create($data);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        "status"=>1,
        'message' => 'User registered successfully. Pending admin approval.',
       "data"=>[
        'token'   => $token] 
    ], 201);
}
    public function login(Request $request){
    
  
    $data = $request->validate([
        'mobile'   => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('mobile', $data['mobile'])->first();

if(!$user){
        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'User not found'
        ], 403);
    }


    if (!$user || !Hash::check($data['password'], $user->password)) {
        return response()->json([
           'status'=>0,
            'data'=>[],
            'message' => 'Invalid credentials'], 401);
    }
    if (!$user->is_active) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'your accuont is block'
        ], 403);
    }
    if (!$user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
    'status'=>1,
    'message' => 'Login successful',
    "data"=>['user'=>$user,
    'token'   => $token] 
    ]);
}

 public function logout(Request $request)
    {
       $user = $request->user();
        if (! $user) {
        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'Not authenticated'], 401);
    }
    $user->currentAccessToken()->delete();

        return response()->json([
            'status'=>1,
            'data'=>[],
            'message' => 'Logged out successfully']);
    }

public function profile(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'Not authenticated'], 401);
    }

    return response()->json([
        'status'=>1,
        'message'=>'your profile',
        'data'=> ['user' => $user]
    ], 200);
}
public function updateProfile(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'Not authenticated'], 401);
    }

    $data = $request->validate([
        'first_name'     => 'nullable|string|max:100',
        'last_name'      => 'nullable|string|max:100',
        'birthdate'      => 'nullable|date',
        'mobile'         => 'nullable|string|unique:users,mobile,' . $user->id,
        'password'       => 'nullable|min:8',
        'profile_photo'  => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        'id_photo'       => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        'address'        => 'nullable|string',
        'card_type'      => 'nullable|in:visa,master,amex,discover',
        'card_number'    => 'nullable|string',
        'security_code'  => 'nullable|string',
        'expiry_date'    => 'nullable|date'
        
    ]);

    if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    }

    if ($request->hasFile('profile_photo')) {
        $data['profile_photo'] = base64_encode(file_get_contents($request->file('profile_photo')->getRealPath()));
    }

    if ($request->hasFile('id_photo')) {
        $data['id_photo'] = base64_encode(file_get_contents($request->file('id_photo')->getRealPath()));
    }

    $user->update($data);

    return response()->json([
       "status"=>1,
        'message' => 'Profile updated successfully',
        'data'=> ['user' => $user]

    ], 200);
}

}