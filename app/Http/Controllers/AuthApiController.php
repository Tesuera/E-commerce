<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8|max:20',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => "Validation error",
                'errors' => $validator->errors(),
                'old' => $request->all()
            ]);
        }
        if(!Auth::attempt($request->all())) {
            return response()->json([
                'status' => 400,
                'message' => 'Authentication issue',
                'errors' => [
                    'password' => 'Incorrect username or password'
                ],
                'old' => $request->all()
            ]);
        } else {
            $user = Auth::user();
            $token = $user->createToken('API TOKEN')->plainTextToken;
            return response()->json([
                'status' => 200,
                'message' => 'Login Successfully',
                'token' => $token,
                'auth' => new UserResource($user)
            ]);
        }
    }

    public function register (Request $request) {
        $validator = Validator::make($request->all(), [
            'profile' => "nullable|file|mimes:png,jpg",
            'name' => "required|min:3|max:30",
            'email' => "required|email|unique:users,email",
            'password' => "required|min:8|max:20|confirmed",
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => "Validation Error",
                'errors' => $validator->errors(),
                'old' => $request->all()
            ]);
        }
        $unique_id = uniqid() . "_user_" . uniqid();
        $user = new User;
        $user->name = $request->name;
        $user->unique_id = $unique_id;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        if($request->profile) {
            $uniqueName = uniqid() . '__profile__.' . $request->profile->extension();
            $request->profile->storeAs('public/profiles', $uniqueName);
            $user->profile = $uniqueName;
        }
        $user->save();

        if(Auth::attempt($request->only(['email', 'password']))) {
            $token = Auth::user()->createToken('API Token')->plainTextToken;
            return response()->json([
                'status' => 200,
                'message' => "Registeration completed successfully",
                'token' => $token,
                'auth' => new UserResource(Auth::user())
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => "Something went wrong with the server",
            ]);
        }
    }

    public function logout () {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response()->json([
            'status' => 200,
            'message' => "Log out successfully"
        ]);
    }
}
