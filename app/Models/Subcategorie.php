<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategorie extends Model
{
    use HasFactory;
    protected $table = 'subcategories';
    protected $fillable = ['name_categorie', 'cat_id'];

public function categorie()
{
    return $this->belongsTo(Categorie::class, 'cat_id', 'id');
}
public function produits()
    {
        return $this->hasMany(Produit::class);
    }
}
