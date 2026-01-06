<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. The "Folder" for the Template (e.g., "Gym", "Cafe")
        Schema::create('master_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Modern Portfolio"
            $table->string('thumbnail')->nullable(); // For the Admin Dashboard grid
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. The "Blueprints" for each Section (Nav, Hero, etc.)
        Schema::create('master_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_template_id')->constrained('master_templates')->onDelete('cascade');
            
            $table->string('name'); // e.g., "Hero Section"
            $table->string('type'); // e.g., "nav", "hero", "form", "generic"
            
            // It stores the "Fields" you create in the Admin Pop-up
            // Example: [{"label": "Title", "type": "text"}, {"label": "Banner", "type": "image"}]
            $table->json('fields_schema'); 
            
            // Default styling (Colors/Fonts)
            $table->json('default_styles')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_sections');
        Schema::dropIfExists('master_templates');
    }
};