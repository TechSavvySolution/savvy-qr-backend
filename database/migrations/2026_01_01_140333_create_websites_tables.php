<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. The Main Website Table
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            // 🟢 All the columns we need
            $table->unsignedBigInteger('template_id')->nullable(); 
            $table->string('title')->default('My Website');
            $table->string('slug')->nullable(); 
            $table->boolean('active')->default(true); 
            
            $table->timestamps();
        });

        // 2. The Sections Table
        Schema::create('websites_sections', function (Blueprint $table) {
            $table->id();
            
            // 🟢 Link to the 'websites' table
            $table->foreignId('website_id')->constrained('websites')->onDelete('cascade');
            
            // 🟢 Link to the Master Section ID
            $table->unsignedBigInteger('section_id')->nullable(); 

            // 🟢 JSON Columns for Data & Style
            $table->json('values')->nullable(); 
            $table->json('style')->nullable();  
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites_sections');
        Schema::dropIfExists('websites');
    }
};