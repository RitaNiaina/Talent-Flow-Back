<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use App\Models\ReponseCandidat;
use App\Models\Question;
use App\Models\User;
use App\Models\Note;
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReponseController extends Controller
{
    /**
     * Afficher toutes les réponses
     */
    public function index()
    {
        try {
            $reponses = Reponse::with(['question', 'candidat'])->get();
            return response()->json($reponses);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Créer une ou plusieurs réponses (max 4 par question)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // candidat_id est nullable
            'candidat_id' => 'nullable|exists:users,id',

            'reponses' => 'required|array',
            'reponses.*.question_id' => 'required|exists:questions,id',
            'reponses.*.contenu_reponse' => 'required|string',
            'reponses.*.reponse_correcte' => 'required|in:Vrai,Faux',
            'reponses.*.date_soumission' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Vérifier max 4 réponses par question
        $grouped = [];
        foreach ($validated['reponses'] as $r) {
            $grouped[$r['question_id']][] = $r;
        }

        foreach ($grouped as $questionId => $reponsesQuestion) {
            if (count($reponsesQuestion) > 4) {
                return response()->json([
                    'message' => "Vous ne pouvez pas ajouter plus de 4 réponses pour la question $questionId"
                ], 422);
            }

            // Vérifier qu’il n’y a qu’une seule réponse correcte (Vrai) par question
            $vraiCount = collect($reponsesQuestion)->where('reponse_correcte', 'Vrai')->count();
            if ($vraiCount > 1) {
                return response()->json([
                    'message' => "Une seule réponse correcte (Vrai) est autorisée pour la question $questionId"
                ], 422);
            }
        }

        // Création des réponses
        $created = [];
        foreach ($validated['reponses'] as $r) {
            $created[] = Reponse::create([
                // on récupère le candidat_id global s’il existe, sinon celui dans la réponse, sinon null
                'candidat_id' => $validated['candidat_id'] ?? $r['candidat_id'] ?? null,
                'question_id' => $r['question_id'],
                'contenu_reponse' => $r['contenu_reponse'],
                'reponse_correcte' => $r['reponse_correcte'],
                'date_soumission' => $r['date_soumission'] ?? null, // ✅ modifié ici
            ]);
        }

        foreach ($created as $r) {
            $r->load('question', 'candidat');
        }

        return response()->json([
            'message' => 'Réponses créées avec succès',
            'reponses' => $created
        ], 201);
    }

    /**
     * Afficher une réponse spécifique
     */
    public function show($id)
    {
        $reponse = Reponse::with(['question', 'candidat'])->find($id);
        if (!$reponse) {
            return response()->json(['message' => 'Réponse introuvable'], 404);
        }
        return response()->json($reponse);
    }

    /**
     * Mettre à jour une ou plusieurs réponses
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reponses' => 'required|array',
            'reponses.*.id' => 'required|exists:reponses,id',
            'reponses.*.contenu_reponse' => 'required|string',
            'reponses.*.reponse_correcte' => 'required|in:Vrai,Faux',
            'reponses.*.date_soumission' => 'nullable|date',
            'reponses.*.candidat_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updated = [];
        foreach ($request->reponses as $r) {
            $reponse = Reponse::find($r['id']);
            if ($reponse) {
                $reponse->update([
                    'contenu_reponse' => $r['contenu_reponse'],
                    'reponse_correcte' => $r['reponse_correcte'],
                    'date_soumission' => $r['date_soumission'] ?? null, // ✅ modifié ici
                    'candidat_id' => $r['candidat_id'] ?? null,
                ]);
                $reponse->load('question', 'candidat');
                $updated[] = $reponse;
            }
        }

        return response()->json([
            'message' => 'Réponses mises à jour avec succès',
            'reponses' => $updated
        ]);
    }

    /**
     * Supprimer une réponse
     */
    public function destroy($id)
    {
        $reponse = Reponse::find($id);
        if (!$reponse) {
            return response()->json(['message' => 'Réponse introuvable'], 404);
        }

        $reponse->delete();

        return response()->json(['message' => 'Réponse supprimée avec succès'], 200);
    }

    public function submitTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'candidat_id' => 'required|exists:users,id',
            'offre_id' => 'required|exists:offres,id',
            'reponses' => 'required|array',
            'reponses.*.question_id' => 'required|exists:questions,id',
            'reponses.*.reponse_id' => 'required|exists:reponses,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $validated = $validator->validated();
        $candidat_id = $validated['candidat_id'];
        $noteFinale = 0;
    
        foreach ($validated['reponses'] as $r) {
            $reponse = \App\Models\Reponse::find($r['reponse_id']);
    
            // Stocke la réponse du candidat
            ReponseCandidat::create([
                'candidat_id' => $candidat_id,
                'question_id' => $r['question_id'],
                'reponse_id' => $r['reponse_id'],
                'contenu_reponse' => $reponse->contenu_reponse,
                'reponse_correcte' => $reponse->reponse_correcte,
            ]);
    
            // Ajouter les points si correct
            if ($reponse->reponse_correcte === 'Vrai') {
                $question = $reponse->question;
                $noteFinale += $question->points_question;
            }
        }
    
        // Enregistrer la note finale
        $testId = \App\Models\Question::find($validated['reponses'][0]['question_id'])->test_id;
    
        Note::updateOrCreate(
            ['candidat_id' => $candidat_id, 'test_id' => $testId],
            ['note_candidat' => $noteFinale]
        );
    
        // Mettre à jour l’état de la candidature
        $candidature = Candidature::where('candidat_id', $candidat_id)
            ->where('offre_id', $validated['offre_id'])
            ->first();
    
        if ($candidature && $candidature->etat_candidature === 'en_attente') {
            $candidature->update(['etat_candidature' => 'en_cours']);
        }
    
        return response()->json([
            'message' => 'Test soumis avec succès',
            'note_candidat' => $noteFinale,
        ]);
    }
}
