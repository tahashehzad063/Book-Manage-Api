<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Support\Facades\Log;
use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;

class LibraryController extends Controller
{
    public function create(Request $request)
    {
        try {
            if (Auth::guard('api')->check()) {
                // Validate request parameters
                $request->validate([
                    'name' => 'string|max:255',
                    'email' => 'string|max:255',
                    'borrowed' => 'string|max:255',
                    'borrowed_date' => 'date|date_format:Y-m-d|max:255',
                    'due_date' => 'date|date_format:Y-m-d|max:255',
                ]);
    
                // Check if there are books with matching authors
               
                $book = new Library([
                    'name' => $request->name,
                    'email' => $request->email,
                    'borrowed' => $request->borrowed,
                    'borrowed_date' => $request->borrowed_date,
                    'due_date' => $request->due_date,
                   
                ]);
    
                $book->save();
    
                // Retrieve all records from the 'Author' model
                // $authors = Library::all();
    
                // Return a JSON response
                return response()->json([
                    'data' => $book,
                    'message' => 'Data including first_name, last_name, list, created successfully',
                    // 'authors' => $authors,
                    'status' => true
                ], 200);
            } else {
                // User is not authenticated, return Add login Tokken response
                return response()->json(['error' => 'Add login Tokken'], 401);
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
  
    public function update(Request $request, $borrow)
    {
        try {
            if (Auth::guard('api')->check()) {
            $tab = Library::findOrFail($borrow);
    
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'string|max:255',
                'borrowed' => 'string|max:255',
                'borrowed_date' => 'date|date_format:Y-m-d|max:255',
                'due_date' => 'date|date_format:Y-m-d|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            // Update non-image fields
            $tab->update($request->only(['name', 'email', 'borrowed','borrowed_date','due_date']));
    
            // Return the updated record
            return response()->json(['message' => 'Record updated successfully', 'updated_tab' => $tab], 200);
        } else {
            // User is not authenticated, return Add login Tokken response
            return response()->json(['error' => 'Add login Tokken'], 401);
        }
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function destroy(Library $borrow)
{
    try {
        if (Auth::guard('api')->check()) {
        $borrow->delete();
// echo $create;
        return response()->json(null, 204, ['message' => 'Data deleted successfully', 'deleted_data' => $borrow]);
    } else {
        // User is not authenticated, return Add login Tokken response
        return response()->json(['error' => 'Add login Tokken'], 401);
    }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Deleted'], 500);
    }
}

public function show(Library $borrow)
{
    if (Auth::guard('api')->check()) {
    return response()->json($borrow);
} else {
    // User is not authenticated, return Add login Tokken response
    return response()->json(['error' => 'Add login Tokken'], 401);
}
}

public function borrowBook(Request $request)
{
    try {
        // Retrieve authenticated user's information
        if (Auth::guard('api')->check()) {
        $user = Auth::guard('api')->user();
        $bookTitle = $request->input('title');

        // Retrieve the book title only
        $book = Book::where('title', trim($bookTitle))->select('title')->first();

        Log::info('Retrieved Book: ' . json_encode($book));
        $request->validate([
            'borrowed_date' => 'required|date|date_format:Y-m-d',
            'due_date' => 'required|date|date_format:Y-m-d',
        ]);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $library = Library::updateOrCreate(
            ['borrowed' => $book->title], // Use the same field name in both places
            [
                'name' => $request->name,
                // 'email' => $user->email,
                'email' => $request->email,
                'borrowed' => $book->title,
                'borrowed_date' => $request->borrowed_date,
                'due_date' => $request->due_date,
            ]
        );

        return response()->json(['message' => 'Book borrowed successfully', 'borrowed_book' => $library], 200);
    } else {
        // User is not authenticated, return Add login Tokken response
        return response()->json(['error' => 'Add login Tokken'], 401);
    } } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Exception: ' . $e->getMessage());
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}
public function return(Request $request)
{
    try {
        if (Auth::guard('api')->check()) {
        // Retrieve authenticated user's information
        $user = Auth::guard('api')->user();

      

        // Fetch the book using the book title from the request
        $bookTitle = $request->input('book_title');
        $library = Library::where('borrowed', trim($bookTitle))->first();

        if (!$library) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        // Update the library record with return information
        $library->update([
            'returned' =>  $bookTitle,
            'return_date' => now(),
        ]);

        return response()->json(['message' => 'Book returned successfully', 'returned_book' => $library], 200);
    } else {
        // User is not authenticated, return Add login Tokken response
        return response()->json(['error' => 'Add login Tokken'], 401);
    }  } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Exception: ' . $e->getMessage());
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}

}


