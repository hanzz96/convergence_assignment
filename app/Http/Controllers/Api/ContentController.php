<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Exceptions\Api\ErrorException;
use App\Services\StrapiServices;
use App\Models\Subscription;
use Carbon\Carbon;
use Exception;
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

            $userId = $request->user()->id;
            // Premium check
            if ($content['is_premium'] ?? false) {
                $user = auth()->user();
                
                if (!$user) {
                    return response()->json(['message' => 'Premium subscription required'], 403);
                }

                $subscription = Subscription::where('user_id', $userId)
                    ->where('plan', 'premium')
                    ->where('expires_at', '>', Carbon::now())
                    ->first();

                if (!$subscription) {
                    return response()->json(['message' => 'Premium subscription required'], 403);
                }
            }

            return response()->json($content);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
