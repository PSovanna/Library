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
            'NumOfPages' => 'nullable|integer|min:0|max:4294967295',
            'NumOfCopies' => 'nullable|integer|min:0|max:4294967295',
            'Edition' => 'nullable|string|max:100',
            'Publisher' => 'nullable|string|max:255',
            'BookSource' => 'nullable|string|max:255',
            'Photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'Remark' => 'nullable|string',
        ]);

        $record = $this->saveWithPhoto(new Book, $request, $data, 'books');

        return $this->success('Book inserted successfully.', $record, 201);
    }

    public function show(Book $record)
    {

        return $this->success('Book retrieved successfully.', $record);
    }

    public function update(Request $request, Book $record)
    {

        $rules = ['BookTitle' => 'required|string|max:255',
            'BookTypeID' => 'required|integer|exists:tblBookType,BookTypeID',
            'PublishDate' => 'nullable|date',
            'NumOfPages' => 'nullable|integer|min:0|max:4294967295',
            'NumOfCopies' => 'nullable|integer|min:0|max:4294967295',
            'Edition' => 'nullable|string|max:100',
            'Publisher' => 'nullable|string|max:255',
            'BookSource' => 'nullable|string|max:255',
            'Photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'Remark' => 'nullable|string'];
        foreach ($rules as &$rule) {
            $rule = 'sometimes|'.$rule;
        }
        unset($rule);
        $data = $request->validate($rules);
        $this->saveWithPhoto($record, $request, $data, 'books');

        return $this->success('Book updated successfully.', $record);
    }

    public function destroy(Book $record)
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

        return $this->success('Book deleted successfully.');
    }
}
