<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'tblAuthor';

    protected $primaryKey = 'AuthorID';

    public $timestamps = false;

    protected $fillable = ['AuthorName', 'Gender', 'DOB', 'POB', 'Address', 'Phone', 'Email', 'Photo'];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'tblBookAuthor', 'AuthorID', 'BookID')->withPivot('AuthorDate', 'Remark');
    }
}
