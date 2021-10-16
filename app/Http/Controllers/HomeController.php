<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{


    public function userChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Password enter is not suitable.']);
        }

        $user = User::with(['hobbies'])->where('id', $request->userId)->first();
        if($user->email == $request->email) {

            $user->password = bcrypt($request->password);
            $user->update();

            return response()->json([
            'user' => $user,
            'success' =>'Password changed successfully'
            ]);

        } else {
            return response()->json([
                'error' =>'Password changed failed'
            ]);
        }
    }


    public function userUpdateEmail(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        $user = User::with(['hobbies'])->where('id', $request->userId)->first();

        if($user) {
            $user->email = $request->email;
            $user->update();

            return response()->json([
            'user' => $user,
            'success' =>'Email updated successfully'
            ]);

        } else {
            return response()->json([
                'error' =>'Email update failed'
            ]);
        }
    }


    public function userUpdateUsername(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        $user = User::with(['hobbies'])->where('id', $request->userId)->first();

        if($user) {
            $user->name = $request->name;
            $user->update();

            return response()->json([
            'user' => $user,
            'success' =>'Username updated successfully'
            ]);

        } else {
            return response()->json([
                'error' =>'Username update failed'
            ]);
        }
    }



}
