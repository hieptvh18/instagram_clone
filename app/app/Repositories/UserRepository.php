<?php
namespace App\Repositories;

use App\Models\Follow;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

class UserRepository implements UserRepositoryInterface
{

    public function getUserByUsername($username){
        try {
            if ($username && User::where('username', $username)->exists()) {
                $user = User::where('username', $username)->first();
                $data = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'bio' => $user->bio,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'google_id' => $user->google_id,
                    'facebook_id' => $user->facebook_id,
                    'follower' => $user->follower,
                    'following' => $user->following,
                    'posts'=>$user->posts
                ];
                return response()->json([
                    'data' => $data,
                    'message' => 'Get user data by username success',
                    'success' => true,
                ]);
            }
            return response()->json([
                'data' => [],
                'message' => 'User not found!',
                'success' => false,
            ]);
        }
        catch (Throwable $e) {
            return response()->json([
                'data' => [],
                'message' => 'Get user by username fail!! ' . $e->getMessage(),
                'success' => false,
            ]);
        }
    }

    public function updateUserById($request){
        try {
            if ($user = User::find($request->id)) {

                $user->username = $request->username;
                $user->email = $request->email;
                $user->bio = $request->bio;
                $user->phone = $request->phone;

                $userSave = $user->save();
                if ($userSave) {
                    return response()->json([
                        'success' => true,
                        'data' => $user,
                        'message' => 'Update profile success'
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Update fail!'
                ],500);
            }
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Params id is invalid!'
            ]);
        } catch (Throwable $e) {
            report($e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Update profile fail! ' . $e->getMessage()
            ]);
        }
    }

    public function findUserByUsername($username){
        try {
            if(!$username){
                return response()->json([
                    "success" => false,
                    "data" => [],
                    "message" => "Params username is required!"
                ]);
            }
            $user = User::select('id','name','email','username','avatar')->where('username','like', $username.'%')->get();

            if ($user) {
                return response()->json([
                    "success" => true,
                    "data" => $user,
                    "message" => "Get success user!"
                ]);
            }

            return response()->json([
                "success" => true,
                "data" => [],
                "message" => "User not found!"
            ]);
        } catch (Throwable $e) {
            report($e->getMessage());
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => $e->getMessage()
            ]);
        }
    }

    public function create($request)
    {
        $dataResponse = ['status' => true, 'data' => [], 'message' => ""];

        if (!$request->username || !$request->name || !$request->email || !$request->password) {
            return response()->json([
                'status' => false,
                'message' => 'Data is not valid!',
                'data' => $request->all()
            ]);
        }

        try {
            $userExistEmail = User::where('email', $request->email)->first();
            $userExistUsername = User::where('username', $request->username)->first();

            if ($userExistEmail) {
                $dataResponse['status'] = false;
                $dataResponse['message'] = 'Email exist customer!';
                return response()->json($dataResponse);
            }

            if ($userExistUsername) {
                $dataResponse['status'] = false;
                $dataResponse['message'] = 'Username exist customer!';
                return response()->json($dataResponse);
            }

            $model = new User();
            $model->fill($request->all());
            $model->password = Hash::make($request->password);
            $model->save();

            $dataResponse['data'] = $request->all();
            $dataResponse['message'] = 'Register success!';
            return response()->json($dataResponse);
        } catch (Throwable $e) {
            report($e);
            $dataResponse['status'] = false;
            $dataResponse['message'] = 'Error: ' . $e->getMessage();
            return response()->json($dataResponse);
        }
    }

    public function follow($request)
    {
        try{
            // action following
            $userFollow = new Follow();
            $userFollow->user_id = $request->user_id;
            $userFollow->following_id = $request->following_id;
            $userFollow->save();

            return response()->json([
                'success'=>true,
                'data'=>[],
                'message'=>'Following success'
            ]);
        }catch(Throwable $e){
            report($e->getMessage());
            return response()->json([
                'success'=>false,
                'data'=>[],
                'message'=>$e->getMessage()
            ]);
        }
    }

    // public function getUserNamebyFollowingId($followListId){
    //     foreach ($followListId as $key) {
    //         $data = User::where('id', $key)->get('name');
    //     }
    //     dd($data);
    //     return $data;
    // }

    public function logout($request)
    {
        try {
            $cookie = Cookie::forget('jwtlogin');
            $request->user()->currentAccessToken()->delete();
            return response()->json(['status' => 'ok', 'message' => 'Logout success!']);
        } catch (Throwable $e) {
            report($e->getMessage());
            return response()->json(['status' => 'fail', 'message' => 'Logout fail!']);
        }
    }
}
