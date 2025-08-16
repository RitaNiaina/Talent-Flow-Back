<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Question::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Valider les données
       $validator = Validator::make($request->all(), [
            
        'intitule_question' => 'required|string',
        'type_question' => 'required|string|max:255',
        'points_question' => 'required|integer',
        'test_id' => 'required|exists:tests,id',
       ]);

      // 2. Si la validation échoue, renvoyer les erreurs
      if ($validator->fails()) {
      return response()->json([
        'errors' => $validator->errors()
      ], 422);
       }

       // 3. Créer l'offre si tout est bon
       $question = Question::create($validator->validated());

       return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $question = Question::find($id);

        if (!$question) {
        return response()->json(['message' => 'question n existe pas'], 404);
    }
        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'question introuvable'], 404);
        }
    
        // Validation
        $validator = Validator::make($request->all(), [
            'intitule_question' => 'required|string',
            'type_question' => 'required|string|max:255',
            'points_question' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Mise à jour
        $question->update($validator->validated());
    
        return response()->json(['message' => 'question mis à jour avec succès', 'question' => $question]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $question = Question::find($id);

    if (!$question) {
        return response()->json([
            'message' => 'question introuvable'
        ], 404);
    }

    $question->delete();

    return response()->json([
        'message' => 'question supprimé avec succès'
    ], 200);
    
    }
}
