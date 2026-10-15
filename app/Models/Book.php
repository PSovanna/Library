<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'tblBook';

    protected $primaryKey = 'BookID';

    public $timestamps = false;

    protected $fillable = ['BookTitle', 'BookTypeID', 'PublishDate', 'NumOfPages', 'NumOfCopies', 'Edition', 'Publisher', 'BookSource', 'Remark'];

    public function bookType()
    {
        return $this->belongsTo(BookType::class, 'BookTypeID');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'tblBookAuthor', 'BookID', 'AuthorID')->withPivot('AuthorDate', 'Remark');
    }
}
