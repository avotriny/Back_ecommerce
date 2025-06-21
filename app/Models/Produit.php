<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcat_id', 'nom_prod', 'desc_prod', 'prix_prod', 'images',
        'poids_prod', 'origin_prod', 'stock_prod', 'promotion', 'prix_promo', 'couleur', 'taille', 'pointure', 'status'
    ];

    protected $casts = [
        'images' => 'array',      // Cast images as array
        'status' => 'boolean',    // Cast status as boolean
    ];

    public function subcategorie()
    {
        return $this->belongsTo(Subcategorie::class, 'subcat_id');
    }

    public function commande()
    {
        return $this->belongsToMany(Commande::class);
    }

        public function likes()
    {
        return $this->hasMany(Like::class, 'prod_id');
    }
}