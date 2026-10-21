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
            'DOB' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'POB' => 'nullable|string|max:255',
            'Address' => 'nullable|string|max:500',
            'Phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 ()-]{5,29}$/'],
            'Email' => 'nullable|email|max:255',
            'Photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $record = $this->saveWithPhoto(new Author, $request, $data, 'authors');

        return $this->success('Author inserted successfully.', $record, 201);
    }

    public function show(Author $record)
    {

        return $this->success('Author retrieved successfully.', $record);
    }

    public function update(Request $request, Author $record)
    {

        $rules = ['AuthorName' => 'required|string|max:255',
            'Gender' => 'nullable|string|max:20',
            'DOB' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'POB' => 'nullable|string|max:255',
            'Address' => 'nullable|string|max:500',
            'Phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 ()-]{5,29}$/'],
            'Email' => 'nullable|email|max:255',
            'Photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'];
        foreach ($rules as &$rule) {
            $rule = is_array($rule) ? array_merge(['sometimes'], $rule) : 'sometimes|'.$rule;
        }
        unset($rule);
        $data = $request->validate($rules);
        $this->saveWithPhoto($record, $request, $data, 'authors');

        return $this->success('Author updated successfully.', $record);
    }

    public function destroy(Author $record)
    {

        try {
            $record->delete();
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23503'])) {
                return response()->json(['success' => false, 'message' => 'Remove related records before deleting this record.'], 409);
            }
            throw $e;
        }

        $this->deletePhoto($record->Photo);

        return $this->success('Author deleted successfully.');
    }
}
