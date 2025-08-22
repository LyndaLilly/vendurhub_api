<?php

// database/migrations/xxxx_xx_xx_create_profile_links_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileLinksTable extends Migration
{
    public function up()
    {
        Schema::create('profile_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->uuid('shareable_link')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('profile_links');
    }
}
