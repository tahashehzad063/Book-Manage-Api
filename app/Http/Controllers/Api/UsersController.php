<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);
    
            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ]);
            }
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            return response()->json(['message' => 'User created successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'An error occurred while processing your request'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
{
    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
        'password' => 'sometimes|required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        throw ValidationException::withMessages([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ]);
    }

    $user->update($request->all());

    return response()->json($user);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204, ['message' => 'User deleted successfully']);
    }
}
