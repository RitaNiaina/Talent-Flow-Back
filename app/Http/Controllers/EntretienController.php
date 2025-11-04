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
        return Entretien::with([
            'candidature.candidat',
            'candidature.offre', 
            'manager'
        ])->get();
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

    // Envoi email HTML avec bouton "Rejoindre l’entretien"
try {
    if ($candidature->candidat && $candidature->candidat->email_utilisateur) {

        // Formatage propre de la date
        $dateEntretien = \Carbon\Carbon::parse($validated['date_entretien'])
            ->locale('fr')
            ->translatedFormat('l j F Y à H:i');

        // Titre de l’offre
        $titreOffre = $candidature->offre ? $candidature->offre->titre_offre : 'Offre non spécifiée';

        // Message HTML complet
        $htmlMessage = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 14px;
                    color: #333;
                    line-height: 1.6;
                    margin: 0;
                    padding: 20px;
                    background-color: #f5f6fa;
                }
                .container {
                    max-width: 600px;
                    margin: auto;
                    background: #ffffff;
                    border: 1px solid #e1e4e8;
                    border-radius: 10px;
                    padding: 25px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                }
                .header {
                    border-bottom: 3px solid #2563eb;
                    padding-bottom: 10px;
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    color: #2563eb;
                }
                p {
                    color: #333;
                    margin: 10px 0;
                }
                .section {
                    margin: 20px 0;
                    border-left: 4px solid #2563eb;
                    background: #f9fbff;
                    padding: 12px 18px;
                    border-radius: 6px;
                }
                .label {
                    font-weight: bold; /* Labels en gras */
                    color: #333;
                }
                .value {
                    font-weight: normal; /* Valeurs dynamiques normales */
                    color: #000;
                }
                .link-value {
                    font-weight: bold; /* Lien Meet en gras */
                    color: #000;
                    text-decoration: none;
                }
                .btn {
                    display: inline-block;
                    background: #2563eb;
                    color: #fff !important;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    font-weight: bold;
                    margin-top: 15px;
                }
                .btn:hover {
                    background: #1e40af;
                }
                .footer {
                    border-top: 1px solid #ddd;
                    margin-top: 25px;
                    padding-top: 10px;
                    font-size: 13px;
                    text-align: center;
                    color: #555;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">📅 Invitation à un entretien</div>

                <p>Bonjour <strong>' . e($candidature->candidat->nom_utilisateur) . '</strong>,</p>

                <p>Vous avez postulé sur l\'offre : <strong>' . e($titreOffre) . '</strong>.</p>

                <p>Nous avons le plaisir de vous informer que votre entretien est planifié comme suit :</p>

                <div class="section">
                    <p>🗓 <span class="label">Date :</span> <span class="value">' . e($dateEntretien) . '</span></p>
                    <p>🧩 <span class="label">Type d\'entretien :</span> <span class="value">' . e(ucfirst($validated['type_entretien'])) . '</span></p>';

        // Si présentiel
        if ($validated['type_entretien'] === 'présentiel') {
            $htmlMessage .= '
                    <p>📍 <span class="label">Lieu :</span> <span class="value">' . e($validated['lieu']) . '</span></p>';
        } 
        // Si en ligne
        else {
            $htmlMessage .= '
                    <p>💻 <span class="label">Lien Meet :</span> <span class="link-value"><a href="' . e($validated['lien_meet']) . '">' . e($validated['lien_meet']) . '</a></span></p>
                    <p style="text-align: center;">
                        <a href="' . e($validated['lien_meet']) . '" class="btn">🎥 Rejoindre l’entretien</a>
                    </p>';
        }

        $htmlMessage .= '
                </div>

                <p>Merci de bien vouloir être prêt(e) à l’heure indiquée.</p>
                <p>Nous vous souhaitons une excellente préparation !</p>

                <div class="footer">
                <strong>Cordialement,</strong><br>
                    👥 L’équipe RH
                </div>
            </div>
        </body>
        </html>
        ';

        // ✅ Envoi du mail HTML (Laravel 10)
        Mail::html($htmlMessage, function ($message) use ($candidature) {
            $message->to($candidature->candidat->email_utilisateur)
                ->subject('📢 Invitation à un entretien');
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
        $entretien = Entretien::with([
            'candidature.candidat',
            'candidature.offre', 
            'manager'
        ])->findOrFail($id);
    
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
            'manager_id' => 'required|exists:users,id',
        ]);
    
        $entretien->update($validated);
    
        $manager = $entretien->manager; // récupère le manager actuel
        $candidat = $entretien->candidature->candidat;
    
        // Envoyer un mail au candidat et au nouveau manager
        try {
            $htmlMessage = "
                <html>
                <head><meta charset='utf-8'></head>
                <body>
                    <p>Bonjour <strong>{$candidat->nom_utilisateur}</strong>,</p>
                    <p>Votre entretien a été reporté au <strong>{$entretien->date_entretien}</strong>.</p>
                    <p>Cordialement,<br>L’équipe RH</p>
                </body>
                </html>
            ";
            Mail::html($htmlMessage, function ($message) use ($candidat) {
                $message->to($candidat->email_utilisateur)
                    ->subject('📅 Reportation de votre entretien');
            });
    
            // Mail au manager
            if ($manager && $manager->email_utilisateur) {
                Mail::html($htmlMessage, function ($message) use ($manager) {
                    $message->to($manager->email_utilisateur)
                        ->subject('📅 Entretien assigné/modifié');
                });
            }
    
        } catch (\Exception $e) {
            \Log::error("Erreur envoi mail: {$e->getMessage()}");
        }
    
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
    $entretien = Entretien::with('candidature.candidat')->findOrFail($id);
    $candidat = $entretien->candidature->candidat;

    try {
        // Envoi de l'email d'annulation
        if ($candidat && $candidat->email_utilisateur) {
            $htmlMessage = '
            <html>
            <head><meta charset="utf-8"></head>
            <body>
                <p>Bonjour <strong>' . e($candidat->nom_utilisateur) . '</strong>,</p>
                <p>Nous vous informons que votre entretien prévu le <strong>' . e($entretien->date_entretien) . '</strong> a été annulé.</p>
                <p>Si vous avez des questions, n’hésitez pas à nous contacter.</p>
                <p>Cordialement,<br>L’équipe RH</p>
            </body>
            </html>
            ';

            Mail::html($htmlMessage, function ($message) use ($candidat) {
                $message->to($candidat->email_utilisateur)
                        ->subject('❌ Annulation de votre entretien');
            });
        }
    } catch (Exception $e) {
        \Log::error("Erreur envoi email annulation entretien: {$e->getMessage()}");
    }

    // Suppression de l'entretien
    $entretien->delete();

    return response()->json(['message' => 'Entretien annulé avec succès et candidat notifié.']);
}
    /**
 * Récupère la liste des recruteurs (utilisateurs ayant le rôle 'Recruteur')
 */
public function getRecruteurs()
{
    try {
        $recruteurs = \App\Models\User::whereHas('role', function ($query) {
            $query->where('type_role', 'Recruteur'); 
        })
        ->select('id', 'nom_utilisateur', 'email_utilisateur')
        ->get();

        return response()->json($recruteurs, 200);
    } catch (\Exception $e) {
        \Log::error("Erreur lors de la récupération des recruteurs : " . $e->getMessage());
        return response()->json(['error' => 'Erreur lors du chargement des recruteurs'], 500);
    }
}

}

