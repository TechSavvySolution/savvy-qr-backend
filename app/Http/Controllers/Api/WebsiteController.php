<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\WebsiteSection;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    // 1️ Create a New Website (When "Use Template" is clicked)
    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string', // e.g., 'portfolio', 'business'
            'title' => 'nullable|string',
        ]);

        $user = $request->auth_user; // Getting user from APIMiddleware

        // A. Create the Main Website Entry
        // We use the username as the 'slug' for now (savvyqr.com/sarik)
        $website = Website::create([
            'user_id' => $user->id,
            'template_name' => $request->template_name,
            'title' => $request->title ?? $user->name . "'s Website",
            'slug' => $user->username, // Default slug is username
            'is_published' => false,
        ]);

        // B. Add Default Sections based on Template
        $this->addDefaultSections($website, $request->template_name);

        return response()->json([
            'status' => true,
            'message' => 'Website created successfully!',
            'data' => $website->load('sections') // Return site with sections
        ], 201);
    }

    // 2️ Get My Website (For the Editor)
    public function show(Request $request)
    {
        $user = $request->auth_user;
        
        $website = Website::where('user_id', $user->id)
                          ->with('sections') // Load the JSON sections
                          ->first();

        if (!$website) {
            return response()->json([
                'status' => false,
                'message' => 'No website found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Website fetched',
            'data' => $website
        ]);
    }

    // 3️ Update Website (Save Changes)
    // public function update(Request $request)
    // {
    //     $request->validate([
    //         'sections' => 'required|array',
    //     ]);

    //     $user = $request->auth_user;
    //     $website = Website::where('user_id', $user->id)->firstOrFail();

    //     foreach ($request->sections as $index => $sectionData) {
    //         $section = WebsiteSection::where('id', $sectionData['id'])
    //                                  ->where('website_id', $website->id)
    //                                  ->first();

    //         if ($section) {
    //             $section->update([
    //                 'content' => $sectionData['content'],
    //                 'styles' => $sectionData['styles'],
    //                 // Save the order!
    //                 'order_index' => $index + 1 
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Website saved successfully!'
    //     ]);
    // }

    // 3️ Update Website (Smart Sync: Add, Edit, Delete)
    public function update(Request $request)
    {
        $request->validate([
            'sections' => 'required|array',
        ]);

        $user = $request->auth_user;
        $website = Website::where('user_id', $user->id)->firstOrFail();

        // 1. Get all incoming Section IDs (Filter out new ones that don't have numeric IDs yet)
        $incomingIds = collect($request->sections)
            ->pluck('id')
            ->filter(fn($id) => is_numeric($id))
            ->toArray();

        // 2. DELETE sections that are in the DB but NOT in the request (User deleted them)
        WebsiteSection::where('website_id', $website->id)
                      ->whereNotIn('id', $incomingIds)
                      ->delete();

        // 3. Loop through the list to Create or Update
        foreach ($request->sections as $index => $sec) {
            
            // Prepare the data
            $data = [
                'website_id' => $website->id,
                'section_type' => $sec['section_type'],
                'order_index' => $index + 1, // Save the new order
                'content' => $sec['content'],
                'styles' => $sec['styles'] ?? [], // Default to empty array if missing
            ];

            if (isset($sec['id']) && is_numeric($sec['id'])) {
                // UPDATE existing section
                WebsiteSection::where('id', $sec['id'])
                              ->where('website_id', $website->id)
                              ->update($data);
            } else {
                // CREATE new section (It has no ID yet)
                WebsiteSection::create($data);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Website saved successfully!',
            // Return the fresh data so Frontend can update IDs
            'data' => $website->load('sections') 
        ]);
    }
    //  Helper: Pre-fill content (CORRECTED: No json_encode)
    private function addDefaultSections($website, $template)
    {
        $sections = [];

        //  If PORTFOLIO
        if ($template === 'portfolio') {
            $sections = [
                [
                    'section_type' => 'hero',
                    'order_index' => 1,
                    //  THIS IS WHERE I ADDED THE IMAGE KEY
                    'content' => [
                        'title' => 'Hello, I am a Developer',
                        'subtitle' => 'Welcome to my digital space.',
                        'button_text' => 'Contact Me',
                        'hero_image' => 'https://via.placeholder.com/400x200' //  NEW LINE ADDED HERE
                    ],
                    'styles' => ['bg_color' => '#ffffff', 'text_color' => '#000000']
                ],
                [
                    'section_type' => 'about',
                    'order_index' => 2,
                    'content' => [
                        'title' => 'About Me',
                        'description' => 'I love coding and building apps.'
                    ],
                    'styles' => ['bg_color' => '#f3f4f6', 'text_color' => '#333333']
                ]
            ];
        } 
        
        //  If BUSINESS
        elseif ($template === 'business') {
            $sections = [
                [
                    'section_type' => 'hero',
                    'order_index' => 1,
                    'content' => [
                        'title' => 'We Serve Quality',
                        'subtitle' => 'Best services in the city.',
                        'button_text' => 'Order Now',
                        'hero_image' => 'https://via.placeholder.com/400x200' //  Added here too just in case
                    ],
                    'styles' => ['bg_color' => '#1e3a8a', 'text_color' => '#ffffff']
                ]
            ];
        }

        // Save all sections
        foreach ($sections as $sec) {
            WebsiteSection::create([
                'website_id' => $website->id,
                'section_type' => $sec['section_type'],
                'order_index' => $sec['order_index'],
                'content' => $sec['content'], // Pass Array directly
                'styles' => $sec['styles']    // Pass Array directly
            ]);
        }
    }

    //  PUBLIC: View a Website by Username (No Login Required)
    public function view($username)
    {
        $website = Website::where('slug', $username)
                          ->with('sections')
                          ->first();

        if (!$website) {
            return response()->json([
                'status' => false,
                'message' => 'Website not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Website found',
            'data' => $website
        ]);
    }
}