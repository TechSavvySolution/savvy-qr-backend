<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\WebsiteSection;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    // 1️⃣ Create a New Website (When "Use Template" is clicked)
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

    // 2️⃣ Get My Website (For the Editor)
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

    // 🛠️ Helper: Pre-fill content so the user sees something immediately
    private function addDefaultSections($website, $template)
    {
        $sections = [];

        // 🟢 If PORTFOLIO, add Hero + About + Skills
        if ($template === 'portfolio') {
            $sections = [
                [
                    'section_type' => 'hero',
                    'order_index' => 1,
                    'content' => json_encode([
                        'title' => 'Hello, I am a Developer',
                        'subtitle' => 'Welcome to my digital space.',
                        'button_text' => 'Contact Me'
                    ]),
                    'styles' => json_encode(['bg_color' => '#ffffff', 'text_color' => '#000000'])
                ],
                [
                    'section_type' => 'about',
                    'order_index' => 2,
                    'content' => json_encode([
                        'title' => 'About Me',
                        'description' => 'I love coding and building apps.'
                    ]),
                    'styles' => json_encode(['bg_color' => '#f3f4f6', 'text_color' => '#333333'])
                ]
            ];
        } 
        
        // 🟠 If BUSINESS, add Hero + Services
        elseif ($template === 'business') {
            $sections = [
                [
                    'section_type' => 'hero',
                    'order_index' => 1,
                    'content' => json_encode([
                        'title' => 'We Serve Quality',
                        'subtitle' => 'Best services in the city.',
                        'button_text' => 'Order Now'
                    ]),
                    'styles' => json_encode(['bg_color' => '#1e3a8a', 'text_color' => '#ffffff'])
                ]
            ];
        }

        // Save all sections to database
        foreach ($sections as $sec) {
            WebsiteSection::create([
                'website_id' => $website->id,
                'section_type' => $sec['section_type'],
                'order_index' => $sec['order_index'],
                'content' => $sec['content'], // Already JSON encoded
                'styles' => $sec['styles']    // Already JSON encoded
            ]);
        }
    }
}