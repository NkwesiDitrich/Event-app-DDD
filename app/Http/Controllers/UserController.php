<?php
namespace App\Http\Controllers;
use Exception;
use App\Models\User;
use App\Mail\OTPMail;
use App\Helper\JWTToken;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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
     * FIXED: User Registration with Detailed Validation Messages
     * Now provides specific error messages for duplicate fields
     */
    function UserRegistration(Request $request){
        try {
            // PERFORMANCE OPTIMIZATION: Use Laravel's built-in validation with custom messages
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'mobile' => 'required|string|unique:users,mobile|max:20',
                'password' => 'required|string|min:6|max:255',
            ], [
                // CUSTOM ERROR MESSAGES for specific field validation failures
                'firstName.required' => 'First name is required',
                'firstName.max' => 'First name cannot exceed 255 characters',
                'lastName.required' => 'Last name is required', 
                'lastName.max' => 'Last name cannot exceed 255 characters',
                'email.required' => 'Email address is required',
                'email.email' => 'Please enter a valid email address',
                'email.unique' => 'This email address is already registered. Please use a different email or try logging in.',
                'email.max' => 'Email address cannot exceed 255 characters',
                'mobile.required' => 'Mobile number is required',
                'mobile.unique' => 'This mobile number is already registered. Please use a different mobile number.',
                'mobile.max' => 'Mobile number cannot exceed 20 characters',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 6 characters long',
                'password.max' => 'Password cannot exceed 255 characters',
            ]);

            // DETAILED VALIDATION: Check for specific validation failures
            if ($validator->fails()) {
                $errors = $validator->errors();
                
                // PRIORITY ERROR MESSAGES: Return the most relevant error first
                if ($errors->has('email')) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $errors->first('email')
                    ], 422);
                }
                
                if ($errors->has('mobile')) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $errors->first('mobile')
                    ], 422);
                }
                
                // Return first validation error if not email/mobile specific
                return response()->json([
                    'status' => 'failed',
                    'message' => $errors->first()
                ], 422);
            }

            // ADDITIONAL MANUAL CHECKS: Double-check for duplicates with custom messages
            $existingEmail = User::where('email', $request->input('email'))->first();
            if ($existingEmail) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This email address is already registered. Please use a different email or try logging in.'
                ], 422);
            }

            $existingMobile = User::where('mobile', $request->input('mobile'))->first();
            if ($existingMobile) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This mobile number is already registered. Please use a different mobile number.'
                ], 422);
            }

            // PERFORMANCE OPTIMIZATION: Create user with validated data
            $user = User::create([
                'firstName' => trim($request->input('firstName')),
                'lastName' => trim($request->input('lastName')),
                'email' => strtolower(trim($request->input('email'))),
                'mobile' => trim($request->input('mobile')),
                'password' => Hash::make($request->input('password')),
            ]);

            // LOG SUCCESS for monitoring
            Log::info('New user registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->firstName . ' ' . $user->lastName
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User Registration Successfully! You can now login with your credentials.'
            ], 201);

        } catch (Exception $e) {
            // LOG ERROR for debugging
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
                'trace' => $e->getTraceAsString()
            ]);

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
     * FIXED: Profile Update Method
     * Issue: Was using $request->header('email') but frontend doesn't send email in headers
     * Solution: Use auth()->id() to get current user and update directly
     */
    function UpdateProfile(Request $request){
        try{
            // CRITICAL FIX: Use authenticated user ID instead of email from headers
            $user_id = auth()->id();
            
            if (!$user_id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated. Please login again.',
                ], 401);
            }

            // VALIDATION: Validate input data with custom messages
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'password' => 'required|string|min:6|max:255',
            ], [
                'firstName.required' => 'First name is required',
                'lastName.required' => 'Last name is required',
                'mobile.required' => 'Mobile number is required',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 6 characters long',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // PERFORMANCE OPTIMIZATION: Get current user data for comparison
            $currentUser = User::find($user_id);
            if (!$currentUser) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found. Please login again.',
                ], 404);
            }

            // DUPLICATE CHECK: Check if mobile number is already used by another user
            $existingMobile = User::where('mobile', $request->input('mobile'))
                                  ->where('id', '!=', $user_id)
                                  ->first();
            
            if ($existingMobile) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This mobile number is already used by another user. Please use a different mobile number.'
                ], 422);
            }

            // PERFORMANCE OPTIMIZATION: Only update fields that have changed
            $updateData = [];
            
            if (trim($request->input('firstName')) !== $currentUser->firstName) {
                $updateData['firstName'] = trim($request->input('firstName'));
            }
            
            if (trim($request->input('lastName')) !== $currentUser->lastName) {
                $updateData['lastName'] = trim($request->input('lastName'));
            }
            
            if (trim($request->input('mobile')) !== $currentUser->mobile) {
                $updateData['mobile'] = trim($request->input('mobile'));
            }
            
            // ALWAYS UPDATE PASSWORD if provided (since it's hashed, we can't compare)
            if ($request->input('password')) {
                $updateData['password'] = Hash::make($request->input('password'));
            }

            // CRITICAL FIX: Update using user ID instead of email
            if (!empty($updateData)) {
                $updated = User::where('id', $user_id)->update($updateData);
                
                if ($updated) {
                    // LOG SUCCESS for monitoring
                    Log::info('User profile updated successfully', [
                        'user_id' => $user_id,
                        'updated_fields' => array_keys($updateData)
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Profile updated successfully!',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Failed to update profile. Please try again.',
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No changes detected. Profile is already up to date.',
                ], 200);
            }

        }catch (Exception $exception){
            // LOG ERROR for debugging
            Log::error('Profile update failed', [
                'user_id' => auth()->id(),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Profile update failed due to a server error. Please try again later.',
            ], 500);
        }
    }

}
