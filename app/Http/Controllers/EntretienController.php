<?php

namespace App\Http\Controllers;

use App\Mail\EntretienNotificationMail;
use App\Mail\EntretienPlanifieMail;
use App\Models\Candidature;
use App\Models\Entretien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class EntretienController extends Controller
{
    /**
     * Liste tous les entretiens avec leurs relations
     */
    public function index()
    {
        return Entretien::with(['candidature.candidat', 'manager'])->get();
    }

    public function testEMail()
    {
        // just a test by raw
        $message = 'test';
        Mail::raw($message, function ($mail) {
            $mail
                ->to('antsaniainaramanandraibe@gmail.com')
                ->subject('Test Email Subject');
        });
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

        if ($candidature->etat_candidature !== 'acceptee') {
            $candidature->etat_candidature = 'acceptee';
            $candidature->save();
        }

        $conflict = Entretien::where('candidature_id', $validated['candidature_id'])
            ->where('date_entretien', $validated['date_entretien'])
            ->exists();

        if ($conflict) {
            return response()->json(['error' => 'Un entretien existe déjà pour ce candidat à cette date.'], 409);
        }

        $entretien = Entretien::create($validated);

        // Envoi email brut
        try {
            if ($candidature->candidat && $candidature->candidat->email_utilisateur) {
                $message = "Bonjour {$candidature->candidat->nom},\n\n"
                    . "Votre entretien est planifié le {$validated['date_entretien']} "
                    . "de type {$validated['type_entretien']}.\n\n"
                    . ($validated['type_entretien'] === 'présentiel'
                        ? "Lieu : {$validated['lieu']}\n"
                        : "Lien Meet : {$validated['lien_meet']}\n")
                    . "\nCordialement,\nL'équipe RH";

                Mail::raw($message, function ($mail) use ($candidature) {
                    $mail
                        ->to($candidature->candidat->email_utilisateur)
                        ->subject('Invitation à un entretien');
                });
            } else {
                Log::warning("Email du candidat manquant pour la candidature ID {$candidature->id}");
            }
        } catch (Exception $e) {
            Log::error("Erreur envoi mail entretien : {$e->getMessage()}");
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
