<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return response()->json(Book::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'BookTitle' => 'required|string|max:255',
            'BookTypeID' => 'required|integer|exists:tblBookType,BookTypeID',
            'PublishDate' => 'nullable|date',
            'NumOfPages' => 'nullable|integer|min:0',
            'NumOfCopies' => 'nullable|integer|min:0',
            'Edition' => 'nullable|string|max:100',
            'Publisher' => 'nullable|string|max:255',
            'BookSource' => 'nullable|string|max:255',
            'Remark' => 'nullable|string',
        ]);

        return response()->json(Book::create($data), 201);
    }

    public function show(Book $record)
    {

        return response()->json($record);
    }

    public function update(Request $request, Book $record)
    {

        $rules = ['BookTitle' => 'required|string|max:255',
            'BookTypeID' => 'required|integer|exists:tblBookType,BookTypeID',
            'PublishDate' => 'nullable|date',
            'NumOfPages' => 'nullable|integer|min:0',
            'NumOfCopies' => 'nullable|integer|min:0',
            'Edition' => 'nullable|string|max:100',
            'Publisher' => 'nullable|string|max:255',
            'BookSource' => 'nullable|string|max:255',
            'Remark' => 'nullable|string'];
        foreach ($rules as &$rule) {
            $rule = 'sometimes|'.$rule;
        }
        unset($rule);
        $data = $request->validate($rules);
        $record->update($data);

        return response()->json($record);
    }

    public function destroy(Book $record)
    {

        try {
            $record->delete();
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23503'])) {
                return response()->json(['message' => 'Remove related records before deleting this record.'], 409);
            }
            throw $e;
        }

        return response()->noContent();
    }
}
