<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Test::with('offre')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Valider les données
       $validator = Validator::make($request->all(), [
            
        'nom_test' => 'required|string|max:255',
        'duree_test' => 'required|date_format:H:i:s',
        'description_test' => 'required|string',
        'offre_id' => 'required|exists:offres,id',
       ]);

      // 2. Si la validation échoue, renvoyer les erreurs
      if ($validator->fails()) {
      return response()->json([
        'errors' => $validator->errors()
      ], 422);
       }

       // 3. Créer l'offre si tout est bon
       $test = Test::create($validator->validated());

       return response()->json($test, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $test = Test::with('offre')->find($id);

       if (!$test) {
        return response()->json(['message' => 'test n existe pas'], 404);
       }

    return response()->json($test);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $test = Test::find($id);

        if (!$test) {
            return response()->json(['message' => 'test introuvable'], 404);
        }
    
        // Validation
        $validator = Validator::make($request->all(), [
            'nom_test' => 'required|string|max:255',
            'duree_test' => 'required|date_format:H:i:s',
            'description_test' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Mise à jour
        $test->update($validator->validated());
    
        return response()->json(['message' => 'test mis à jour avec succès', 'test' => $test]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $test = Test::find($id);

    if (!$test) {
        return response()->json([
            'message' => 'test introuvable'
        ], 404);
    }

    $test->delete();

    return response()->json([
        'message' => 'test supprimé avec succès'
    ], 200);
    }
}
