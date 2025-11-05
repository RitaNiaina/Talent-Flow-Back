<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Entretien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;

class EntretienController extends Controller
{
    /**
     * Liste tous les entretiens
     */
    public function index()
    {
        return Entretien::with(['candidature.candidat', 'candidature.offre', 'manager'])->get();
    }

    /**
     * Crée un nouvel entretien + envoi email
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

    $candidature = Candidature::with('candidat', 'offre')->findOrFail($validated['candidature_id']);

    if ($candidature->etat_candidature !== 'acceptee') {
        $candidature->etat_candidature = 'acceptee';
        $candidature->save();
    }

    $entretien = Entretien::create($validated);

    try {
        if ($candidature->candidat && $candidature->candidat->email_utilisateur) {
            $dateEntretien = Carbon::parse($validated['date_entretien'])
                ->locale('fr')
                ->translatedFormat('l j F Y à H:i');
            $titreOffre = $candidature->offre ? $candidature->offre->titre_offre : 'Offre non spécifiée';
            $logoUrl = asset('images/unit-logo.png');

            $htmlMessage = '
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin:0; padding:20px; background-color:#f5f6fa; }
                    .container { max-width:600px; margin:auto; background:#fff; border:1px solid #e1e4e8; border-radius:10px; padding:25px; box-shadow:0 2px 8px rgba(0,0,0,0.05);}
                    .header { border-bottom:3px solid #2563eb; padding-bottom:10px; text-align:center; font-size:18px; font-weight:bold; color:#2563eb; }
                    .logo { text-align:center; margin-bottom:10px; }
                    .logo img { width:100px; }
                    .section { margin:20px 0; border-left:4px solid #2563eb; background:#f9fbff; padding:12px 18px; border-radius:6px; }
                    .label { font-weight:bold; color:#333; }
                    .value { font-weight:normal; color:#000; }
                    .btn { display:inline-block; background:#2563eb; color:#fff !important; text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:bold; margin-top:15px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="logo"><img src="'.$logoUrl.'" alt="Logo Entreprise"></div>
                    <div class="header">📅 Invitation à un entretien</div>
                    <p>Bonjour <strong>'.e($candidature->candidat->nom_utilisateur).'</strong>,</p>
                    <p>Vous avez postulé sur l\'offre : <strong>'.e($titreOffre).'</strong>.</p>
                    <div class="section">
                        <p>🗓 <span class="label">Date :</span> <span class="value">'.e($dateEntretien).'</span></p>
                        <p>🧩 <span class="label">Type :</span> <span class="value">'.e($validated['type_entretien']).'</span></p>';

            if ($validated['type_entretien'] === 'présentiel') {
                $htmlMessage .= '<p>📍 <span class="label">Lieu :</span> '.e($validated['lieu']).'</p>';
            } else {
                $htmlMessage .= '<p>💻 <span class="label">Lien :</span> <a href="'.e($validated['lien_meet']).'">'.e($validated['lien_meet']).'</a></p>
                <p style="text-align:center"><a href="'.e($validated['lien_meet']).'" class="btn">🎥 Rejoindre l’entretien</a></p>';
            }

            $htmlMessage .= '
                    </div>
                    <p>Merci de bien vouloir être prêt(e) à l’heure indiquée.</p>
                    <p>Cordialement,<br>L’équipe RH</p>
                </div>
            </body>
            </html>';

            Mail::html($htmlMessage, function ($message) use ($candidature) {
                $message->to($candidature->candidat->email_utilisateur)
                    ->subject('📢 Invitation à un entretien');
            });
        }
    } catch (Exception $e) {
        Log::error("Erreur envoi mail entretien : {$e->getMessage()}");
    }

    return response()->json(['message' => 'Entretien planifié et candidature acceptée avec succès !', 'entretien' => $entretien], 201);
}

/**
 * Mise à jour / report
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
    $entretien->load([
        'manager:id,nom_utilisateur,email_utilisateur',
        'candidature:id,candidat_id,offre_id',
        'candidature.candidat:id,nom_utilisateur,email_utilisateur',
        'candidature.offre:id,titre_offre'
    ]);

    $manager = $entretien->manager;
    $candidat = $entretien->candidature->candidat;
    $titreOffre = $entretien->candidature->offre ? $entretien->candidature->offre->titre_offre : 'Offre non spécifiée';
    $logoUrl = asset('images/unit-logo.png');

    try {
        $dateEntretien = Carbon::parse($entretien->date_entretien)
            ->locale('fr')
            ->translatedFormat('l j F Y à H:i');

        $htmlMessage = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin:0; padding:20px; background-color:#f5f6fa; }
                .container { max-width:600px; margin:auto; background:#fff; border:1px solid #e1e4e8; border-radius:10px; padding:25px; box-shadow:0 2px 8px rgba(0,0,0,0.05);}
                .header { border-bottom:3px solid #2563eb; padding-bottom:10px; text-align:center; font-size:18px; font-weight:bold; color:#2563eb; }
                .logo { text-align:center; margin-bottom:10px; }
                .logo img { width:100px; }
                .section { margin:20px 0; border-left:4px solid #2563eb; background:#f9fbff; padding:12px 18px; border-radius:6px; }
                .label { font-weight:bold; color:#333; }
                .value { font-weight:normal; color:#000; }
                .btn { display:inline-block; background:#2563eb; color:#fff !important; text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:bold; margin-top:15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="logo"><img src="'.$logoUrl.'" alt="Logo Entreprise"></div>
                <div class="header">📢 Report d\'entretien</div>
                <p>Bonjour <strong>'.e($candidat->nom_utilisateur).'</strong>,</p>
                <p>Votre entretien prévu sur l\'offre : <strong>'.e($titreOffre).'</strong> a été <strong>reporté</strong>.</p>
                <div class="section">
                    <p>🗓 <span class="label">Date :</span> <span class="value">'.e($dateEntretien).'</span></p>
                    <p>🧩 <span class="label">Type :</span> <span class="value">'.e($validated['type_entretien']).'</span></p>';

        if ($validated['type_entretien'] === 'présentiel') {
            $htmlMessage .= '<p>📍 <span class="label">Lieu :</span> '.e($validated['lieu']).'</p>';
        } else {
            $htmlMessage .= '<p>💻 <span class="label">Lien :</span> <a href="'.e($validated['lien_meet']).'">'.e($validated['lien_meet']).'</a></p>
            <p style="text-align:center"><a href="'.e($validated['lien_meet']).'" class="btn">🎥 Rejoindre l’entretien</a></p>';
        }

        $htmlMessage .= '
                </div>
                <p>Merci de prendre note de ce changement.</p>
                <p>Cordialement,<br>L’équipe RH</p>
            </div>
        </body>
        </html>';

        if ($candidat && $candidat->email_utilisateur) {
            Mail::html($htmlMessage, function ($message) use ($candidat) {
                $message->to($candidat->email_utilisateur)
                    ->subject('📢 Votre entretien a été reporté');
            });
        }

        if ($manager && $manager->email_utilisateur) {
            Mail::html($htmlMessage, function ($message) use ($manager) {
                $message->to($manager->email_utilisateur)
                    ->subject('📢 Entretien reporté / Assignation mise à jour');
            });
        }

    } catch (Exception $e) {
        Log::error("Erreur envoi mail update : {$e->getMessage()}");
    }

    return response()->json(['message' => 'Entretien reporté avec succès !', 'entretien' => $entretien]);
}

/**
 * Annulation
 */
public function destroy($id)
{
    $entretien = Entretien::with('candidature.candidat', 'candidature.offre')->findOrFail($id);
    $candidat = $entretien->candidature->candidat;
    $titreOffre = $entretien->candidature->offre ? $entretien->candidature->offre->titre_offre : 'Offre non spécifiée';
    $logoUrl = asset('images/unit-logo.png');

    $dateEntretien = Carbon::parse($entretien->date_entretien)
        ->locale('fr')
        ->translatedFormat('l j F Y à H:i');

    try {
        if ($candidat && $candidat->email_utilisateur) {
            $htmlMessage = '
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin:0; padding:20px; background-color:#f5f6fa; }
                    .container { max-width:600px; margin:auto; background:#fff; border:1px solid #e1e4e8; border-radius:10px; padding:25px; box-shadow:0 2px 8px rgba(0,0,0,0.05);}
                    .header { border-bottom:3px solid #dc2626; padding-bottom:10px; text-align:center; font-size:18px; font-weight:bold; color:#dc2626; }
                    .logo { text-align:center; margin-bottom:10px; }
                    .logo img { width:100px; }
                    .section { margin:20px 0; border-left:4px solid #dc2626; background:#fff5f5; padding:12px 18px; border-radius:6px; }
                    .label { font-weight:bold; color:#333; }
                    .value { font-weight:normal; color:#000; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="logo"><img src="'.$logoUrl.'" alt="Logo Entreprise"></div>
                    <div class="header">❌ Annulation d\'entretien</div>
                    <p>Bonjour <strong>'.e($candidat->nom_utilisateur).'</strong>,</p>
                    <p>Nous vous informons que votre entretien prévu sur l\'offre <strong>'.e($titreOffre).'</strong> a été <span style="color:red; font-weight:bold">annulé</span>.</p>
                    <div class="section">
                        <p>🗓 <span class="label">Date prévue :</span> <span class="value">'.e($dateEntretien).'</span></p>
                        <p>🧩 <span class="label">Type :</span> <span class="value">'.e($entretien->type_entretien).'</span></p>';

            if ($entretien->type_entretien === 'présentiel' && $entretien->lieu) {
                $htmlMessage .= '<p>📍 <span class="label">Lieu :</span> '.e($entretien->lieu).'</p>';
            } elseif ($entretien->type_entretien === 'en ligne' && $entretien->lien_meet) {
                $htmlMessage .= '<p>💻 <span class="label">Lien :</span> <a href="'.e($entretien->lien_meet).'">'.e($entretien->lien_meet).'</a></p>';
            }

            $htmlMessage .= '
                    </div>
                    <p>Si vous avez des questions, n’hésitez pas à nous contacter.</p>
                    <p>Cordialement,<br>L’équipe RH</p>
                </div>
            </body>
            </html>';

            Mail::html($htmlMessage, function ($message) use ($candidat) {
                $message->to($candidat->email_utilisateur)
                    ->subject('❌ Annulation de votre entretien');
            });
        }
    } catch (Exception $e) {
        Log::error("Erreur email annulation : {$e->getMessage()}");
    }

    $entretien->delete();

    return response()->json(['message' => 'Entretien annulé avec succès et candidat notifié.']);
}

    
    public function getRecruteurs()
    {
        try {
            $recruteurs = \App\Models\User::whereHas('role', fn($q) => $q->where('type_role', 'Recruteur'))
                ->select('id', 'nom_utilisateur', 'email_utilisateur')
                ->get();

            return response()->json($recruteurs);
        } catch (Exception $e) {
            Log::error("Erreur getRecruteurs : {$e->getMessage()}");
            return response()->json(['error' => 'Erreur lors du chargement des recruteurs'], 500);
        }
    }
}
