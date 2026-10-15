<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookType extends Model
{
    protected $table = 'tblBookType';

    protected $primaryKey = 'BookTypeID';

    public $timestamps = false;

    protected $fillable = ['BookTypeName'];

    public function books()
    {
        return $this->hasMany(Book::class, 'BookTypeID');
    }
}
