<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Afficher toutes les questions
     */
    public function index()
    {
        return response()->json(
            Question::with('test')->get()
        );
    }

    /**
     * Ajouter une ou plusieurs questions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_id' => 'required|exists:tests,id',
            'questions' => 'required|array|min:1',
            'questions.*.intitule_question' => 'required|string',
            'questions.*.type_question' => 'required|in:QCM', 
            'questions.*.points_question' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Création des questions
        $created = [];
        foreach ($validated['questions'] as $q) {
            $created[] = Question::create([
                'intitule_question' => $q['intitule_question'],
                'type_question' => 'QCM', 
                'points_question' => $q['points_question'],
                'test_id' => $validated['test_id'], 
            ]);
        }

        foreach ($created as $q) {
            $q->load('test');
        }

        return response()->json([
            'message' => 'Questions créées avec succès',
            'questions' => $created
        ], 201);
    }

    /**
     * Afficher une question spécifique
     */
    public function show($id)
    {
        $question = Question::with('test')->find($id);

        if (!$question) {
            return response()->json(['message' => 'question n\'existe pas'], 404);
        }

        return response()->json($question);
    }

    /**
     * Mettre à jour une ou plusieurs questions
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.intitule_question' => 'required|string',
            'questions.*.type_question' => 'required|in:QCM', 
            'questions.*.points_question' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updated = [];
        foreach ($request->questions as $q) {
            $question = Question::find($q['id']);
            if ($question) {
                $question->update([
                    'intitule_question' => $q['intitule_question'],
                    'type_question' => 'QCM', 
                    'points_question' => $q['points_question'],
                ]);
                $question->load('test');
                $updated[] = $question;
            }
        }

        return response()->json([
            'message' => 'Questions mises à jour avec succès',
            'questions' => $updated
        ]);
    }

    /**
     * Supprimer une question
     */
    public function destroy($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'question introuvable'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'question supprimée avec succès'], 200);
    }
}
