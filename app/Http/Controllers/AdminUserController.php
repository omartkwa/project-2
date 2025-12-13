<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    
    public function approve(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can perform this action'
            ], 403);
        }
        $user = User::findOrFail($id);
        $user->is_approved = true;
        $user->save();

        return response()->json([
            'message' => 'User approved successfully',
            'user' => $user
        ]);
    }

    public function disactive(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can perform this action'
            ], 403);
        }
        $user = User::findOrFail($id);
        $user->is_active = false;
        $user->save();

        return response()->json([
            'message' => 'User disactive successfully',
            'user' => $user
        ]);
    }
    
    public function active(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can perform this action'
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->is_active = true;
        $user->save();

        return response()->json([
            'message' => 'User active successfully',
            'user' => $user
        ]);
    }
    public function upgradeToLandlord($id, Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can perform this action'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->role !== 'tenant') {
            return response()->json([
                'message' => 'User is not a tenant'
            ], 400);
        }

        $user->role = 'landlord';
        $user->save();

        return response()->json([
            'message' => 'User role updated to landlord successfully',
            'user'    => $user
        ], 200);
    }
    public function pendingUsers(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Only admin can view pending users'
        ], 403);
    }

    $pending = User::where('is_approved', false)->get();

    return response()->json([
        'message' => 'Pending users fetched successfully',
        'users'   => $pending
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
}
