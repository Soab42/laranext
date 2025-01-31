<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Libs\Jwt\JwtManager;
use App\Models\User;
use App\Utils\ApiHttpCode;
use App\Utils\ApiStatusCode;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = auth()->attempt($credentials);

        if (!empty($user)) {
            $response = array_merge($user, ['user'=>auth()->user()]);
            return sendResponse(ApiStatusCode::SUCCESS, __('Login success'), $response);
        }

        return sendResponse(ApiStatusCode::FAILED, __('User not found'), []);
    }

    public function refreshToken(Request $request)
    {
        $refresh_token = $request->refresh_token;

        if(empty($refresh_token)){
            return sendErrorResponse(ApiStatusCode::TOKEN_EXPIRED, __('Refresh Token Expired'), [],ApiHttpCode::notAcceptable);
        }

        try{

            $user_id = JwtManager::validateToken('refresh_token',$refresh_token);

            $user_from_db = User::find($user_id);
            $user = auth()->regenerateAuth($user_from_db);
            if ($user) {
                return sendResponse(ApiStatusCode::SUCCESS, __('Token Refresh success'), $user);
            }
        }catch (\Exception $exception){

        }

        return sendResponse(ApiStatusCode::FAILED, __('Refresh Token failed'), []);
    }

    public function getAuthenticatedUser()
    {

        dd(auth()->user());
        //auth()->logout();
        return sendResponse(ApiStatusCode::SUCCESS, __('Fetched successfully.'), auth()->user());
    }

    public function register(RegisterRequest $request)
    {
        $inputData = $request->all();
        dd($inputData);
        $user = (new User())->createUser($inputData);

        if (!empty($user)) {
            return sendResponse(ApiStatusCode::SUCCESS, __('Registered successfully.'), $user);
        }

        return sendResponse(ApiStatusCode::FAILED, __('User creation failed'), []);

    }
}
