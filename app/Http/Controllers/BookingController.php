<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
     public function store(Request $request, $apartmentId)
{
    try {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'no auth'], 401);
        }
        if (!$user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);}

        $apartment = Apartment::findOrFail($apartmentId);

        $start = Carbon::parse($request->start_date)->toDateString();
        $end = Carbon::parse($request->end_date)->toDateString();

        $hasExistingBooking = Booking::where('user_id', $user->id)
            ->where('apartment_id', $apartment->id) 
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            })
            ->exists();

        if ($hasExistingBooking) {
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'you already have a booking for this apartment during this date range',
            ], 409);
        }

    $start_date = Carbon::parse($request->start_date)->startOfDay();
    $end_date  = Carbon::parse($request->end_date)->startOfDay();
    $days  = $start_date->diffInDays($end_date);
    if ($days <= 0) $days = 1;

    $requiredAmount = (float) ($apartment->price ) * $days;
    $currentBalance = (float) ($user->account);

 if ($currentBalance < $requiredAmount) {
        return response()->json([
            'status'=>0,
            'data'=>[
            'required_amount' => $requiredAmount,
            'current_balance' => $currentBalance],
            'message' => 'Insufficient balance to make this booking',
            
        ], 402);
    }
    

        $booking = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user->id,
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'pending',
        ]);

        return response()->json([
            'status'=>1,
            'message' => 'your request has been submitted to the owner. please wait for approval',
           'data'=> ['booking' => $booking]
        ], 201);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'apartment is not exist',
        ], 404);
    }
}

    
    public function update(Request $request, $bookingId)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' =>'no auth'], 401);
        }
        if (!$user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);}

        $booking = Booking::findOrFail($bookingId);

        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'this request not ',
                                    'status'=>0,
                                    'data'=>[]], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'it can not be modefied'], 400);
        }

        $booking->start_date = Carbon::parse($request->start_date)->toDateString();
        $booking->end_date = Carbon::parse($request->end_date)->toDateString();
       
        $booking->save();


        return response()->json([
            'message' => 'update request',
            'data'=>['booking' => $booking],
            'status'=>1
        ]);
    }

 
    public function cancel(Request $request, $bookingId)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'no auth',
                                    'status'=>0,
                                    'data'=>[],
        ], 401);
        }
        if (!$user->is_approved) {
        return response()->json([
           'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);}

        $booking = Booking::findOrFail($bookingId);

        if ($booking->user_id !== $user->id) {
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'this request not your'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'is not panding'], 400);
        }

        $booking->status='cancelled';
        $booking->save();


        return response()->json([
            'status'=>1,
            'data'=>[],
            'message' => 'cancled']);
    }

    
    public function ownerIndex(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
            'status'=>0,
            'data'=>[],    
            'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
            'apartment_id' => 'nullable|integer|exists:apartments,id',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $perPage = $request->input('per_page', 20);

        $query = Booking::with(['user', 'apartment'])
            ->whereHas('apartment', function ($q) use ($user, $request) {
                $q->where('user_id', $user->id);

                if ($request->filled('apartment_id')) {
                    $q->where('id', $request->input('apartment_id'));
                }
            });

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy('created_at', 'desc');

        $paginator = $query->paginate($perPage);

        $items = $paginator->items();

        return response()->json($items);
    }

   
    public function approve(Request $request, $id)
{
    $user = $request->user();

          if (! $user) {
            return response()->json([
            'status'=>0,
            'data'=>[],    
            'message' => 'Unauthenticated'], 401);
        }

    $booking = Booking::with('apartment')->find($id);

    if (! $booking) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Booking not found.'], 404);
    }

    if ($booking->apartment->user_id !== $user->id) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Unauthorized.'], 403);
    }

    DB::beginTransaction();

    try {
        $booking = Booking::where('id', $booking->id)->lockForUpdate()->first();

        if ($booking->status === 'approved') {
            DB::rollBack();
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Booking already approved.'], 400);
        }

        $start = $booking->start_date;
        $end = $booking->end_date;
        $apartmentId = $booking->apartment_id;




 $start_date = Carbon::parse($booking->start_date)->startOfDay();
        $end_date   = Carbon::parse($booking->end_date)->startOfDay();

        $days = $start_date->diffInDays($end_date);
        if ($days <= 0) $days = 1;

        $pricePerDay = (float) ($booking->apartment->price );
        $requiredAmount = $pricePerDay * $days;

        $renter = User::where('id', $booking->user_id)->lockForUpdate()->first();

        if (! $renter) {
            DB::rollBack();
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Renter not found.'], 404);
        }

        $currentBalance = (float) ($renter->account ?? 0);

        if ($currentBalance < $requiredAmount) {
            DB::rollBack();
            return response()->json([
                'status'=>0,
                'message' => 'Renter has insufficient balance to pay for this booking.',
                'data'=>['required_amount' => $requiredAmount,
                'current_balance' => $currentBalance]

            ], 402);
        }


        $overlapping = Booking::where('apartment_id', $apartmentId)
            ->where('id', '<>', $booking->id)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->where('status', 'pending') 
            ->lockForUpdate()
            ->get();

        Booking::where('apartment_id', $apartmentId)
            ->where('id', '<>', $booking->id)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->where('status', 'pending')   
            ->update([
                'status' => 'rejected',
                'updated_at' => now()
            ]);
  $renter->account = $currentBalance - $requiredAmount;
  $user->account= $user->account+ floor($requiredAmount*90/100);
  $admin=User::findOrFail(1);
  $admin->account= $admin->account+ ceil($requiredAmount*10/100);
        $user->save();
        $admin->save();
        $renter->save();
        $booking->status = 'approved';
        $booking->save();

        DB::commit();

        return response()->json([
            'message' => 'Booking approved. Pending overlapping bookings rejected.',
            'approved_booking_id' => $booking->id,
            'rejected_pending_count' => $overlapping->count(),
            'rejected_pending_ids' => $overlapping->pluck('id')->values()
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Error approving booking.',
        ], 500);
    }
}

   
    public function reject(Request $request, $id)
{
    $user = $request->user();

    if (! $user) {
            return response()->json([
            'status'=>0,
            'data'=>[],    
            'message' => 'Unauthenticated'], 401);
        }

    $booking = Booking::with('apartment')->find($id);

    if (! $booking) {
        return response()->json([
             'status'=>0,
            'data'=>[],
            'message' => 'Booking not found.'], 404);
    }

    if ($booking->apartment->user_id !== $user->id) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Unauthorized. You are not the owner of this apartment.'], 403);
    }

    DB::beginTransaction();

    try {
        $booking = Booking::where('id', $booking->id)->lockForUpdate()->first();

        if (! $booking) {
            DB::rollBack();
            return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Booking not found after locking.'], 404);
        }

        if ($booking->status !== 'pending') {
            DB::rollBack();
            return response()->json([
                'message' => 'Cannot reject booking. Only bookings with status "pending" can be rejected.',
                'data'=>['current_status' => $booking->status],
                'status'=>0,
            ], 400);
        }

        $previousStatus = $booking->status;
        $booking->status = 'rejected';
        $booking->save();

        DB::commit();

        return response()->json([
            'message' => 'Booking rejected successfully.',
            'booking_id' => $booking->id,
            'previous_status' => $previousStatus,
            'new_status' => $booking->status
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Error rejecting booking.',
        ], 500);
    }
}
   public function userBookings(Request $request)
{
    $user = $request->user();

    if (! $user) {
            return response()->json([
            'status'=>0,
            'data'=>[],    
            'message' => 'Unauthenticated'], 401);
        }

    $bookings = Booking::with('apartment')
        ->where('user_id', $user->id)
        ->orderBy('start_date', 'desc')
        ->simplePaginate(10); 

    return response()->json([
        'message' => 'User bookings fetched successfully.',
        'data' => [$bookings->items(),],
        'status'=>1   

    ], 200);
}
}
