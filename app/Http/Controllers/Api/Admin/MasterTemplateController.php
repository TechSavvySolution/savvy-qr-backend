<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterTemplate;
use App\Models\MasterSection;

class MasterTemplateController extends Controller
{

    //1️⃣ CREATE TEMPLATE (Admin)
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
        $thumbnailUrl = asset('public/storage/' . $path);
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

    //2️⃣ GET ALL TEMPLATES (Admin Dashboard)
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

    //3️⃣ UPDATE TEMPLATE DETAILS (Name/Image)
   public function updateDetails(Request $request, $id)
{
    $template = MasterTemplate::findOrFail($id);
    
    // 1. Validation: Make thumbnail 'nullable' so we don't force re-upload
    $request->validate([
        'name' => 'required|string|max:255',
        'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
    ]);

    // 2. Handle File Upload (Only if user uploaded a NEW one)
    if ($request->hasFile('thumbnail')) {
        $path = $request->file('thumbnail')->store('templates', 'public');
        $template->thumbnail = asset('storage/' . $path);//public
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

    // 4️⃣ ADD SECTION RULE (Dynamic Schema)
    public function storeSection(Request $request)
    {
        $request->validate([
            'master_template_id' => 'required|exists:master_templates,id',
            'name' => 'required|string', // e.g., "Hero Section"
            'type' => 'required|string', // e.g., "hero", "nav"
            'fields_schema' => 'nullable|array', // THIS IS THE DYNAMIC PART
            'default_styles' => 'nullable|array'
        ]);

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


    /* ===============================
       5️⃣ Find Template by ID (PUBLIC/PROTECTED)
       Used by: Mobile App & Frontend Builder
    =============================== */
    public function getTemplateById(Request $request, $id)
    {
        // We use 'with("sections")' because the app needs the sections to build the layout
        $template = MasterTemplate::with('sections')->find($id);

        if (!$template) {
            return response()->json([
                'status'  => false,
                'message' => 'Template not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Template fetched successfully',
            'data'    => $template
        ]);
    }

       //6️⃣ Delete Template

    public function destroy($id)
    {
        $template = MasterTemplate::find($id);

        if (!$template) {
            return response()->json([
                'status' => false, 
                'message' => 'Template not found'
            ], 404);
        }
        
        $template->delete();

        return response()->json([
            'status' => true,
            'message' => 'Template deleted successfully',
            'data' => $template
        ]);
    }
}