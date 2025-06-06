<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['type_categorie'];


    public function subcategorie()
    {
        return $this->hasMany(Subcategorie::class);
    }
}
