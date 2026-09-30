<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageSearchController extends Controller
{
    /**
     * Accept an uploaded image, send it to the configured AI provider,
     * extract product-relevant keywords, and redirect to the catalogue.
     */
    public function search(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'], // 10 MB max
        ]);

        $ai = new AiService();

        if (! $ai->hasKey()) {
            return redirect()
                ->route('public.catalogue')
                ->with('image_search_error', 'Image search is not configured. Please contact the administrator.');
        }

        try {
            $file     = $request->file('image');
            $mimeType = $file->getMimeType();
            $base64   = base64_encode(file_get_contents($file->getRealPath()));

            $prompt = 'This image is from a B2B wholesale product catalogue search. '
                . 'Your job is to extract 2–4 short search keywords a buyer would type to find this product. '
                . 'Focus on: the product type/name, material, or main visual feature. '
                . 'Output ONLY a comma-separated list of simple English words or short phrases '
                . '(e.g. "tiger, stuffed toy" or "blue denim jacket" or "ceramic mug"). '
                . 'No sentences, no explanations, no punctuation other than commas. '
                . 'Keep each term under 3 words. Prefer the product name over descriptions.';

            $content = $ai->chat($prompt, $base64, $mimeType);

            // Sanitise: keep only alphanumeric, spaces, commas, hyphens
            $keywords = preg_replace('/[^a-zA-Z0-9\s,\-]/', '', $content);
            $keywords = trim($keywords, ', ');

            if ($keywords) {
                return redirect()->route('public.catalogue', ['search' => $keywords]);
            }

        } catch (\Throwable $e) {
            Log::error('Image search exception: ' . $e->getMessage());
        }

        // Fallback: go to catalogue with no search term
        return redirect()
            ->route('public.catalogue')
            ->with('image_search_error', 'Could not analyse the image. Please try again or use text search.');
    }
}
