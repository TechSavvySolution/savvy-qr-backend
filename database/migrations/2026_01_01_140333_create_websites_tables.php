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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to User
            $table->string('template_name')->nullable(); // e.g., 'portfolio', 'business'
            $table->string('title')->default('My Website'); // Site Title
            $table->string('slug')->unique(); // For URL: savvyqr.com/sarik
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // 2. The Sections Table (Where the magic happens)
        Schema::create('website_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade'); // Link to Website
            
            $table->string('section_type'); // e.g., 'hero', 'about', 'contact'
            $table->integer('order_index')->default(0); // To sort sections (1, 2, 3...)
            
            // 🟢 JSON COLUMNS: flexible storage for App & Web
            $table->json('content')->nullable(); // Stores { "title": "Hi", "desc": "..." }
            $table->json('styles')->nullable();  // Stores { "bg_color": "#fff", "text_color": "#000" }
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_sections');
        Schema::dropIfExists('websites');
    }
};