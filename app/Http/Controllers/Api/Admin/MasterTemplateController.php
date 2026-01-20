<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterTemplate;
use App\Models\MasterSection;
use Illuminate\Support\Facades\Storage; // Added for file deletion safety

class MasterTemplateController extends Controller
{

    //1️⃣ CREATE TEMPLATE (Admin)
    public function storeTemplate(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' 
        ]);

        $thumbnailUrl = null;

        // 🟢 FIX: Generate Clean URL
        if ($request->hasFile('thumbnail')) {
            // Stores in storage/app/public/templates
            $path = $request->file('thumbnail')->store('templates', 'public');
            
            // Generates http://127.0.0.1:8000/storage/templates/filename.jpg
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
        
        // 1. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        // 2. Handle File Upload
        if ($request->hasFile('thumbnail')) {
            // Optional: Delete old image if exists to save space
            // if ($template->thumbnail) { ... logic to delete old file ... }

            $path = $request->file('thumbnail')->store('templates', 'public');
            
            // 🟢 FIX: Ensure URL is clean (No /public/storage)
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

    // 4️⃣ ADD SECTION RULE (Dynamic Schema)
    public function storeSection(Request $request)
    {
        $request->validate([
            'master_template_id' => 'required|exists:master_templates,id',
            'name' => 'required|string', 
            'type' => 'required|string', 
            'fields_schema' => 'nullable|array', 
            'default_styles' => 'nullable|array'
        ]);

        $section = MasterSection::create([
            'master_template_id' => $request->master_template_id,
            'name' => $request->name,
            'type' => $request->type,
            'fields_schema' => $request->fields_schema,
            'default_styles' => $request->default_styles
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Section rule saved successfully',
            'data' => $section
        ]);
    }

    // 5️⃣ Find Template by ID (PUBLIC/PROTECTED)
    public function getTemplateById(Request $request, $id)
    {
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

    // 6️⃣ Delete Template
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