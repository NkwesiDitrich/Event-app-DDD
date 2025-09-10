<?php
namespace App\Http\Controllers;
use Exception;
use App\Models\User;
use App\Mail\OTPMail;
use App\Helper\JWTToken;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    function LoginPage():View{
        return view('backend.pages.auth.login-page');
    }

    function RegistrationPage():View{
        return view('backend.pages.auth.registration-page');
    }
    function SendOtpPage():View{
        return view('backend.pages.auth.send-otp-page');
    }
    function VerifyOTPPage():View{
        return view('backend.pages.auth.verify-otp-page');
    }

    function ResetPasswordPage():View{
        return view('backend.pages.auth.reset-pass-page');
    }

    function ProfilePage():View{
        return view('backend.pages.dashboard.profile-page');
    }

    /**
     * LIGHTNING FAST: User Registration with Optimized Validation
     * Maintains all validation features but with maximum speed
     */
    function UserRegistration(Request $request){
        try {
            // SPEED OPTIMIZATION: Fast inline validation
            $firstName = trim($request->input('firstName'));
            $lastName = trim($request->input('lastName'));
            $email = strtolower(trim($request->input('email')));
            $mobile = trim($request->input('mobile'));
            $password = $request->input('password');

            // FAST VALIDATION: Quick checks with immediate return
            if (empty($firstName)) {
                return response()->json(['status' => 'failed', 'message' => 'First name is required'], 422);
            }
            if (empty($lastName)) {
                return response()->json(['status' => 'failed', 'message' => 'Last name is required'], 422);
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['status' => 'failed', 'message' => 'Please enter a valid email address'], 422);
            }
            if (empty($mobile)) {
                return response()->json(['status' => 'failed', 'message' => 'Mobile number is required'], 422);
            }
            if (empty($password) || strlen($password) < 6) {
                return response()->json(['status' => 'failed', 'message' => 'Password must be at least 6 characters long'], 422);
            }

            // SUPER FAST DUPLICATE CHECK: Single query for both email and mobile
            $existing = DB::table('users')
                ->select('email', 'mobile')
                ->where('email', $email)
                ->orWhere('mobile', $mobile)
                ->first();

            if ($existing) {
                if ($existing->email === $email) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'This email address is already registered. Please use a different email or try logging in.'
                    ], 422);
                }
                if ($existing->mobile === $mobile) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'This mobile number is already registered. Please use a different mobile number.'
                    ], 422);
                }
            }

            // FAST INSERT: Direct database insert for maximum speed
            $userId = DB::table('users')->insertGetId([
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'mobile' => $mobile,
                'password' => Hash::make($password),
                'otp' => '0',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User Registration Successfully! You can now login with your credentials.'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Registration failed due to a server error. Please try again later.'
            ], 500);
        }
    }

    function UserLogin(Request $request){
        //dd($request->all());
       /*$count=User::where('email','=',$request->input('email'))
            ->where('password','=',$request->input('password'))
            ->select('id')->first();

       if($count!==null){
           // User Login-> JWT Token Issue
           $token=JWTToken::CreateToken($request->input('email'),$count->id);
           return response()->json([
               'status' => 'success',
               'message' => 'User Login Successful',
           ],200)->cookie('token',$token,60*24*30);
       }
       else{
           return response()->json([
               'status' => 'failed',
               'message' => 'unauthorized'
           ],200);

       }*/

       $data = [
        'email' => $request->email,
        'password' => $request->password
    ];

    if (Auth::attempt($data,true)) {
        return response()->json([
            'status' => 'success',
            'message' => 'User Login Successful',
        ],200);
        // return redirect()->route('home');
    } else {
        return response()->json([
            'status' => 'failed',
            'message' => 'unauthorized'
        ],200);
        //return redirect()->back();
    }

    }

    function SendOTPCode(Request $request){

        $email=$request->input('email');
        $otp=rand(1000,9999);
        $count=User::where('email','=',$email)->count();

        if($count==1){
            // OTP Email Address
            Mail::to($email)->send(new OTPMail($otp));
            // OTO Code Table Update
            User::where('email','=',$email)->update(['otp'=>$otp]);

            return response()->json([
                'status' => 'success',
                'message' => '4 Digit OTP Code has been send to your email !'
            ],200);
        }
        else{
            return response()->json([
                'status' => 'failed',
                'message' => 'unauthorized'
            ]);
        }
    }

    function VerifyOTP(Request $request){
        $email=$request->input('email');
        $otp=$request->input('otp');
        $count=User::where('email','=',$email)
            ->where('otp','=',$otp)->count();

        if($count==1){
            // Database OTP Update
            User::where('email','=',$email)->update(['otp'=>'0']);

            // Pass Reset Token Issue
            $token=JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
                'status' => 'success',
                'message' => 'OTP Verification Successful',
            ],200)->cookie('token',$token,60*24*30);

        }
        else{
            return response()->json([
                'status' => 'failed',
                'message' => 'unauthorized'
            ],200);
        }
    }

    function ResetPassword(Request $request){
        try{
            $email=$request->header('email');
            $password=$request->input('password');
            User::where('email','=',$email)->update(['password'=>$password]);
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
            ],200);

        }catch (Exception $exception){
            return response()->json([
                'status' => 'fail',
                'message' => 'Something Went Wrong',
            ],200);
        }
    }

    function UserLogout(){
        Auth::logout();
        return redirect()->route('login');
        // return redirect('/userLogin')->cookie('token','',-1);
    }


    function UserProfile(){
         
        $user=User::find(auth()->id());
        return response()->json([
            'status' => 'success',
            'message' => 'Request Successful',
            'data' => $user
        ],200);
    }

    /**
     * ULTRA FAST: Profile Update - Eliminates Password Hashing Bottleneck
     * Password hashing can take 2-5 seconds - this version makes it optional and super fast
     */
    function UpdateProfile(Request $request){
        try{
            // SPEED OPTIMIZATION: Get user ID immediately
            $user_id = auth()->id();
            
            if (!$user_id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated. Please login again.',
                ], 401);
            }

            // ULTRA FAST VALIDATION: Quick inline validation
            $firstName = trim($request->input('firstName'));
            $lastName = trim($request->input('lastName'));
            $mobile = trim($request->input('mobile'));
            $password = $request->input('password');

            if (empty($firstName)) {
                return response()->json(['status' => 'failed', 'message' => 'First name is required'], 422);
            }
            if (empty($lastName)) {
                return response()->json(['status' => 'failed', 'message' => 'Last name is required'], 422);
            }
            if (empty($mobile)) {
                return response()->json(['status' => 'failed', 'message' => 'Mobile number is required'], 422);
            }

            // CRITICAL SPEED FIX: Make password update optional to avoid hashing bottleneck
            $updateData = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mobile' => $mobile,
                'updated_at' => now()
            ];

            // OPTIONAL PASSWORD UPDATE: Only hash and update password if it's provided and not empty
            if (!empty($password) && strlen($password) >= 6) {
                // SPEED OPTIMIZATION: Only check mobile duplicates if we're actually updating
                $existingMobile = DB::table('users')
                    ->where('mobile', $mobile)
                    ->where('id', '!=', $user_id)
                    ->exists();
                
                if ($existingMobile) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'This mobile number is already used by another user. Please use a different mobile number.'
                    ], 422);
                }

                // Add password to update data only if provided
                $updateData['password'] = Hash::make($password);
                
                // LIGHTNING FAST UPDATE: Direct database update with password
                $updated = DB::table('users')
                    ->where('id', $user_id)
                    ->update($updateData);
                    
                if ($updated) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Profile and password updated successfully!',
                    ], 200);
                }
            } else {
                // ULTRA FAST UPDATE: Update without password (no hashing needed)
                // Still check mobile duplicates for data integrity
                $existingMobile = DB::table('users')
                    ->where('mobile', $mobile)
                    ->where('id', '!=', $user_id)
                    ->exists();
                
                if ($existingMobile) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'This mobile number is already used by another user. Please use a different mobile number.'
                    ], 422);
                }

                // INSTANT UPDATE: No password hashing = instant response
                $updated = DB::table('users')
                    ->where('id', $user_id)
                    ->update($updateData);
                    
                if ($updated) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Profile updated successfully! (Password unchanged)',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'No changes detected. Profile is already up to date.',
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to update profile. Please try again.',
            ], 500);

        }catch (Exception $exception){
            return response()->json([
                'status' => 'failed',
                'message' => 'Profile update failed due to a server error. Please try again later.',
            ], 500);
        }
    }

}
