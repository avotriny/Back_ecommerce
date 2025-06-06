<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categorie;
use App\Models\Subcategorie;

class SubCategorieController extends Controller
{
    public function index()
    {
        try {
        $subcategories = Subcategorie::with('categorie')->get(); // relation en minuscule
        return response()->json([
            'status' => 200,
            'subcategories' => $subcategories
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Erreur lors de la récupération des sous-catégories',
            'error' => $e->getMessage()
        ]);
    }
    }

    public function store(Request $request)
    {
        try {
            $fields = $request->validate([
                'name_categorie' => 'required|string', 
                'cat_id' => 'required|integer', 

            ]);
            $data = $fields;


            $categorie = Subcategorie::create($data);

            return response()->json([
                'status' => 200,
                'success' => true,
                'categorie' => $categorie
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
