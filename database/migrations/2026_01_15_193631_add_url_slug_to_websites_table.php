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
        Schema::table('websites', function (Blueprint $table) {
            // ✅ Add the missing 'url_slug' column
            // We make it unique (no duplicates) and nullable (just in case)
            $table->string('url_slug')->unique()->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // ✅ Remove the column if we ever undo this migration
            $table->dropColumn('url_slug');
        });
    }
};