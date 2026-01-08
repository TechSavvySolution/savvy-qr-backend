<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // 1. Validate (Must be an image, max 2MB)
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->file('image')) {
            // 2. Save file to 'storage/app/public/uploads'
            $path = $request->file('image')->store('uploads', 'public');

            // 3. Return the Public URL
            // Example: http://127.0.0.1:8000/storage/uploads/xyz.jpg
            $url = asset('storage/' . $path);

            return response()->json([
                'status' => true,
                'message' => 'Image uploaded successfully!',
                'url' => $url
            ]);
        }

        return response()->json([
            'status' => false, 
            'message' => 'Upload failed'
            ], 400);
    }
}