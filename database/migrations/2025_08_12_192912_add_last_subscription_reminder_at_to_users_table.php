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
    Schema::table('users', function ($table) {
        $table->timestamp('last_subscription_reminder_at')->nullable()->after('subscription_expires_at');
    });
}

public function down()
{
    Schema::table('users', function ($table) {
        $table->dropColumn('last_subscription_reminder_at');
    });
}

};
