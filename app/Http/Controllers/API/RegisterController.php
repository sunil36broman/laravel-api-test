<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Resources\User as UserResource;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $users = User::all();
        return $this->sendResponse(UserResource::collection($users), 'Users retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (Auth::user()->is_admin) {

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');
        }else{
         return $this->sendError("only admin can create new user", ['error'=>'Unauthorised']);
        }


    }

    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.');
    }


    public function update(Request $request, User $user)
    //public function update(Request $request, $id)
    {
        $input = $request->all();



        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|email'

        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (Auth::user()->is_admin || Auth::user()->id==$user->id) {
            $user->name = $input['name'];
            $user->email = $input['email'];
            $user->save();
            return $this->sendResponse(new UserResource($user), 'user updated successfully.');
        }else{
            return $this->sendError("only owner or admin can be updated", ['error'=>'Unauthorised']);
        }





    }

    public function destroy(User $user)
    {

        if (Auth::user()->is_admin) {
           $user->delete();
           return $this->sendResponse([], 'Usser deleted successfully.');
        }else{
            return $this->sendError("without admin can't delete", ['error'=>'Unauthorised']);
        }


    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            $success['name'] =  $user->name;

            return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }
}