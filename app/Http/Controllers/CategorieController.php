<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function index()
    {
        try {
            $categories = Categorie::all();
            return response()->json(['categorie' => $categories,
        "status"=>200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des catégories', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $fields = $request->validate([
                'type_categorie' => 'required|string', 

            ]);
            $data = $fields;


            $categorie = Categorie::create($data);

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
