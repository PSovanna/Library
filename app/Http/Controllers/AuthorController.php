<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return response()->json(Author::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'AuthorName' => 'required|string|max:255',
            'Gender' => 'nullable|string|max:20',
            'DOB' => 'nullable|date',
            'POB' => 'nullable|string|max:255',
            'Address' => 'nullable|string|max:500',
            'Phone' => 'nullable|string|max:30',
            'Email' => 'nullable|email|max:255',
            'Photo' => 'nullable|string|max:2048',
        ]);

        return response()->json(Author::create($data), 201);
    }

    public function show(Author $record)
    {

        return response()->json($record);
    }

    public function update(Request $request, Author $record)
    {

        $rules = ['AuthorName' => 'required|string|max:255',
            'Gender' => 'nullable|string|max:20',
            'DOB' => 'nullable|date',
            'POB' => 'nullable|string|max:255',
            'Address' => 'nullable|string|max:500',
            'Phone' => 'nullable|string|max:30',
            'Email' => 'nullable|email|max:255',
            'Photo' => 'nullable|string|max:2048'];
        foreach ($rules as &$rule) {
            $rule = 'sometimes|'.$rule;
        }
        unset($rule);
        $data = $request->validate($rules);
        $record->update($data);

        return response()->json($record);
    }

    public function destroy(Author $record)
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
