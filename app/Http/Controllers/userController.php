<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class userController extends Controller
{

    public function registerSocial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }


        $input = $request->all();
        $input['password'] = bcrypt(rand ( 10000 , 99999 ));
        $user_check =  User::where('email',$request->email)->first();
        if($user_check){
            $data['status'] = true;
            $data['name'] =  $user_check->name;
            $data['phone'] =  $user_check->phone;
            $data['email'] =  $user_check->email;
            $data['token'] =  $user_check->createToken('MyApp')-> accessToken;
            return response()->json($data,200);
        }else{
            $user = User::create($input);
            Auth::login($user);
            $data['status'] = true;
            $data['name'] =  $user->name;
            $data['phone'] =  $user->phone;
            $data['email'] =  $user->email;
            $data['token'] =  $user->createToken('MyApp')-> accessToken;
            return response()->json($data, 200);
        }

    }


    public function socialSignin( Request $request )
    {

        try {
            switch ($request->type) {
                case 1:
                    $data = Socialite::driver('facebook')->userFromToken($request->token);
                    break;
            }


            $name = $request->type != 3 ? $data->getName() : $request->name;
            $email = $data->getEmail();
            $pass = Str::random();

            $user = User::where('email', $email)->first();
            if (!$user) {

                $user['name'] = $name;
                $user['email'] = $email;
                $user['password'] = $pass;

                $user = User::create($user);
                Auth::login($user);


                $res['status'] = true;
                $res['name'] = $user->name;
                $res['phone'] = $user->phone;
                $res['email'] = $user->email;
                $res['token'] = $user->createToken('MyApp')->accessToken;

                return response()->json($res, 200);


            }
        }
        catch (\Exception $e) {
                print_r($e->getMessage());
                return response()->json(['message' => "Invalid token",'status'=>false]);
            }

    }





}
