<?php

namespace App\Http\Controllers;

use App\Models\Competence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompetenceController extends Controller
{
    /**
     * Afficher toutes les compétences
     */
    public function index()
    {
        return response()->json(Competence::with('offres')->get());
    }

    /**
     * Créer une nouvelle compétence
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nom_competence' => 'required|string|max:255|unique:competences,nom_competence',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Création
        $competence = Competence::create($validator->validated());

        return response()->json($competence, 201);
    }

    /**
     * Afficher une compétence spécifique
     */
    public function show($id)
    {
        $competence = Competence::with('offres')->find($id);

        if (!$competence) {
            return response()->json(['message' => 'Compétence introuvable'], 404);
        }

        return response()->json($competence);
    }

    /**
     * Mettre à jour une compétence
     */
    public function update(Request $request, $id)
    {
        $competence = Competence::find($id);

        if (!$competence) {
            return response()->json(['message' => 'Compétence introuvable'], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'nom_competence' => 'sometimes|required|string|max:255|unique:competences,nom_competence,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mise à jour
        $competence->update($validator->validated());

        return response()->json([
            'message' => 'Compétence mise à jour avec succès',
            'competence' => $competence
        ]);
    }

    /**
     * Supprimer une compétence
     */
    public function destroy($id)
    {
        $competence = Competence::find($id);

        if (!$competence) {
            return response()->json(['message' => 'Compétence introuvable'], 404);
        }

        // Supprimer les relations pivot
        $competence->offres()->detach();

        // Supprimer la compétence
        $competence->delete();

        return response()->json(['message' => 'Compétence supprimée avec succès'], 200);
    }
}
