<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public $status;
    public $message;
    public $resource;

    public function __construct($status, $message, $resource)
    {
        parent::__construct($resource);
        $this->status  = $status;
        $this->message = $message;
    }
    
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}

// public function _fullBookedChecker(Request $request)
// {
//     $product = Product::find($request->product_id);

//     if (!$product) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Data tidak ditemukan'
//         ], 404);
//     }

//     $startDate = Carbon::parse($request->start_booking_date);
//     $endDate = Carbon::parse($request->end_booking_date);

//     $existingBooking = Transaction::where('product_id', $product->id)
//         ->where('status', 'pending')
//         ->where(function ($query) use ($startDate, $endDate) {
//             $query->whereBetween('start_booking_date', [$startDate, $endDate])
//                 ->orWhereBetween('end_booking_date', [$startDate, $endDate])
//                 ->orWhere(function ($subquery) use ($startDate, $endDate) {
//                     $subquery->where('start_booking_date', '<', $startDate)
//                         ->where('end_booking_date', '>', $endDate);
//                 });
//         })->exists();

//     if ($existingBooking) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Tanggal sudah dibooking'
//         ], 400);
//     }

//     return true;
// }
