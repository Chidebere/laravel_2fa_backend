<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Hobby;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Authenticates a user and generates an access token for that user
    public function login(Request $request)
    {

         // Perform validation check
         $validator = Validator::make($request->all(), [
            'email_phone' => 'required',
            'password' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        $login_type = filter_var($request->input('email_phone'), FILTER_VALIDATE_EMAIL )
        ? 'email'
        : 'phone_number';

        $request->merge([
            $login_type => $request->input('email_phone')
        ]);


            if (Auth::attempt($request->only($login_type, 'password'))) {

                $user = User::with(['hobbies'])->where('id', Auth::user()->id)->first();

                if($user->isVerified == true) {

                    $token = Auth::user()->createToken('PWAToken')->accessToken;

                    return response()->json(['access_token' => $token, 'user' => $user]);
                } else {
                    return response()->json(['error' => 'Account has not been verified yet']);
                }
            } else{
                return response()->json(['error' => 'These credentials do not match our records']);
        }

    }


    // protected function credentials(Request $request)
    // {
    //     if(is_numeric($request->get('email'))){
    //     return ['phone_number'=>$request->get('email'),'password'=>$request->get('password')];
    //     }
    //     elseif (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
    //     return ['email' => $request->get('email'), 'password'=>$request->get('password')];
    //     }
    //     return ['username' => $request->get('email'), 'password'=>$request->get('password')];
    // }



    public function register(Request $request)
    {
        // Perform validation check
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:90',
            'phone_number' => 'required|min:11|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'hobbies' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        $data = $request->only(['name', 'phone_number', 'email', 'password', 'verification_channel', 'hobbies']);
        $data['password'] = bcrypt($data['password']);

      /* Get credentials from .env */
       $token = getenv("TWILIO_AUTH_TOKEN");
       $twilio_sid = getenv("TWILIO_SID");
       $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
       $twilio = new Client($twilio_sid, $token);

       //Perform action based on verification channel type, whether to verify and send the token via SMS or Email
       if($data['verification_channel'] == 'SMS') {
            $twilio->verify->v2->services($twilio_verify_sid)
                ->verifications
                ->create($data['phone_number'], "sms");
        }  else {
            $twilio->verify->v2->services($twilio_verify_sid)
                ->verifications
                ->create($data['email'], "email");
        }

        //Create user account
        $user = User::create($data);

        // Loop through the values and save the record.
        foreach($data['hobbies'] as $hobby) {
            $hobbyData = new Hobby();
            $hobbyData->name = $hobby;
            $hobbyData->slug = Str::slug($hobby);
            $hobbyData->user_id = $user->id;
            $hobbyData->save();
        }

        return response()->json($user->phone_number);
    }


    public function verify(Request $request)
    {
        // Perform validation check
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|numeric',
            'phone_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=> 'Verification code must be a number']);
        }
        $data = $request->only(['phone_number', 'verification_code']);

        /* Get credentials from .env */
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_sid = getenv("TWILIO_SID");
        $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");

        $twilio = new Client($twilio_sid, $token);
        $verification = $twilio->verify->v2->services($twilio_verify_sid)
            ->verificationChecks
            ->create($data['verification_code'], array('to' => $data['phone_number']));

        // Update isVerified field if token returned is valid
        if ($verification->valid) {
            User::where('phone_number', $data['phone_number'])->update(['isVerified' => true]);

            return response()->json(['success' => 'Phone number verified']);
        } else {
            // Throw error with phone number if verification fails
            return response()->json([
                'error' => 'Invalid verification code entered!'
            ]);
        }
    }


    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->tokens->each(function ($token, $key) {
                $token->delete();
                // $token->revoke();
            });
        }
        return response()->json('Logged out successfully', 200);
    }


}
