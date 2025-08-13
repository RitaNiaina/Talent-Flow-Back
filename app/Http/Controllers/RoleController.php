<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Role::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       // 1. Valider les données
       $validator = Validator::make($request->all(), [
        'type_role' => 'required|string',
    ]);

    // 2. Si la validation échoue, renvoyer les erreurs
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    // 3. Créer le role si tout est bon
    $role = Role::create($validator->validated());

    return response()->json($role, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        $role = Role::find($id);

        if (!$role) {
        return response()->json(['message' => 'role n existe pas'], 404);
    }
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

    if (!$role) {
        return response()->json(['message' => 'Role introuvable'], 404);
    }

    // Validation
    $validator = Validator::make($request->all(), [
        'type_role' => 'required|string',
        
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Mise à jour
    $role->update($validator->validated());

    return response()->json(['message' => 'Role mis à jour avec succès', 'role' => $role]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::find($id);

    if (!$role) {
        return response()->json([
            'message' => 'Role introuvable'
        ], 404);
    }

    $role->delete();

    return response()->json([
        'message' => 'Role supprimé avec succès'
    ], 200);
   }
    }
