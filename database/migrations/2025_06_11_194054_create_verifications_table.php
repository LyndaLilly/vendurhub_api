<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('verifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type'); // Add this
    $table->string('code');
    $table->timestamp('expires_at')->nullable();  // Add this
    $table->timestamp('verified_at')->nullable(); // Add this
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
