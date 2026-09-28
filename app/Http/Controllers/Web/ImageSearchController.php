<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageSearchController extends Controller
{
    /**
     * Accept an uploaded image, send it to Claude Vision API,
     * extract product-relevant keywords, and redirect to the catalogue.
     */
    public function search(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'], // 10 MB max
        ]);

        $apiKey = config('services.anthropic.key');

        if (! $apiKey) {
            return redirect()
                ->route('public.catalogue')
                ->with('image_search_error', 'Image search is not configured. Please contact the administrator.');
        }

        try {
            // Encode image as base64
            $file     = $request->file('image');
            $mimeType = $file->getMimeType();
            $base64   = base64_encode(file_get_contents($file->getRealPath()));

            // Call Anthropic Claude vision
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5',
                'max_tokens' => 256,
                'messages'   => [
                    [
                        'role'    => 'user',
                        'content' => [
                            [
                                'type'  => 'image',
                                'source' => [
                                    'type'       => 'base64',
                                    'media_type' => $mimeType,
                                    'data'       => $base64,
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => 'This image is being used to search a B2B wholesale product catalogue (electronics, clothing, home goods, industrial parts, etc.). Identify the product category and key product attributes visible in the image. Respond with ONLY a short comma-separated list of 3–6 English search keywords — product name, category, and key attributes (e.g. "wireless headphones, bluetooth, over-ear"). No sentences, no punctuation other than commas.',
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text', '');
                // Sanitise: keep only alphanumeric, spaces, commas, hyphens
                $keywords = preg_replace('/[^a-zA-Z0-9\s,\-]/', '', $content);
                $keywords = trim($keywords, ', ');

                if ($keywords) {
                    return redirect()->route('public.catalogue', ['search' => $keywords]);
                }
            }

            Log::warning('Image search API call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Image search exception: ' . $e->getMessage());
        }

        // Fallback: go to catalogue with no search term
        return redirect()
            ->route('public.catalogue')
            ->with('image_search_error', 'Could not analyse the image. Please try again or use text search.');
    }
}
