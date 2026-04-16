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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('synopsis')->nullable();
            $table->string('director')->nullable();
            $table->string('duration')->nullable(); // e.g. "166 mins"
            $table->date('release_date')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('poster')->nullable();   // image path
            $table->string('banner')->nullable();   // image path
            $table->string('trailer_url')->nullable();
            $table->json('genres')->nullable();
            $table->enum('status', ['now_showing', 'coming_soon', 'ended'])->default('coming_soon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
