<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class AutherController extends Controller
{
   

    /**
     * Display a listing of the resource.
     */
    public function create(Request $request)
    {
        try {
            if (Auth::guard('api')->check()) {
                // Validate request parameters
                $request->validate([
                    'first_name' => 'string|max:255',
                    'last_name' => 'string|max:255',
                ]);
    
                // Check if there are books with matching authors
                $matchingBooks = Book::where(function($query) use ($request) {
                    $query->where('author', $request->first_name . ' ' . $request->last_name)
                          ->orWhere('second_author', $request->first_name . ' ' . $request->last_name);
                })->get();
    
                // Create author with appropriate list value
                $listValue = $matchingBooks->isNotEmpty() ? $matchingBooks->pluck('title')->toJson() : 'no book by this author';
    
                $book = new Author([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'list' => $listValue,
                ]);
    
                $book->save();
    
                // Retrieve all records from the 'Author' model
                $authors = Author::all();
    
                // Return a JSON response
                return response()->json([
                    'data' => $book,
                    'message' => 'Data including first_name, last_name, list, created successfully',
                    'authors' => $authors,
                    'status' => true
                ], 200);
            } else {
                // User is not authenticated, return unauthorized response
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (QueryException $e) {
            // Handle database query errors
            return response()->json(['error' => 'Database error'], 500);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
    
    
public function update(Request $request, $create)
{
    try {
        if (Auth::guard('api')->check()) {
        $tab = Author::findOrFail($create);

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'list' => 'string|max:255',
            // Remove image validation
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update non-image fields
        $tab->update($request->only(['first_name', 'last_name', 'list']));

        // Return the updated record
        return response()->json(['message' => 'Record updated successfully', 'updated_tab' => $tab], 200);
    } else {
        // User is not authenticated, return unauthorized response
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Record not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}
public function show(Author $author)
{
    if (Auth::guard('api')->check()) {
    return response()->json($author);
} else {
    // User is not authenticated, return unauthorized response
    return response()->json(['error' => 'Unauthorized'], 401);
}
}

public function destroy(Author $author)
{
    try {
        if (Auth::guard('api')->check()) {
        $author->delete();
// echo $create;
        return response()->json(null, 204, ['message' => 'Data deleted successfully', 'deleted_data' => $author]);
    } else {
        // User is not authenticated, return unauthorized response
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Deleted'], 500);
    }
}



        }

