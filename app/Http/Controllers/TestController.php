<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Offre;
use App\Models\Question;
use App\Models\Reponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $validator = Validator::make($request->all(), [
            'nom_test' => 'required|string|max:255',
            'duree_test' => 'required|date_format:H:i:s',
            'description_test' => 'required|string',
            'offre_id' => 'required|exists:offres,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

        $validator = Validator::make($request->all(), [
            'nom_test' => 'required|string|max:255',
            'duree_test' => 'required|date_format:H:i:s',
            'description_test' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
            return response()->json(['message' => 'test introuvable'], 404);
        }

        $test->delete();

        return response()->json(['message' => 'test supprimé avec succès'], 200);
    }

    public function getByOffre($offre_id)
    {
        $test = Test::with(['questions.reponses'])->where('offre_id', $offre_id)->first();

        if (!$test) {
            return response()->json(['message' => 'test n existe pas'], 404);
        }

        return response()->json($test);
    }

    public function storeWithQuestions(Request $request)
    {
        $request->validate([
            'nom_test' => 'required|string',
            'description_test' => 'required|string',
            'duree_test' => 'required|string',
            'offre_id' => 'required|integer|exists:offres,id',
            'questions' => 'required|array|min:1',
            'questions.*.intitule_question' => 'required|string',
            'questions.*.points_question' => 'required|integer|min:1',
            'questions.*.reponses' => 'required|array|min:1',
            'questions.*.reponses.*.contenu_reponse' => 'required|string',
            'questions.*.reponses.*.correcte' => 'required|in:Vrai,Faux',
        ]);

        DB::beginTransaction();

        try {
            $test = Test::create([
                'nom_test' => $request->nom_test,
                'description_test' => $request->description_test,
                'duree_test' => $request->duree_test,
                'offre_id' => $request->offre_id,
            ]);

            foreach ($request->questions as $q) {
                $question = Question::create([
                    'intitule_question' => $q['intitule_question'],
                    'type_question' => $q['type_question'] ?? 'QCM',
                    'points_question' => $q['points_question'],
                    'test_id' => $test->id,
                ]);

                foreach ($q['reponses'] as $r) {
                    Reponse::create([
                        'contenu_reponse' => $r['contenu_reponse'],
                        'reponse_correcte' => $r['correcte'],
                        'question_id' => $question->id,
                        'candidat_id' => null, // garde null
                        // date_soumission supprimé
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Test avec questions et réponses créé avec succès',
                'test_id' => $test->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du test',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateWithQuestions(Request $request, $id)
    {
        $test = Test::find($id);
        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        $request->validate([
            'nom_test' => 'required|string',
            'description_test' => 'required|string',
            'duree_test' => 'required|string',
            'offre_id' => 'required|integer|exists:offres,id',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|integer|exists:questions,id',
            'questions.*.intitule_question' => 'required|string',
            'questions.*.points_question' => 'required|integer|min:1',
            'questions.*.reponses' => 'required|array|min:1',
            'questions.*.reponses.*.id' => 'nullable|integer|exists:reponses,id',
            'questions.*.reponses.*.contenu_reponse' => 'required|string',
            'questions.*.reponses.*.correcte' => 'required|in:Vrai,Faux',
        ]);

        DB::beginTransaction();

        try {
            $test->update([
                'nom_test' => $request->nom_test,
                'description_test' => $request->description_test,
                'duree_test' => $request->duree_test,
                'offre_id' => $request->offre_id,
            ]);

            $existingQuestionIds = $test->questions()->pluck('id')->toArray();

            foreach ($request->questions as $q) {
                if (!empty($q['id']) && in_array($q['id'], $existingQuestionIds)) {
                    $question = Question::find($q['id']);
                    $question->update([
                        'intitule_question' => $q['intitule_question'],
                        'points_question' => $q['points_question'],
                    ]);
                } else {
                    $question = Question::create([
                        'intitule_question' => $q['intitule_question'],
                        'type_question' => 'QCM',
                        'points_question' => $q['points_question'],
                        'test_id' => $test->id,
                    ]);
                }

                $existingReponseIds = $question->reponses()->pluck('id')->toArray();

                foreach ($q['reponses'] as $r) {
                    if (!empty($r['id']) && in_array($r['id'], $existingReponseIds)) {
                        $reponse = Reponse::find($r['id']);
                        $reponse->update([
                            'contenu_reponse' => $r['contenu_reponse'],
                            'reponse_correcte' => $r['correcte'],
                        ]);
                    } else {
                        Reponse::create([
                            'contenu_reponse' => $r['contenu_reponse'],
                            'reponse_correcte' => $r['correcte'],
                            'question_id' => $question->id,
                            'candidat_id' => null,
                            // date_soumission supprimé
                        ]);
                    }
                }

                $reponseIdsToKeep = collect($q['reponses'])->pluck('id')->filter()->toArray();
                Reponse::where('question_id', $question->id)
                    ->whereNotIn('id', $reponseIdsToKeep)
                    ->delete();
            }

            $questionIdsToKeep = collect($request->questions)->pluck('id')->filter()->toArray();
            Question::where('test_id', $test->id)
                ->whereNotIn('id', $questionIdsToKeep)
                ->delete();

            DB::commit();

            return response()->json([
                'message' => 'Test avec questions et réponses mis à jour avec succès',
                'test_id' => $test->id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du test',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showWithQuestions($id)
    {
        $test = Test::with(['questions.reponses'])->find($id);

        if (!$test) {
            return response()->json(['message' => 'Test introuvable'], 404);
        }

        return response()->json($test);
    }
}
