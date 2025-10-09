<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CandidatureController extends Controller
{
    /**
     * Afficher toutes les candidatures.
     */
    public function index(Request $request)
{
    $query = Candidature::with([
        'candidat:id,nom_utilisateur',
        'offre:id,titre_offre'
    ]);

    // Si un candidat_id est fourni, ne récupérer que ses candidatures
    if ($request->has('candidat_id')) {
        $query->where('candidat_id', $request->candidat_id);
    }

    $candidatures = $query->get();

    $result = $candidatures->map(function ($c) {
        return [
            'id' => $c->id,
            'cv_candidat' => $c->cv_candidat,
            'lettre_motivation' => $c->lettre_motivation,
            'date_postule' => $c->date_postule,
            'etat_candidature' => $c->etat_candidature,
            'candidat_id' => $c->candidat_id,
            'candidat_nom' => $c->candidat->nom_utilisateur ?? null,
            'offre_id' => $c->offre_id,
            'offre_titre' => $c->offre->titre_offre ?? null,
        ];
    });

    return response()->json($result);
}

    /**
     * Créer une nouvelle candidature avec upload CV/lettre.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cv_candidat' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'lettre_motivation' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'etat_candidature' => 'sometimes|in:en_attente,en_cours,acceptee,refusee',
            'candidat_id' => 'required|exists:users,id',
            'offre_id' => 'required|exists:offres,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $validated['date_postule'] = Carbon::now()->toDateString();
        $validated['etat_candidature'] = $validated['etat_candidature'] ?? 'en_attente';

        // Upload des fichiers
        if ($request->hasFile('cv_candidat')) {
            $validated['cv_candidat'] = $request->file('cv_candidat')->store('cvs', 'public');
        }

        if ($request->hasFile('lettre_motivation')) {
            $validated['lettre_motivation'] = $request->file('lettre_motivation')->store('lettres', 'public');
        }

        $candidature = Candidature::create($validated);
        $candidature->load(['candidat', 'offre']);

        return response()->json([
            'message' => 'Candidature créée avec succès',
            'candidature' => $candidature
        ], 201);
    }

    /**
     * Afficher une candidature spécifique.
     */
    public function show($id)
    {
        $candidature = Candidature::with(['candidat', 'offre.recruteur'])->find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        return response()->json($candidature);
    }

    /**
     * Mettre à jour une candidature.
     */
    public function update(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'cv_candidat' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'lettre_motivation' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'etat_candidature' => 'sometimes|in:en_attente,en_cours,acceptee,refusee',
            'candidat_id' => 'sometimes|exists:users,id',
            'offre_id' => 'sometimes|exists:offres,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Si nouveaux fichiers, uploader
        if ($request->hasFile('cv_candidat')) {
            $validated['cv_candidat'] = $request->file('cv_candidat')->store('cvs', 'public');
        }

        if ($request->hasFile('lettre_motivation')) {
            $validated['lettre_motivation'] = $request->file('lettre_motivation')->store('lettres', 'public');
        }

        $candidature->update($validated);

        return response()->json([
            'message' => 'Candidature mise à jour avec succès',
            'candidature' => $candidature
        ]);
    }

    /**
     * Supprimer une candidature.
     */
    public function destroy($id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature introuvable'], 404);
        }

        $candidature->delete();

        return response()->json(['message' => 'Candidature supprimée avec succès'], 200);
    }
}
