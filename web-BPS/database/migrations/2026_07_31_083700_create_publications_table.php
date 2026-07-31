<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('bps_pub_id')->nullable(); // ID dari BPS API jika disinkronkan
            $table->string('cover')->nullable();
            $table->text('abstract')->nullable();
            $table->string('issn')->nullable();
            $table->string('pdf_url')->nullable();
            $table->date('release_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
