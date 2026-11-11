<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the columns that turn a plain Laravel user
     * into a parent or a child of our wallet app.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // "parent" or "child" - decides which dashboard a user sees
            $table->string('role', 10)->after('email')->index();

            $table->string('phone', 20)->nullable()->after('role');

            // Needed for children (the app is for under-16s)
            $table->date('date_of_birth')->nullable()->after('phone');

            // Parents get a short code; a child types it when registering
            // to link their account to that parent.
            $table->string('link_code', 8)->nullable()->unique()->after('date_of_birth');
        });
    }

    /**
     * Undo the changes above (used by "php artisan migrate:rollback").
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'date_of_birth', 'link_code']);
        });
    }
};