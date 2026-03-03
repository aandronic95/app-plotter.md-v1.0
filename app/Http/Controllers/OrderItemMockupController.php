<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderItemMockupController extends Controller
{
    /**
     * Download mockup file for an order item (admin only).
     * File on disk may have a different name (e.g. with timestamp); we send the display name (mockup_filename) to the browser.
     */
    public function __invoke(OrderItem $orderItem): Response|StreamedResponse
    {
        $path = $orderItem->mockup_path;
        if (empty($path)) {
            abort(404, 'Fișierul maketei nu există.');
        }

        // Normalize path (e.g. backslashes on Windows)
        $path = str_replace('\\', '/', trim($path));

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Fișierul maketei nu există.');
        }

        // Use display name for download (e.g. "document.pdf"), not the stored name (e.g. "20260303_111923_document.pdf")
        $downloadFilename = $orderItem->mockup_filename ?: basename($path);

        return Storage::disk('public')->download(
            $path,
            $downloadFilename,
            [
                'Content-Type' => Storage::disk('public')->mimeType($path),
            ]
        );
    }
}
