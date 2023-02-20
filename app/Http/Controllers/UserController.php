<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = UserResource::collection(User::latest('id')->paginate(10));
        return $users;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uniqueId)
    {
        $user = User::where('unique_id', $uniqueId)->first();
        return response()->json([
            'status' => 200,
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if(is_null($user)) {
            return response()->json([
                'status' => 204,
                'message' => 'No such content is found'
            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => "required|min:3|max:30",
            'email' => "required|email|unique:users,email,". $id,
            'photo' => "nullable|file|mimes:png,jpg|max:512"
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation error',
                'old' => $request->all()
            ]);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        if($request->photo) {
            //remove from storage
            Storage::delete('public/profiles/'. $user->profile);
            // insert to storage and database
            $newname = uniqid() . '__profile__.' . $request->photo->extension();
            $request->photo->storeAs('public/profiles', $newname);
            $user->profile = $newname;
        }
        $user->update();
        return response()->json([
            'status' => 200,
            'message' => "updated successfully",
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('unique_id', $id)->first();
        if(!is_null($user)){
            $name = $user->name;
            $user->delete();
            return response()->json([
                'status' => 200,
                'message' => "user " . $name . " is deleted successfully"
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'No such user is found'
            ]);
        }
    }

    public function changeRole (Request $request, $unique_id) {
        $user = User::where('unique_id', $unique_id)->first();
        if(is_null($user)) {
            return response()->json([
                'status' => 204,
                'message' => 'No such user is found'
            ]);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => $user->name . ' is promoted to ' . $user->role,
            'user' => new UserResource($user)
        ]);
    }
}
