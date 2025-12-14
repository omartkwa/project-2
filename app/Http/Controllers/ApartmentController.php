<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApartmentController extends Controller
{
 public function index(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'not auth'], 401);
    }

    if (! $user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);
    }

    $perPage = (int) $request->get('per_page', 15);
    $page = (int) $request->get('page', 1);

    $paginator = Apartment::with('images')
        ->withAvg('ratings', 'rating')     
        ->withCount('ratings')             
        ->where('is_approved', true)
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        "status" => 1,
        'message' => 'approved apartments',
        'data' => $paginator->items(),
    ]);
}
public function show(Request $request, $id)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'not auth'], 401);
    }

    if (! $user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);
    }

    try {
        $apartment = Apartment::with('images')
            ->withAvg('ratings', 'rating')   
            ->withCount('ratings')           
            ->findOrFail($id);

        return response()->json([
            'status' => 1,
            'data'=>['apartment' => $apartment],
            'message'=>'this apartment'
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 0,
            'message' => 'Apartment not found',
            'data'=>[]
        ], 404);
    }
}


    
    public function store(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'not auth'], 401);
    }

    if (!$user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);
    }

    if ($user->role !== 'landlord') {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'not landlord'], 403);
    }

    $validated = $request->validate([
        'state'            => 'required|string|max:255',
        'city'             => 'required|string|max:255',
        'street'           => 'nullable|string|max:255',
        'building_number'  => 'nullable|string|max:50',
        'rooms'            => 'required|integer|min:1',
        'floor'            => 'nullable|integer',
        'area'             => 'required|numeric|min:1',
        'has_furnish'      => 'required|boolean',
        'price'            => 'required|numeric|min:0',
        'description'      => 'nullable|string',

        'images'           => 'required|array|min:1',
        'images.*'         => 'file|image|max:8192',
    ]);

    $validated['user_id'] = $user->id;

    $apartment = Apartment::create($validated);

    foreach ($request->file('images') as $file) {
        $binary = file_get_contents($file->getRealPath());
        $base64 = base64_encode($binary);

        ApartmentImage::create([
            'apartment_id' => $apartment->id,
            'image_base64' => $base64,
        ]);
    }

    return response()->json([
        'status'=>1,
        'message'   => 'Apartment created successfully',
      'data'=>  ['apartment' => $apartment->load('images')]
    ], 201);
}

public function search(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'not auth'], 401);
    }

    if (! $user->is_approved) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Account awaiting admin approval. Please wait.'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'user_id'         => 'nullable|integer|exists:users,id',
        'state'           => 'nullable|string|max:255',
        'city'            => 'nullable|string|max:255',
        'street'          => 'nullable|string|max:255',
        'building_number' => 'nullable|string|max:50',
        'rooms'           => 'nullable|integer|min:1',
        'floor'           => 'nullable|integer',
        'min_area'        => 'nullable|numeric|min:0',
        'max_area'        => 'nullable|numeric|min:0',
        'has_furnish'     => 'nullable|boolean',
        'min_price'       => 'nullable|numeric|min:0',
        'max_price'       => 'nullable|numeric|min:0',
        'description'     => 'nullable|string',
        'q'               => 'nullable|string',
        'sort'            => 'nullable|in:price_asc,price_desc,latest,oldest',
        'per_page'        => 'nullable|integer|min:1|max:100',
        'page'            => 'nullable|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'=>0,
            'data'=>[],
            'message' => 'Invalid filter parameters',
            'errors'  => $validator->errors()
        ], 422);
    }

    $query = Apartment::with('images')
        ->withAvg('ratings', 'rating')   
        ->withCount('ratings')           
        ->where('is_approved', true);

    if ($request->filled('state'))           $query->where('state', 'like', '%' . $request->state . '%');
    if ($request->filled('city'))            $query->where('city', 'like', '%' . $request->city . '%');
    if ($request->filled('street'))          $query->where('street', 'like', '%' . $request->street . '%');
    if ($request->filled('building_number')) $query->where('building_number', 'like', '%' . $request->building_number . '%');
    if ($request->filled('description'))     $query->where('description', 'like', '%' . $request->description . '%');
    if ($request->filled('rooms'))           $query->where('rooms', (int) $request->rooms);
    if ($request->filled('floor'))           $query->where('floor', (int) $request->floor);
    if ($request->filled('min_area'))        $query->where('area', '>=', (float) $request->min_area);
    if ($request->filled('max_area'))        $query->where('area', '<=', (float) $request->max_area);
    if ($request->filled('has_furnish')) {
        $has = filter_var($request->has_furnish, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (!is_null($has)) $query->where('has_furnish', $has);
    }
    if ($request->filled('min_price'))       $query->where('price', '>=', (float) $request->min_price);
    if ($request->filled('max_price'))       $query->where('price', '<=', (float) $request->max_price);

    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function($sub) use ($q) {
            $sub->where('state', 'like', "%$q%")
                ->orWhere('city', 'like', "%$q%")
                ->orWhere('street', 'like', "%$q%")
                ->orWhere('building_number', 'like', "%$q%")
                ->orWhere('description', 'like', "%$q%");
        });
    }

    switch ($request->get('sort')) {
        case 'price_asc':  $query->orderBy('price', 'asc'); break;
        case 'price_desc': $query->orderBy('price', 'desc'); break;
        case 'oldest':     $query->orderBy('created_at', 'asc'); break;
        case 'latest':
        default:           $query->orderBy('created_at', 'desc'); break;
    }

    $perPage   = (int) $request->get('per_page', 10);
    $page      = (int) $request->get('page', 1);
    $paginator = $query->paginate($perPage, ['*'], 'page', $page)
                       ->appends($request->query());

    return response()->json([
        'status' => 1,
        'message'=>'your serch',
        'data'   => $paginator->items()
    ]);
}

}
