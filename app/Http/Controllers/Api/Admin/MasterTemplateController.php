<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterTemplate;
use App\Models\MasterSection;

class MasterTemplateController extends Controller
{

public function storeTemplate(Request $request)
{
    // 1. Allow nullable thumbnail for "Draft" creation
    $request->validate([
        'name' => 'required|string|max:255',
        'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' 
    ]);

    $thumbnailUrl = null;

    if ($request->hasFile('thumbnail')) {
        $path = $request->file('thumbnail')->store('templates', 'public');
        $thumbnailUrl = asset('storage/' . $path);
    }

    $template = MasterTemplate::create([
        'name' => $request->name,
        'thumbnail' => $thumbnailUrl, 
        'is_active' => true
    ]);

   return response()->json([
        'status' => true,
        'message' => 'Template created successfully.',
        'data' => $template
    ]);
}


    public function getTemplates()
{
    // Fetch templates with their sections
    $templates = MasterTemplate::with('sections')
                    ->orderBy('created_at', 'desc')
                    ->get();

    return response()->json([
        'status' => true,
        'message' => 'Templates fetched successfully.', 
        'data' => $templates
    ]);
}


    // Save a Section Rule (The data from your "Add Section" Pop-up)
    public function storeSection(Request $request)
    {
        $request->validate([
            'master_template_id' => 'required|exists:master_templates,id',
            'name' => 'required|string', // e.g., "Hero Section"
            'type' => 'required|string', // e.g., "hero", "nav"
            'fields_schema' => 'required|array', // THIS IS THE DYNAMIC PART
            'default_styles' => 'nullable|array'
        ]);

        // Example of what 'fields_schema' looks like coming from your Admin UI:
        // [
        //    { "label": "Main Title", "type": "short_text", "key": "title" },
        //    { "label": "Background", "type": "photo", "key": "bg_image" }
        // ]

        $section = MasterSection::create([
            'master_template_id' => $request->master_template_id,
            'name' => $request->name,
            'type' => $request->type,
            'fields_schema' => $request->fields_schema, // Saving the rules as JSON
            'default_styles' => $request->default_styles
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Section rule saved successfully',
            'data' => $section
        ]);
    }



   public function updateDetails(Request $request, $id)
{
    $template = MasterTemplate::findOrFail($id);
    
    // 1. Validation: Make thumbnail 'nullable' so we don't force re-upload
    $request->validate([
        'name' => 'required|string|max:255',
        'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
    ]);

    // 2. Handle File Upload (Only if user uploaded a NEW one)
    if ($request->hasFile('thumbnail')) {
        $path = $request->file('thumbnail')->store('templates', 'public');
        $template->thumbnail = asset('storage/' . $path);
    }

    // 3. Save Changes
    $template->name = $request->name;
    $template->save();

    return response()->json([
        'status' => true,
        'message' => 'Template details updated successfully.',
        'data' => $template
    ]);
}
}