<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StrapiServices;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends Controller
{
    protected $strapi;

    public function __construct(StrapiServices $strapi)
    {
        $this->strapi = $strapi;
    }

    // List all content (paginated)
    public function index(Request $request)
    {
        try {
            $contents = $this->strapi->getContents();
            return response()->json($contents);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // Get single content by ID
    public function show(Request $request, $id)
    {
        try {
            $content = $this->strapi->getContentById($id);

            if (!$content) {
                return response()->json(['message' => 'Content not found'], 404);
            }

            // Premium check
            if ($content['attributes']['is_premium'] ?? false) {
                $user = auth()->user();
                if (!$user || !$user->subscription || $user->subscription->plan !== 'premium') {
                    return response()->json(['message' => 'Premium subscription required'], 403);
                }
            }

            return response()->json($content);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
