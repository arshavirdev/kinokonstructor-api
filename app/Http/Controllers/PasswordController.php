<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\PasswordController as ParentPasswordController;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class PasswordController extends ParentPasswordController
{
    public function update(Request $request, UpdatesUserPasswords $updater)
    {
        try {
            $updater->update($request->user(), $request->all());
    
            return response()->json(["message" => "Successfully updated"], 200);
        } catch (\Exception $e) {
            return response()->json(["message"=> $e->getMessage()],400);
        }
    }
}
