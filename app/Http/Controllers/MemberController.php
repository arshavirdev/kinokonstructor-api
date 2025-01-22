<?php

namespace App\Http\Controllers;

use App\Models\Profile;

use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function checkId(Request $request)
    {
        $id = $request->input('id');
        abort_unless($id, 422, 'ID is required');

        $profile = Profile::where('member_id', $id)->first();
        abort_unless($profile, 404, 'Member not found');

        if ($profile['user_id']) {
            return ['canRegister' => false];
        };
        return [
            'canRegister' => true,
            'fullname' => $profile->firstname . ' ' . $profile->middlename
        ];
    }
}
