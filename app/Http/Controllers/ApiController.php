<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use DB;

class ApiController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'mobile' => 'required|unique:users',
                'password' => 'required|min:6',
                'address_1' => 'required',
                'city' => 'required',
                'state' => 'required',
                'pincode' => 'required',
            ], [
                'email.unique' => 'Email already registered.',
                'mobile.unique' => 'Mobile number already registered.',
            ]);

            if ($validator->fails()) {
                $errorMessages = $validator->errors()->all();
                $errorMessagesString = implode(', ', $errorMessages);
                return response()->json(['message' => 'Validation failed : '.$errorMessagesString, 'status' => false]);
            }

            $user = User::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'pwd' => bcrypt($request->password),
            ]);

            $user->address()->create([
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
            ]);
            DB::commit();

            return response()->json(['message' => 'Account created successfully!', 'status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while creating the account: ' . $e->getMessage(), 'status' => false]);
        }
    }

    public function validateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            $errorMessagesString = implode(', ', $errorMessages);
            return response()->json(['message' => 'Validation failed : '.$errorMessagesString, 'status' => false]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->pwd)) {
            return response()->json(['message' => 'Invalid credentials!', 'status' => false]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login successful!', 'token' => $token, 'status' => true]);
    }

    public function getProfileDetails(Request $request)
    {
        $user = User::with('address')->find(auth()->id());

        if (!$user) {
            return response()->json(['message' => 'User not found!', 'status' => false]);
        }

        return response()->json(['message' => 'Success', 'data' => $user, 'status' => true]);
    }
}
