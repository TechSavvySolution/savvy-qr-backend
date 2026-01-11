<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\WebsiteSection;
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
{
    /* =============================================
       1️⃣ SAVE USER WEBSITE (POST /api/website/create)
       Used by: User Editor
    ============================================= */
    public function store(Request $request)
    {
        $user_id = auth()->id(); 

        if (!$user_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:master_templates,id',
            'title'       => 'required|string', 
            'sections'    => 'required|array'   
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        // Create or Update the Website for this user
        $website = Website::updateOrCreate(
            ['user_id' => $user_id],
            [
                'template_id' => $request->template_id,
                'title'       => $request->title,
                'active'      => true
            ]
        );

        // Save the Section Data
        WebsiteSection::where('website_id', $website->id)->delete();

        foreach ($request->sections as $secData) {
            WebsiteSection::create([
                'website_id' => $website->id,
                'section_id' => $secData['section_id'], 
                'values'     => json_encode($secData['values']), // e.g. {"title": "Sharik"}
                'style'      => json_encode($secData['style'] ?? []) 
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Published!', 'data' => $website]);
    }

    /* =============================================
       2️⃣ GET MY DASHBOARD SITE (GET /api/website/my-site)
       Used by: User Dashboard
    ============================================= */
    public function mySite(Request $request)
    {
        $user_id = auth()->id();
        
        $website = Website::where('user_id', $user_id)
                          ->with(['template']) // Just basic info for the card
                          ->first();

        if (!$website) {
            return response()->json(['status' => false, 'data' => null]);
        }

        return response()->json(['status' => true, 'data' => $website]);
    }

    /* =============================================
       3️⃣ PUBLIC VIEW API (GET /api/websites/{id})
       Used by: The Public Link (savvyqr.com/website/1)
    ============================================= */
    public function index($user_id)
    {
        // 🟢 CRITICAL: We load 'sections.section' 
        // This gets the User Data AND the Master Rules (Schema) in one shot.
        
        $website = Website::where('user_id', $user_id)
                          ->with(['template', 'sections.section']) 
                          ->first();

        if (!$website) {
            return response()->json(['status' => false, 'message' => 'Website not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $website
        ]);
    }
}