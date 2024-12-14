<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function create(Request $request)
    {
        try {
            // Check if the user is authenticated
            if (Auth::guard('api')->check()) {
                // Validate request parameters
                $request->validate([
                    'title' => 'string|max:255',
                    'description' => 'string|max:255',
                    'isbn' => 'string|max:255',
                    'date' => 'date|date_format:Y-m-d|max:255',
                   
                    'second_author' => 'string|max:255',
                    // Add any other validation rules for non-image fields
                ]);
                $authors = Author::all();

                $combinedNames = $authors->map(function ($author) {
                    return $author->first_name . ' ' . $author->last_name;
                })->toArray();
                // Save data to the database
                $book = new Book([
                    'title' => $request->title,
                    'description' => $request->description,
                    'isbn' => $request->isbn,
                    'date' => $request->date,
                    'author' => $request->author,
                    'second_author' => $request->second_author,
                ]);
                $book->save();
                   // Retrieve all records from the 'Book' model
                $books = Book::all();
    
                // Return a JSON response
                return response()->json([
                    'data' => $book,
                    'message' => 'Data including Title, Description, ISBN, Date, Author, and Second Author created successfully',
                    'books' => $books,
                    'status' => true
                ], 200);
            } else {
                // User is not authenticated, return unauthorized response
                return response()->json(['error' => 'Please Login'], 401);
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
        $tab = Book::findOrFail($create);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string|max:255',
            'isbn' => 'string|max:255',
            'date' => 'date|date_format:Y-m-d|max:255',
            'author' => 'string|max:255',
            'second_author' => 'string|max:255',
            // Remove image validation
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update non-image fields
        $tab->update($request->only(['title', 'description', 'isbn', 'date', 'author','second_author']));

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
public function show(Book $create)
{
    if (Auth::guard('api')->check()) {
    return response()->json($create);
} else {
    // User is not authenticated, return unauthorized response
    return response()->json(['error' => 'Unauthorized'], 401);
}
}

public function destroy(Book $create)
{
    try {
        if (Auth::guard('api')->check()) {
        $create->delete();
// echo $create;
        return response()->json(null, 204, ['message' => 'Data deleted successfully', 'deleted_data' => $create]);
    } else {
        // User is not authenticated, return unauthorized response
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}

public function search(Request $request)
{
    try {
        // Validate request parameters
        $request->validate([
            'title' => 'string|max:255',
            'author' => 'string|max:255',
        ]);

        // Build the query based on search parameters
        $query = Book::query();

        $query->where('title', 'like', '%' . $request->title . '%')
            ->where(function ($query) use ($request) {
                $query->where('author', 'like', '%' . $request->author . '%')
                    ->orWhere('second_author', 'like', '%' . $request->author . '%');
            })
            ->when(!is_null($request->author), function ($query) {
                // Exclude entries with null authors and null second authors
                $query->whereNotNull('author')->orWhereNotNull('second_author');
            });

        Log::info('Generated Query: ' . $query->toSql());

        // Execute the query and get the result
        $result = $query->get();

        // Return a JSON response
        return response()->json([
            'data' => $result,
            'message' => 'Search results based on Title and Author',
            'status' => true
        ], 200);
    } catch (\Exception $e) {
        // Handle other exceptions
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}



        }


