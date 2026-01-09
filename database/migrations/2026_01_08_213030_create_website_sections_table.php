<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('website_sections', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }


    public function up()
{
    Schema::create('website_sections', function (Blueprint $table) {
        $table->id();
        
        // Connect to the Website
        $table->foreignId('website_id')->constrained('websites')->onDelete('cascade');
        
        // Connect to the Master Section (Optional, in case it's a custom section)
        $table->unsignedBigInteger('section_id')->nullable(); 
        
        // Store Data & Styles
        $table->json('values')->nullable(); // Stores { "title": "Hello", "image": "url..." }
        $table->json('style')->nullable();  // Stores { "color": "red" }
        
        $table->integer('order_index')->default(0); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_sections');
    }
};
