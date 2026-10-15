<?php

namespace App\Http\Controllers;

use App\Models\BookType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class BookTypeController extends Controller
{
    public function index()
    {
        return response()->json(BookType::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'BookTypeName' => 'required|string|max:255',
        ]);

        return response()->json(BookType::create($data), 201);
    }

    public function show(BookType $record)
    {

        return response()->json($record);
    }

    public function update(Request $request, BookType $record)
    {

        $rules = ['BookTypeName' => 'required|string|max:255'];
        foreach ($rules as &$rule) {
            $rule = 'sometimes|'.$rule;
        }
        unset($rule);
        $data = $request->validate($rules);
        $record->update($data);

        return response()->json($record);
    }

    public function destroy(BookType $record)
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
