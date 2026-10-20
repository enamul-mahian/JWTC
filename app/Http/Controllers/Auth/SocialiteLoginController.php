<?php

namespace App\Http\Controllers\Auth;

use App\Enum\RoleType;
use App\Enum\StatusType;
use App\Enum\UserType;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use App\Models\User as ModelUser;
use App\Http\Controllers\Controller;
use App\Models\PersonalInformation;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class SocialiteLoginController extends Controller
{

    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $name = $user->getName();
        $email = $user->getEmail();
        $provider_id = $user->getId();
        $provider_token = $user->token;
        $photo = $user->getAvatar();

        $responseData = json_encode([
            'provider_id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'provider_token' => $user->token,
            'photo' => $user->getAvatar(),
            'provider' => $provider,
        ]);

        $response = new Response();
        $response->setContent($responseData);
        $response->setStatusCode(Response::HTTP_OK);

        $role = Role::where('name', RoleType::JOB_SEEKER)->first();


        $modelUser = ModelUser::where('email', $email)->first();

        if (!$modelUser) {
            $socialUser = ModelUser::create([
                'name' => $name,
                'email' => $email,
                'provider_id' => $provider_id,
                'provider_token' => $provider_token,
                'photo' => $photo,
                'provider' => $provider,
                'status' => StatusType::ACTIVE,
                'user_type' => UserType::JOB_SEEKER,
            ]);

            $socialUser->roles()->attach($role->id);

            if ($socialUser->user_type == UserType::JOB_SEEKER) {
                PersonalInformation::create([
                    'user_id' => $socialUser->id,
                    'first_name' => $socialUser->name,
                    'last_name' => $socialUser->name,
                ]);
            }

            Auth::login($socialUser);
        } else {
            Auth::login($modelUser);
        }

        return redirect()->route('home')->withSuccess('successfully login');
    }

}
