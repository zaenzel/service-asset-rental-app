<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESS = 'process';
    const STATUS_REJECT = 'reject';
    const STATUS_APPROVE = 'approve';

    const PAYMENT_STATUS_NOT_YET_PAID = "not yet paid";
    const PAYMENT_STATUS_DP = 'dp';
    const PAYMENT_STATUS_KEEL = 'keel';

    protected $fillable = [
        'user_id',
        'product_id',
        'start_booking_date',
        'end_booking_date',
        'booking_duration',
        'price_per_hour',
        'price_total',
        'status',
        'payment_status',
        'payment_total'
    ];

    public function setProductIdAttribute($value)
    {
        $this->attributes['product_id'] = $value;
    }

    public function setPriceTotalAttribute($value)
    {
        $this->attributes['price_total'] = $value;
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'start_booking_date' => 'datetime:Y-m-d H:i:s',
            'end_booking_date' => 'datetime:Y-m-d H:i:s',
        ];
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
