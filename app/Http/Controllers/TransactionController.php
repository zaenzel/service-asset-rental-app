<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()->with('product');

        $status = $request->query('status');
        $user_id = $request->query('user_id');

        $transactions->when($status, function ($query) use ($status) {
            return $query->where('status', $status);
        });

        $transactions->when($user_id, function ($query) use ($user_id) {
            return $query->where('user_id', $user_id);
        });

        return new TransactionResource(
            true,
            'List Data Transactions',
            $transactions->paginate(10)
        );
    }

    public function show($id)
    {
        $transaction = Transaction::where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction is not found'
            ], 404);
        }

        return new TransactionResource(true, 'Detail Transaction', $transaction,);
    }

    public function store(Request $request)
    {
        $todayDate = Carbon::today()->toDateString();

        $rules = [
            'user_id' => 'required|integer',
            'product_id' => 'required|integer',
            'start_booking_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:' . $todayDate,
            'end_booking_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:start_booking_date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }


        $existingBooking = Transaction::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->where('status', Transaction::STATUS_PENDING)
            ->first();

        if ($existingBooking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah memiliki booking yang sedang diproses untuk produk ini.'
            ], 400);
        }

        $dateBooked = Transaction::where('product_id', $request->product_id)
            // ->where('status', '==', 'approve')
            // ->where(function ($query) use ($request) {
            //     $query->whereBetween('start_booking_date', [$request->start_booking_date, $request->end_booking_date])
            //         ->orWhereBetween('end_booking_date', [$request->start_booking_date, $request->end_booking_date])
            //         ->orWhere(function ($subquery) use ($request) {
            //             $subquery->where('start_booking_date', '<=', $request->start_booking_date)
            //                 ->where('end_booking_date', '>=', $request->end_booking_date);
            //         });
            // })
            ->where('status', 'approve')
            ->where(function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery->whereBetween('start_booking_date', [$request->start_booking_date, $request->end_booking_date])
                        ->orWhereBetween('end_booking_date', [$request->start_booking_date, $request->end_booking_date])
                        ->orWhere(function ($innerQuery) use ($request) {
                            $innerQuery->where('start_booking_date', '<=', $request->start_booking_date)
                                ->where('end_booking_date', '>=', $request->end_booking_date);
                        });
                });
            })
            ->first();

        if ($dateBooked) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk ini sudah dibooking pada rentang tanggal yang dipilih.'
            ], 400);
        }

        $startBookingDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_booking_date);
        $endBookingDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_booking_date);

        $totalHours = $startBookingDate->diffInHours($endBookingDate);

        $product = Product::find($request->product_id);
        $totalPrice = $product->price_per_hour * $totalHours;


        $transaction = Transaction::create([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'slug' => $product->slug,
            'start_booking_date' => $request->start_booking_date,
            'end_booking_date' => $request->end_booking_date,
            'booking_duration' => $totalHours,
            'price_per_hour' => $product->price_per_hour,
            'price_total' => $totalPrice,
        ]);

        return new TransactionResource(true, 'Trasaksi berhasil dibuat', $transaction,);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'status' => 'in:pending,process,reject,approve',
            'payment_status' => 'in:not yet paid,dp,lunas',
            'payment_total' => 'integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 400);
        }

        // Cek apakah status diubah menjadi approve
        if ($request->status == 'approve') {
            // Cari transaksi yang pending dan bersinggungan dengan tanggal transaksi ini
            $startBookingDate = $transaction->start_booking_date;
            $endBookingDate = $transaction->end_booking_date;

            $pendingTransactions = Transaction::where('product_id', $transaction->product_id)
                ->where('status', 'pending')
                ->where(function ($query) use ($startBookingDate, $endBookingDate) {
                    $query->whereBetween('start_booking_date', [$startBookingDate, $endBookingDate])
                        ->orWhereBetween('end_booking_date', [$startBookingDate, $endBookingDate])
                        ->orWhere(function ($subquery) use ($startBookingDate, $endBookingDate) {
                            $subquery->where('start_booking_date', '<=', $startBookingDate)
                                ->where('end_booking_date', '>=', $endBookingDate);
                        });
                })
                ->get();

            // Ubah status transaksi yang ditemukan menjadi reject
            foreach ($pendingTransactions as $pendingTransaction) {
                $pendingTransaction->status = 'reject';
                $pendingTransaction->save();
            }
        }


        $transaction->fill($data);

        try {
            $transaction->save();
            return new TransactionResource(true, 'Data berhasil di ubah', $transaction,);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction is not found'
            ], 404);
        }

        $transaction->delete();

        return new TransactionResource(true, 'Data berhasil dihapus', null);
    }
}
