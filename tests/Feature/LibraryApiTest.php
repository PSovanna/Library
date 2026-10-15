<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_for_all_four_resources(): void
    {
        $author = $this->postJson('/api/authors', ['AuthorName' => 'Dara'])->assertCreated()->json('AuthorID');
        $type = $this->postJson('/api/book-types', ['BookTypeName' => 'Programming'])->assertCreated()->json('BookTypeID');
        $book = $this->postJson('/api/books', ['BookTitle' => 'Laravel', 'BookTypeID' => $type])->assertCreated()->json('BookID');
        $link = "/api/book-authors/$book/$author";
        $this->postJson('/api/book-authors', ['BookID' => $book, 'AuthorID' => $author])->assertCreated();
        $this->postJson('/api/book-authors', ['BookID' => $book, 'AuthorID' => $author])->assertUnprocessable();
        foreach (['authors', 'book-types', 'books', 'book-authors'] as $resource) {
            $this->getJson("/api/$resource")->assertOk()->assertJsonCount(1);
        }
        foreach (["/api/authors/$author", "/api/book-types/$type", "/api/books/$book", $link] as $url) {
            $this->getJson($url)->assertOk();
        }
        $this->patchJson("/api/authors/$author", ['AuthorName' => 'Updated'])->assertOk()->assertJsonPath('AuthorName', 'Updated');
        $this->putJson("/api/book-types/$type", ['BookTypeName' => 'Updated'])->assertOk()->assertJsonPath('BookTypeName', 'Updated');
        $this->patchJson("/api/books/$book", ['NumOfCopies' => 3])->assertOk()->assertJsonPath('NumOfCopies', 3);
        $this->patchJson($link, ['Remark' => 'Updated'])->assertOk()->assertJsonPath('Remark', 'Updated');
        $this->patchJson($link, [])->assertOk();
        $this->deleteJson("/api/books/$book")->assertConflict();
        $this->deleteJson("/api/authors/$author")->assertConflict();
        $this->deleteJson("/api/book-types/$type")->assertConflict();
        $this->deleteJson($link)->assertNoContent();
        $this->getJson($link)->assertNotFound();
        $this->deleteJson("/api/books/$book")->assertNoContent();
        $this->deleteJson("/api/authors/$author")->assertNoContent();
        $this->deleteJson("/api/book-types/$type")->assertNoContent();
    }

    public function test_validation_and_missing_records(): void
    {
        $this->postJson('/api/authors', [])->assertUnprocessable();
        $this->postJson('/api/book-types', [])->assertUnprocessable();
        $this->postJson('/api/books', ['BookTitle' => 'Invalid', 'BookTypeID' => 999, 'NumOfPages' => -1])->assertUnprocessable();
        $this->postJson('/api/book-authors', ['BookID' => 999, 'AuthorID' => 999])->assertUnprocessable();
        foreach (['authors/999', 'book-types/999', 'books/999', 'book-authors/999/999'] as $path) {
            $this->getJson("/api/$path")->assertNotFound();
            $this->patchJson("/api/$path", [])->assertNotFound();
            $this->deleteJson("/api/$path")->assertNotFound();
        }
    }
}
