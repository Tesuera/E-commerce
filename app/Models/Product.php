<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unique_id',
        'slug',
        'price',
        'stock',
        'description',
        'rating',
        'category_id',
        'user_id'
    ];

    public $with = ['photos'];

    public function photos() {
        return $this->hasMany(ProductPhoto::class);
    }
}
