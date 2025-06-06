<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produit;
use Illuminate\Support\Facades\Validator;

class ProduitController extends Controller
{
    public function index()
    {
            $produits = Produit::with('subcategorie.categorie')->get();
            return response()->json(['produit' => $produits,
        "status"=>200]);
            
    }

    public function store(request $request){
        if ($request->has('images') && $request->input('images') === '') {
            $request->request->remove('images');
        }

        $validator = Validator::make($request->all(),[
           
            'nom_prod' => 'required|string',
                'desc_prod' => 'required|string',
                'prix_prod' => 'required|string',
                'stock_prod' => 'required|string',
                'poids_prod' => 'nullable|string',
                'promotoin' => 'nullable|string',
                'origin_prod' => 'nullable|string',
                'couleur' => 'nullable|string',
                'taille' => 'nullable|string',
                'pointure' => 'nullable|string',
                'subcat_id' => 'required|numeric',
            'images'=>'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages(),
            ]); 
        }else{ 
      
       $produit = new Produit();
       $produit->subcat_id = $request->input('subcat_id');
       $produit->nom_prod = $request->input('nom_prod');
       $produit->desc_prod = $request->input('desc_prod');
       $produit->origin_prod = $request->input('origin_prod');
       $produit->prix_prod = $request->input('prix_prod');
       $produit->stock_prod = $request->input('stock_prod');
       $produit->promotion = $request->input('promotion');
       $produit->couleur = $request->input('couleur');
       $produit->taille = $request->input('taille');
       $produit->pointure = $request->input('pointure');
       $produit->poids_prod = $request->input('poids_prod');
       $produit->status = true;

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $filename = time() . '_' . $images->getClientOriginalName();
            $destinationPath = public_path('uploads/produit');
    
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
    
            $images->move($destinationPath, $filename);
            $produit->images = 'uploads/produit/' . $filename;
        }

        if($request->hasFile('promotion')){
            $produit->prix_promo = ($request->input('promotion') * $request->input('prix_prod'));
        }
       

       $produit->save();
       return response()->json([
        'status'=>200,
        'message'=>"avec success",
        "produit"=>$produit
    ]); 
      }
    }


}
