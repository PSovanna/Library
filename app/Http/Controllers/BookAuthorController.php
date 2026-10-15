<?php

namespace App\Http\Controllers;

use App\Models\BookAuthor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookAuthorController extends Controller
{
    public function index()
    {
        return response()->json(BookAuthor::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'BookID' => 'required|integer|exists:tblBook,BookID',
            'AuthorID' => 'required|integer|exists:tblAuthor,AuthorID',
            'AuthorDate' => 'nullable|date',
            'Remark' => 'nullable|string',
        ]);
        if (BookAuthor::where('BookID', $data['BookID'])->where('AuthorID', $data['AuthorID'])->exists()) {
            throw ValidationException::withMessages(['AuthorID' => 'This author is already linked to this book.']);
        }

        return response()->json(BookAuthor::create($data), 201);
    }

    public function show(int $bookId, int $authorId)
    {
        $record = BookAuthor::where('BookID', $bookId)->where('AuthorID', $authorId)->firstOrFail();

        return response()->json($record);
    }

    public function update(Request $request, int $bookId, int $authorId)
    {
        $record = BookAuthor::where('BookID', $bookId)->where('AuthorID', $authorId)->firstOrFail();
        $rules = ['AuthorDate' => 'nullable|date', 'Remark' => 'nullable|string'];
        foreach ($rules as &$rule) {
            $rule = 'sometimes|'.$rule;
        }
        unset($rule);
        $data = $request->validate($rules);
        if ($data !== []) {
            BookAuthor::where('BookID', $bookId)->where('AuthorID', $authorId)->update($data);
        }
        $record = BookAuthor::where('BookID', $bookId)->where('AuthorID', $authorId)->firstOrFail();

        return response()->json($record);
    }

    public function destroy(int $bookId, int $authorId)
    {
        $record = BookAuthor::where('BookID', $bookId)->where('AuthorID', $authorId)->firstOrFail();
        try {
            BookAuthor::where('BookID', $bookId)->where('AuthorID', $authorId)->delete();
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23503'])) {
                return response()->json(['message' => 'Remove related records before deleting this record.'], 409);
            }
            throw $e;
        }

        return response()->noContent();
    }
}
