<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('profiles', function (Blueprint $table) {
        $table->string('profile_image')->nullable()->after('user_id');
        $table->string('whatsapp_link')->nullable()->after('contact_number_whatsapp');
    });
}

public function down()
{
    Schema::table('profiles', function (Blueprint $table) {
        $table->dropColumn(['profile_image', 'whatsapp_link']);
    });
}



};
