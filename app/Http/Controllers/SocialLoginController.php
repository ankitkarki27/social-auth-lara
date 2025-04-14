<?php

namespace App\Http\Controllers;

use App\Models\SocialLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function toProvider($driver){
    return Socialite::driver($driver)->redirect();

    }

    public function handleCallback($driver){

        $socialUser=Socialite::driver($driver)->user();

        // check if user acc is found in userlofin model or not
        $User_account= SocialLogin::where('provider',$driver)
        ->where('provider_id',$socialUser->getId())
        ->first();

    if($User_account){
        Auth::login($User_account->user);
        Session::regenerate();
        return redirect()->intended('dashboard');
    }

     // Check if user already exists
    $db_user=User::where('email',$socialUser->getEmail())->first();
    if($db_user){
        // Save SocialLogin
        SocialLogin::create([
            'provider'=>$driver,
            'provider_id'=>$socialUser->getId(),
            'user_id'=>$db_user->id
        ]);
        }  
        // create new
         else{
            $db_user=User::create([
                'name'=>$socialUser->getName(), //name in gmail
                'email'=>$socialUser->getEmail(),
                'password'=>bcrypt(rand(1000,9999))
            ]);
          
        } 
        // log user in
        Auth::login($db_user);
        Session::regenerate();
        return redirect()->intended('dashboard'); 
    }
}
