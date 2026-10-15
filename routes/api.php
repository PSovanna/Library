<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookAuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookTypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('authors', AuthorController::class)->parameters(['authors' => 'record']);
Route::apiResource('book-types', BookTypeController::class)->parameters(['book-types' => 'record']);
Route::apiResource('books', BookController::class)->parameters(['books' => 'record']);
Route::get('book-authors', [BookAuthorController::class, 'index']);
Route::post('book-authors', [BookAuthorController::class, 'store']);
Route::get('book-authors/{bookId}/{authorId}', [BookAuthorController::class, 'show'])->whereNumber(['bookId', 'authorId']);
Route::match(['put', 'patch'], 'book-authors/{bookId}/{authorId}', [BookAuthorController::class, 'update'])->whereNumber(['bookId', 'authorId']);
Route::delete('book-authors/{bookId}/{authorId}', [BookAuthorController::class, 'destroy'])->whereNumber(['bookId', 'authorId']);
