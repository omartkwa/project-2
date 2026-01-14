<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    
    public function approve(Request $request, $id)
    {
        $admin=$request->user();
        if (! $admin ||$admin->role !== 'admin') {
            return response()->json([
                'status'=>0,
                'data'=>[],
                'message' => 'Only admin can perform this action'
            ], 403);
        }
         $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'=>0,
                'data'=>[],
                'message' => 'User not found'
            ], 404);
        }        
        $user->is_approved = true;
        $user->save();

        return response()->json([
            'message' => 'User approved successfully',
           'data'=> ['user' => $user],
           'status'=>1
        ]);
    }

    public function disactive(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'data'=>[],
                'status'=>0,
                'message' => 'Only admin can perform this action'
            ], 403);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status'=>0,
                'data'=>[],
                'message' => 'User not found'
            ], 404);
        }       
        $user->is_active = false;
        $user->save();

        return response()->json([
            'message' => 'User disactive successfully',
           'data'=>[ 'user' => $user],
           'status'=>1
        ]);
    }
    
    public function active(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'data'=>[],
                'status'=>0,
                'message' => 'Only admin can perform this action'
                        ], 403);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status'=>0,
                'data'=>[],
                'message' => 'User not found'
            ], 404);
        }       
        $user->is_active = true;
        $user->save();

        return response()->json([
            'message' => 'User disactive successfully',
           'data'=>[ 'user' => $user],
           'status'=>1
        ]);
    }
    public function upgradeToLandlord($id, Request $request)
    {
        if ($request->user()->role !== 'admin') {
             return response()->json([
                'data'=>[],
                'status'=>0,
                'message' => 'Only admin can perform this action'
                        ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'=>0,
                'data'=>[],
                'message' => 'User not found'
            ], 404);
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
            'status'=>1,
            'data'=>['user'    => $user],
        ], 200);
    }

public function showUser(Request $request,$id)
{

    if ($request->user()->role !== 'admin') {
             return response()->json([
                'data'=>[],
                'status'=>0,
                'message' => 'Only admin can perform this action'
                        ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'=>0,
                'data'=>[],
                'message' => 'User not found'
            ], 404);
        }

    return response()->json([
        'status'=>1,
        'message'=>'user',
        'data'=> ['user' => $user]
    ], 200);}
public function pendingUsers(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Only admin can view pending users'
        ], 403);
    }

    $pending = User::where('is_approved', false)->get();

    return response()->json([
        'status'=>1,
         'data'=>[    
             'users'   => $pending
                ],
        'message' => 'Pending users fetched successfully',
    ], 200);
}

public function approveApartment($id, Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admin can approve apartments'], 403);
        }

        $apartment = Apartment::with('images', 'user')->find($id);

        if (! $apartment) {
            return response()->json(['message' => 'Apartment not found'], 404);
        }

        if ($apartment->is_approved) {
            return response()->json(['message' => 'Apartment is already approved', 'apartment' => $apartment], 200);
        }

        $apartment->is_approved = true;
        $apartment->save();


        return response()->json([
            'message' => 'Apartment approved successfully',
            'apartment' => $apartment->fresh('images', 'user')
        ], 200);
    }

    public function pendingApartments(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only admin can view pending apartments'], 403);
        }

        $pending = Apartment::with(['images', 'user'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Pending apartments fetched successfully',
            'apartments' => $pending
        ], 200);
    }
     public function stats(Request $request)
    {
        $admin = $request->user();

        if (! $admin || $admin->role !== 'admin') {
            return response()->json([
                'status' => 0,
                'data'=>[],
                'message' => 'Unauthorized'
            ], 403);
        }
        return response()->json([
            'status'  => 1,
            'message' => 'Admin dashboard statistics',
            'data'    => [ 
            'users_count'      => (User::count()-1),
            'users-panding'=>User::where('is_approved',false)->count(),
            'apartment-aprov'=>Apartment::where('is_approved',true)->count(),
            'apartment-reject'=>Apartment::where('is_approved',false)->count(),
            'tenants_count'    => User::where('role', 'tenant')->count(),
            'landlords_count'  => User::where('role', 'landlord')->count(),
            'admin_balance'    => $admin->account,
            'bookings_count'   => Booking::where('status', 'approved')->count(),
            'bookings_panding'   => Booking::where('status', 'panding')->count(),

 ]
        ]);
    }
}
