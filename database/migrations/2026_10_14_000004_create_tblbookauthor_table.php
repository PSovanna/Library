<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblBookAuthor', function (Blueprint $table) {
            $table->unsignedBigInteger('BookID');
            $table->foreign('BookID')->references('BookID')->on('tblBook')->restrictOnDelete();
            $table->unsignedBigInteger('AuthorID');
            $table->foreign('AuthorID')->references('AuthorID')->on('tblAuthor')->restrictOnDelete();
            $table->date('AuthorDate')->nullable();
            $table->text('Remark')->nullable();
            $table->primary(['BookID', 'AuthorID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblBookAuthor');
    }
};
