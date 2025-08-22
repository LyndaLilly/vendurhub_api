<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('business_name');
        $table->string('business_logo')->nullable();
        $table->date('date_of_establishment');
        $table->string('contact_number_whatsapp');
        $table->string('business_account_number');
        $table->string('busines_account_name');
        $table->string('business_bank_name');
        $table->string('signature')->nullable();
        $table->timestamp('last_editable_update')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
