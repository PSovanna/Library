<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblBook', function (Blueprint $table) {
            $table->string('Photo', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tblBook', function (Blueprint $table) {
            $table->dropColumn('Photo');
        });
    }
};
