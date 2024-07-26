<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Product extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'price_per_hour',
        'slug',
        'category',
        'description',
        'image',
    ];

    protected $table = 'products';

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => url('/storage/product/' . $image),
        );
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:m:s',
            'updated_at' => 'datetime:Y-m-d H:m:s'
        ];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
