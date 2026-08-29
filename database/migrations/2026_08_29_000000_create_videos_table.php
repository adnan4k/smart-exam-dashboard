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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('chapter_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            // 'upload' => file stored on disk, 'url' => external link (YouTube/Vimeo/CDN)
            $table->enum('source', ['upload', 'url'])->default('url');
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('thumbnail_path')->nullable();

            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->unsignedInteger('duration')->nullable();     // seconds

            $table->unsignedTinyInteger('grade')->nullable();
            $table->string('language')->default('english');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['subject_id', 'chapter_id']);
            $table->index('type_id');
            $table->index('is_active');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
