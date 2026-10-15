<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblBook', function (Blueprint $table) {
            $table->id('BookID');
            $table->string('BookTitle', 255);
            $table->unsignedBigInteger('BookTypeID');
            $table->foreign('BookTypeID')->references('BookTypeID')->on('tblBookType')->restrictOnDelete();
            $table->date('PublishDate')->nullable();
            $table->unsignedInteger('NumOfPages')->nullable();
            $table->unsignedInteger('NumOfCopies')->nullable();
            $table->string('Edition', 100)->nullable();
            $table->string('Publisher', 255)->nullable();
            $table->string('BookSource', 255)->nullable();
            $table->text('Remark')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblBook');
    }
};
