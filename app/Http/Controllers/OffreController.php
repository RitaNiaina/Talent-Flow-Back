<?php

namespace App\Http\Controllers;
use App\Models\Offre;
use App\Models\User;
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OffreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Offre::with(['recruteur', 'candidatures', 'tests', 'competences'])->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // 1️⃣ Validation
    $validator = Validator::make($request->all(), [
        'titre_offre' => 'required|string|max:255',
        'description_offre' => 'required|string',
        'date_publication' => 'required|date',
        'date_expiration' => 'nullable|date|after_or_equal:date_publication',
        'statut_offre' => 'in:ouvert,fermé,en_attente',
        'recruteur_id' => 'required|exists:users,id',
        'type_offre' => 'required|string|max:255',
        'lieu_offre' => 'required|string|max:255',
        'competences' => 'nullable|array',
        'competences.*' => 'exists:competences,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $validator->validated();

    // 2️⃣ Vérifier que le recruteur est bien un recruteur
    $utilisateur = User::with('role')->find($data['recruteur_id']);
    if (!$utilisateur || !$utilisateur->role || $utilisateur->role->type_role !== 'Recruteur') {
        return response()->json(['error' => 'Cet utilisateur n\'est pas un recruteur'], 403);
    }

    // 3️⃣ Créer l'offre
    $offre = Offre::create([
        'titre_offre' => $data['titre_offre'],
        'description_offre' => $data['description_offre'],
        'date_publication' => $data['date_publication'],
        'date_expiration' => $data['date_expiration'] ?? null,
        'statut_offre' => $data['statut_offre'] ?? 'en_attente',
        'recruteur_id' => $data['recruteur_id'],
        'type_offre' => $data['type_offre'],
        'lieu_offre' => $data['lieu_offre'],
    ]);

    // 4️⃣ Attacher les compétences sélectionnées
    if (!empty($data['competences'])) {
        $offre->competences()->sync($data['competences']);
    }

    // 5️⃣ Retourner l’offre avec toutes les relations
    return response()->json(
        $offre->load(['competences', 'recruteur', 'candidatures', 'tests']),
        201
    );
}


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $offre = Offre::with(['recruteur', 'candidatures', 'tests', 'competences'])->find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre introuvable'], 404);
        }

        return response()->json($offre);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'titre_offre' => 'sometimes|required|string|max:255',
            'description_offre' => 'sometimes|required|string',
            'date_publication' => 'sometimes|required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_publication',
            'statut_offre' => 'in:ouvert,fermé,en_attente',
            'type_offre' => 'sometimes|required|string|max:255',
            'lieu_offre' => 'sometimes|required|string|max:255',
            'competences' => 'nullable|array',
            'competences.*' => 'exists:competences,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Mise à jour de l'offre principale
        $offre->update($data);

        // Mise à jour des compétences si fournies
        if (array_key_exists('competences', $data)) {
            $offre->competences()->sync($data['competences'] ?? []);
        }

        return response()->json([
            'message' => 'Offre mise à jour avec succès',
            'offre' => $offre->load(['competences', 'recruteur', 'candidatures', 'tests']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre introuvable'], 404);
        }

        // Supprimer les relations pivot (compétences)
        $offre->competences()->detach();

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès'], 200);
    }

    /**
 * Afficher les détails complets d'une offre (tests, questions, réponses)
 */
public function getDetailsComplet($id)
{
    $offre = Offre::with([
        'recruteur',
        'competences',
        'tests.questions.reponses'
    ])->find($id);

    if (!$offre) {
        return response()->json(['message' => 'Offre introuvable'], 404);
    }

    return response()->json($offre);
}



public function hasApplied($offreId, $candidatId)
{
    // Vérifie que l'offre existe
    $offre = \App\Models\Offre::find($offreId);
    if (!$offre) {
        return response()->json(['error' => 'Offre non trouvée'], 404);
    }

    // Vérifie si le candidat a déjà postulé
    $hasApplied = \App\Models\Candidature::where('offre_id', $offreId)
        ->where('candidat_id', $candidatId)
        ->exists();

    return response()->json(['hasApplied' => $hasApplied]);
}


}
