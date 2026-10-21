<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->deleteJson($link)->assertOk()->assertJsonPath('success', true);
        $this->getJson($link)->assertNotFound();
        $this->deleteJson("/api/books/$book")->assertOk()->assertJsonPath('success', true);
        $this->deleteJson("/api/authors/$author")->assertOk()->assertJsonPath('success', true);
        $this->deleteJson("/api/book-types/$type")->assertOk()->assertJsonPath('success', true);
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

    public function test_picture_upload_replacement_and_invalid_input(): void
    {
        Storage::fake('public');
        $picture = fn () => UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $author = $this->post('/api/authors', ['AuthorName' => 'Dara', 'Photo' => $picture()])
            ->assertCreated()->assertJsonPath('success', true)->json();
        $disk = Storage::disk('public');
        $disk->assertExists($author['Photo']);
        $replacement = $this->post('/api/authors/'.$author['AuthorID'], ['_method' => 'PATCH', 'Photo' => $picture()])
            ->assertOk()->json('Photo');
        $disk->assertMissing($author['Photo']);
        $disk->assertExists($replacement);
        $this->patchJson('/api/authors/'.$author['AuthorID'], ['AuthorName' => 'Updated'])
            ->assertOk()->assertJsonPath('Photo', $replacement);
        $this->post('/api/authors', ['AuthorName' => 'Bad', 'Photo' => UploadedFile::fake()->create('bad.txt', 1)])
            ->assertUnprocessable()->assertJsonValidationErrors('Photo')->assertJsonPath('success', false);
        $this->postJson('/api/authors', ['AuthorName' => ' ', 'Email' => 'bad', 'DOB' => '2999-01-01', 'Phone' => 'abc'])
            ->assertUnprocessable()->assertJsonValidationErrors(['AuthorName', 'Email', 'DOB', 'Phone']);
        $this->deleteJson('/api/authors/'.$author['AuthorID'])->assertOk()->assertJsonPath('success', true);
        $disk->assertMissing($replacement);
        $type = $this->postJson('/api/book-types', ['BookTypeName' => 'Fiction'])->json('BookTypeID');
        $book = $this->post('/api/books', ['BookTitle' => 'Cover test', 'BookTypeID' => $type, 'Photo' => $picture()])
            ->assertCreated()->json();
        $disk->assertExists($book['Photo']);
        $this->patchJson('/api/books/'.$book['BookID'], ['NumOfCopies' => -1])->assertUnprocessable();
        $this->deleteJson('/api/books/'.$book['BookID'])->assertOk();
        $disk->assertMissing($book['Photo']);
        $this->get('/api/authors/999')->assertNotFound()->assertJsonPath('success', false);
    }
    public function test_picture_urls_use_the_current_application_address(): void
    {
        $author = \App\Models\Author::create(['AuthorName' => 'Photo URL', 'Photo' => 'authors/example.png']);
        $this->getJson('http://library.test:8080/api/authors/'.$author->AuthorID)
            ->assertOk()->assertJsonPath('PhotoUrl', 'http://library.test:8080/storage/authors/example.png');
    }
}
