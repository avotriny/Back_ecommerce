<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivraisonFaite extends Model
{
    use HasFactory;
     protected $table='livraison_faites';
    protected $fillable = [
        'commande_id',
        'images',
        'signature',
    ];

    public function commande(){
        return $this->belongsTo(Commande::class, 'commande_id');
    }
}
