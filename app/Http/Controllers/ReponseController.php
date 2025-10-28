<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use App\Models\ReponseCandidat;
use App\Models\Question;
use App\Models\User;
use App\Models\Note;
use App\Models\Offre;
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
                'candidat_id' => $validated['candidat_id'] ?? $r['candidat_id'] ?? null,
                'question_id' => $r['question_id'],
                'contenu_reponse' => $r['contenu_reponse'],
                'reponse_correcte' => $r['reponse_correcte'],
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

    /**
     * Soumettre un test par un candidat
     */
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

        if (!$reponse) {
            return response()->json([
                'message' => "Réponse {$r['reponse_id']} introuvable."
            ], 404);
        }

        // Vérifier si le candidat a déjà répondu à cette question
        $existing = ReponseCandidat::where('candidat_id', $candidat_id)
            ->where('question_id', $r['question_id'])
            ->first();

        if ($existing) {
            continue; // ignorer ou remplacer selon la logique souhaitée
        }

        // Stocke la réponse du candidat avec la date de soumission
        ReponseCandidat::create([
            'candidat_id' => $candidat_id,
            'question_id' => $r['question_id'],
            'reponse_id' => $r['reponse_id'],
            'contenu_reponse' => $reponse->contenu_reponse,
            'reponse_correcte' => $reponse->reponse_correcte,
            'date_soumission' => now(),
        ]);

        // Ajouter les points si correct
        if ($reponse->reponse_correcte === 'Vrai') {
            $question = $reponse->question;
            $noteFinale += $question->points_question;
        }
    }

    // Enregistrer la note finale
    $firstQuestionId = $validated['reponses'][0]['question_id'];
    $testId = \App\Models\Question::find($firstQuestionId)->test_id;

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

    /**
 * Récupérer toutes les réponses soumises par tous les candidats
 */
public function getAllReponsesCandidats()
{
    try {
        // Charger toutes les relations imbriquées
        $reponses = \App\Models\ReponseCandidat::with([
            'question.test.offre', // 🔗 permet d'accéder à l'offre
            'reponse',
            'candidat'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reponses->isEmpty()) {
            return response()->json(['message' => 'Aucune réponse trouvée.'], 404);
        }

        // 🔹 Regrouper par candidat + offre
        $grouped = $reponses->groupBy(function ($item) {
            $offreId = optional($item->question->test->offre)->id ?? 'inconnu';
            return $item->candidat_id . '_' . $offreId;
        })->map(function ($items) {
            $first = $items->first();
            return [
                'candidat' => $first->candidat,
                'offre' => $first->question->test->offre,
                'reponses' => $items->values(),
            ];
        })->values();

        return response()->json($grouped);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



}
