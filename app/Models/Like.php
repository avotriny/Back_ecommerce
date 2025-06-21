<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'prod_id',
        'user_id',
        'like',
    ];

    protected $casts = [
        'like' => 'boolean',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'prod_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
