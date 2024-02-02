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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('post_id');
            $table->integer('status')->default(0);
            $table->json('body')->nullable();
            $table->json('media')->nullable();
            $table->text('output')->nullable();
            $table->json('meta')->nullable();
            $table->tinyInteger('published')->nullable()->default(0);
            $table->foreignId('site_id')
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
