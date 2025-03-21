<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;

class ReportController extends Controller
{
    public function store(ReportRequest $request)
    {
        $user = auth()->user();
        $modelClass = 'App\\Models\\' . ucfirst($request->entity_type);
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid entity type'], 400);
        }

        $entity = $modelClass::find($request->entity_id);
        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }

        $report = $entity->reports()->create([
            'user_id' => $user->id,
            'email' => $request->email,
            'name' => $request->name,
            'text' => $request->text,
        ]);

        return response()->json($report);
    }
}
