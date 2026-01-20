<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')
                  ->nullable()
                  ->constrained('posts')
                  ->onDelete('cascade');

            $table->foreignId('comment_id')
                  ->nullable()
                  ->constrained('comments')
                  ->onDelete('cascade');

            $table->string('user_ip');
            $table->timestamps();

            // prevent duplicate likes
            $table->unique(['post_id', 'comment_id', 'user_ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
