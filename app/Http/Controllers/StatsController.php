<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Offre;
use App\Models\Candidature;
use App\Models\Test;

class StatsController extends Controller
{
    public function index()
    {
        try {
            // Stats principales
            $stats = [
                'utilisateurs' => User::count(),
                'offres' => Offre::count(),
                'candidatures' => Candidature::count(),
                'tests' => Test::count(),
            ];

            // Activités récentes (limitées à 5 par type)
            $activities = [];

            // Candidatures récentes
            $lastCandidatures = Candidature::latest()->take(5)->get();
            foreach ($lastCandidatures as $c) {
                $activities[] = [
                    'id' => $c->id,
                    'type' => 'candidature',
                    'description' => "Nouvelle candidature reçue (ID: {$c->id})",
                    'created_at' => $c->created_at,
                ];
            }

            // Offres récentes
            $lastOffres = Offre::latest()->take(5)->get();
            foreach ($lastOffres as $o) {
                $activities[] = [
                    'id' => $o->id,
                    'type' => 'offre',
                    'description' => "Nouvelle offre publiée (ID: {$o->id})",
                    'created_at' => $o->created_at,
                ];
            }

            // Tests récents
            $lastTests = Test::latest()->take(5)->get();
            foreach ($lastTests as $t) {
                $activities[] = [
                    'id' => $t->id,
                    'type' => 'test',
                    'description' => "Un test a été complété (ID: {$t->id})",
                    'created_at' => $t->created_at,
                ];
            }

            // Trier toutes les activités par date décroissante
            usort($activities, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

            // Quick stats dynamiques
            $quickStats = [
                ['title' => 'Utilisateurs actifs', 'value' => User::whereNotNull('email_verified_at')->count()],
                ['title' => 'Offres actives', 'value' => Offre::where('statut_offre', 'active')->count()],
                ['title' => 'Candidatures récentes (7j)', 'value' => Candidature::whereDate('created_at', '>=', now()->subDays(7))->count()],
                ['title' => 'Tests programmés', 'value' => Test::count()],
            ];

            // Retourner les stats + activités + quick stats
            return response()->json([
                ...$stats,
                'activities' => $activities,
                'quick_stats' => $quickStats,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
