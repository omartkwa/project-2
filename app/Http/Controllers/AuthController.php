<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\AppNotification; 
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AuthController extends Controller
{ 


    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            // 'role'        => 'nullable|in:landlord,tenant,admin',
            'birthdate'      => 'nullable|date',
            'mobile'         => 'required|string|unique:users,mobile',
            'password'       => 'required|min:8',
            'profile_photo'  => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
            'id_photo'       => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
            'address'        => 'nullable|string',
            'card_type'      => 'required|in:visa,master,amex,discover',
            'card_number'    => 'required|string',
            'security_code'  => 'required|string',
            'expiry_date'    => 'required|date',
            'fcm_token'      => 'nullable|string', 
        ]);

        if(User::where('mobile', $data['mobile'])->first()){
            return response()->json([
                "status"  => 0,
                'data'    => [],
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

        $newUser = User::create($data);
        $token = $newUser->createToken('auth_token')->plainTextToken;


        try {
            $admins = User::where('role', 'admin')->get();

            if ($admins->count() > 0) {
$messaging = app('firebase.messaging');

                $notifTitle = 'تسجيل جديد';
                $notifBody  = "قام {$newUser->first_name} {$newUser->last_name} بالتسجيل، بانتظار الموافقة.";

                foreach ($admins as $admin) {
                    
                    AppNotification::create([
                        'user_id' => $admin->id,
                        'title'   => $notifTitle,
                        'body'    => $notifBody,
                        'is_read' => false,
                    ]);

                    if ($admin->fcm_token) {
                        try {
                            $notification = Notification::create($notifTitle, $notifBody);
                            
                            $message = CloudMessage::withTarget('token', $admin->fcm_token)
                                ->withNotification($notification)
                                ->withData(['user_id' => strval($newUser->id)]); 

                            $messaging->send($message);
                        } catch (\Throwable $e) {

                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        return response()->json([
            "status"  => 1,
            'message' => 'User registered successfully. Pending admin approval.',
            "data"    => [
                'user'=>$newUser,
                'token' => $token
            ] 
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
        $token = $user->createToken('auth_token')->plainTextToken;

    if (!$user->is_approved) {
        return response()->json([
            'status'=>2,
            'data'=>[
                'user'=>$user,
                'token'   => $token],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 200);
    }


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
        'mobile'         => 'nullable|string|unique:users,mobile,',
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
public function upgradeToLandlord( Request $request)
    {
        $user=$request->user();
       
      if(!$user) {

        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'Not authenticated'], 401);
    }

        if ($user->role !== 'tenant') {
            return response()->json([
                'status'=>0,
                'data'=>[],                
                'message' => 'User is not a tenant'
            ], 400);
        }

        $user->role = 'landlord';
        $user->save();

        return response()->json([
            'message' => 'User role updated to landlord successfully',
            'status'=>0,
            'data'=>['user'    => $user],
        ], 200);
    }

public function changePassword( Request $request)
{
 $user=$request->user();
       
      if(!$user) {

        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'Not authenticated'], 401);
    }
        $data = $request->validate([
        'password' => 'required|string',
        'password-new' => 'required|string',
        ]);
    if(!Hash::check($data['password'], $user->password)){
        return response()->json([
           'status'=>0,
            'data'=>[],
            'message' => 'Invalid credentials'], 401);
    }
        $data['password-new'] = Hash::make($data['password-new']);
        $user->password = $data['password-new'];
        $user->save();
return response()->json([
    'status'=>1,
    'message' => 'change password successful',
    "data"=>$user->password 
    ]);
}
public function verified( Request $request)
{
     $user=$request->user();

if(!$user){
        return response()->json([
            "status"=>0,
            'data'=>[],
            'message' => 'User not found'
        ], 403);
    }

 if (!$user->is_approved) {
        return response()->json([
            'status'=>2,
            'data'=>[  ],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 200);
}
 if ($user->is_approved) {
        return response()->json([
            'status'=>1,
            'data'=>[
                ],
            'message' => 'yes'
        ], 200);
}
}
public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

         $user=$request->user();

        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'status'=>1,
            'message' => 'FCM token saved or updated successfully',
            'data'=>[]
        ], 200);
    }
    public function indexNot(Request $request)
    {
        $user=$request->user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status'=>1,
            'message' => ' successfully',
            'data'=>['notifications' => $notifications]], 200);
    }

}


