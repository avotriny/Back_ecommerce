<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;
    protected $table='commandes';
    protected $fillable = [
        'prod_id',
        'prix_total',
        'quantite',
        'nom',
        'email',
        'phone',
        'adresse',
        'status',
        'latitude',
        'longitude',
    ];
    public function produit(){
        return $this->belongsTo(Produit::class, 'prod_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
