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
            return response()->json([
                'utilisateurs' => User::count(),
                'offres' => Offre::count(),
                'candidatures' => Candidature::count(),
                'tests' => Test::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
