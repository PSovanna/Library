<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookAuthor extends Pivot
{
    protected $table = 'tblBookAuthor';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['BookID', 'AuthorID', 'AuthorDate', 'Remark'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setPivotKeys('BookID', 'AuthorID');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'BookID');
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'AuthorID');
    }
}
