<?php

namespace App\Http\Controllers;

use App\Models\Entretien;
use App\Models\Candidature;
use App\Mail\EntretienPlanifieMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EntretienNotificationMail;

class EntretienController extends Controller
{
    /**
     * Liste tous les entretiens avec leurs relations
     */
    public function index()
    {
        return Entretien::with(['candidature.candidat', 'manager'])->get();
    }

    /**
     * Crée un nouvel entretien
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'candidature_id' => 'required|exists:candidatures,id',
            'manager_id' => 'required|exists:users,id',
            'type_entretien' => 'required|in:présentiel,en ligne',
            'lieu' => 'nullable|string|required_if:type_entretien,présentiel',
            'lien_meet' => 'nullable|url|required_if:type_entretien,en ligne',
            'date_entretien' => 'required|date|after:now',
            'commentaire' => 'nullable|string|max:500',
        ]);
    
        $candidature = Candidature::with('candidat')->findOrFail($validated['candidature_id']);
    
        // Mettre la candidature en "acceptee" si nécessaire
        if ($candidature->etat_candidature !== 'acceptee') {
            $candidature->etat_candidature = 'acceptee';
            $candidature->save();
        }
    
        // Vérification doublon
        $conflict = Entretien::where('candidature_id', $validated['candidature_id'])
            ->where('date_entretien', $validated['date_entretien'])
            ->exists();
    
        if ($conflict) {
            return response()->json(['error' => 'Un entretien existe déjà pour ce candidat à cette date.'], 409);
        }
    
        // Création de l’entretien
        $entretien = Entretien::create($validated);
    
        // Envoi email au candidat
        try {
            if ($candidature->candidat && $candidature->candidat->email_utilisateur) {
                Mail::to($candidature->candidat->email_utilisateur)
                    ->send(new EntretienPlanifieMail($entretien));
            } else {
                \Log::warning("Email du candidat manquant pour la candidature ID {$candidature->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Erreur envoi mail entretien : {$e->getMessage()}");
        }
    
        return response()->json([
            'message' => 'Entretien planifié et candidature acceptée avec succès !',
            'entretien' => $entretien
        ], 201);
    }
    
    /**
     * Afficher un entretien
     */
    public function show($id)
    {
        $entretien = Entretien::with(['candidature.candidat', 'manager'])->findOrFail($id);
        return response()->json($entretien);
    }

    /**
     * Modifier un entretien existant
     */
    public function update(Request $request, $id)
    {
        $entretien = Entretien::findOrFail($id);

        $validated = $request->validate([
            'type_entretien' => 'in:présentiel,en ligne',
            'lieu' => 'nullable|string|required_if:type_entretien,présentiel',
            'lien_meet' => 'nullable|url|required_if:type_entretien,en ligne',
            'date_entretien' => 'nullable|date|after:now',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $entretien->update($validated);

        return response()->json([
            'message' => 'Entretien mis à jour avec succès !',
            'entretien' => $entretien
        ]);
    }

    /**
     * Supprimer un entretien
     */
    public function destroy($id)
    {
        $entretien = Entretien::findOrFail($id);
        $entretien->delete();

        return response()->json(['message' => 'Entretien supprimé avec succès.']);
    }
}
