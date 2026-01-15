<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\WebsiteSection;
//use App\Models\User;//new add
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
{
    /* =============================================
       1️⃣ SAVE USER WEBSITE (POST /api/website/create)
       Used by: User Editor
    ============================================= */
    // public function store(Request $request)
    // {
    //     $user_id = auth()->id(); 

    //     if (!$user_id) {
    //         return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'template_id' => 'required|exists:master_templates,id',
    //         'title'       => 'required|string', 
    //         'sections'    => 'required|array'   
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'message' => $validator->errors()], 400);
    //     }

    //     // Create or Update the Website for this user
    //     $website = Website::updateOrCreate(
    //         ['user_id' => $user_id],
    //         [
    //             'template_id' => $request->template_id,
    //             'title'       => $request->title,
    //             'active'      => true
    //         ]
    //     );

    //     // Save the Section Data
    //     WebsiteSection::where('website_id', $website->id)->delete();

    //     foreach ($request->sections as $secData) {
    //         WebsiteSection::create([
    //             'website_id' => $website->id,
    //             'section_id' => $secData['section_id'], 
    //             'values'     => json_encode($secData['values']), // e.g. {"title": "Sharik"}
    //             'style'      => json_encode($secData['style'] ?? []) 
    //         ]);
    //     }

    //     return response()->json(['status' => true, 'message' => 'Published!', 'data' => $website]);
    // }

   /*  public function store(Request $request, $user_id)
    {
        // 1. Validate the Incoming Data (The JSON from your diagram)
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|integer',
            'title'       => 'nullable|string',
            'sections'    => 'required|array', // This is the list of sections (1, 2...)
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        // 2. Find or Create the Website Record
        // We check if this user already has a website. If yes, update it. If no, create new.
        $website = Website::firstOrCreate(
            ['user_id' => $user_id], 
            ['template_id' => $request->template_id, 'title' => $request->title ?? 'My Website', 'active' => 1]
        );

        // Update title/template if it changed
        $website->template_id = $request->template_id;
        $website->title = $request->title ?? $website->title;
        $website->save();

        // 3. Loop Through Sections and Save Them
        if ($request->has('sections')) {
            foreach ($request->sections as $sectionData) {
                
                // Prepare the 'values' data (Everything that isn't ID or Style)
                // In your diagram, this is 'listitem', 'name', 'description', etc.
                $values = collect($sectionData)->except(['section_id', 'style', 'title'])->toArray();

                // Save to 'website_sections' table
                WebsiteSection::updateOrCreate(
                    [
                        'website_id' => $website->id,
                        'section_id' => $sectionData['section_id'] // e.g., 1 (Nav), 2 (Hero)
                    ],
                    [
                        'values' => json_encode($values),      // Stores {listitem: ..., name: ...}
                        'style'  => json_encode($sectionData['style'] ?? []) // Stores {color: #fff...}
                    ]
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Website saved successfully!',
            'data' => [
                'website_id' => $website->id,
                'link' => url('/website/' . $website->url_slug) // Optional: Generate a public link
            ]
        ]);
    } */


        public function saveWebsiteData(Request $request, $user_id)
    {
        // 1. Validate the Big JSON Object
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|integer',
            'title'       => 'nullable|string',
            'sections'    => 'required|array', // The list of sections (Hero, Nav, etc.)
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        // 2. Find or Create the "Website" wrapper
        // We check if this user_id already has a website.
        $website = Website::firstOrCreate(
            ['user_id' => $user_id], 
            [
                'template_id' => $request->template_id, 
                'title' => $request->title ?? 'My Website',
                'active' => 1,
                // Create a random slug if new
                'url_slug' => 'site-' . $user_id . '-' . time() 
            ]
        );

        // Update basic info if it changed
        $website->template_id = $request->template_id;
        if($request->has('title')) $website->title = $request->title;
        $website->save();

        // 3. PROCESS THE SECTIONS (The Loop)
        // This is where we save the "Hero", "About", "Nav" data
        
        // Optional: clear old sections if you want a fresh save every time
        // WebsiteSection::where('website_id', $website->id)->delete(); 

        foreach ($request->sections as $sectionData) {
            
            // Extract "Values" (Content) vs "Style" (CSS)
            // 'values' will hold things like: title, listitem, name, description, photo
            // 'style' will hold: tc, bg, etc.
            
            $values = collect($sectionData)->except(['section_id', 'style'])->toArray();
            $style  = $sectionData['style'] ?? [];
            $sectionId = $sectionData['section_id'];

            // Save to DB
            WebsiteSection::updateOrCreate(
                [
                    'website_id' => $website->id,
                    'section_id' => $sectionId 
                ],
                [
                    'values' => $values, // Model $casts will turn this to JSON automatically
                    'style'  => $style
                ]
            );
        }

        return response()->json([
            'status' => true, 
            'message' => 'Website saved successfully!',
            'data' => [
                'website_id' => $website->id,
                'sections_count' => count($request->sections)
            ]
        ]);
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