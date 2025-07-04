<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;
    protected $table='commandes';
    protected $fillable = [
        'prix_total',
        'nom',
        'email',
        'phone',
        'adresse',
        'status',
        'latitude',
        'longitude',
    ];

    public function lignes()
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function livraison(){
        return $this->hasMany(LivraisonFaite::class);
    }
}
