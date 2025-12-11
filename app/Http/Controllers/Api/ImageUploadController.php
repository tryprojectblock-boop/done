<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Core\Contracts\FileUploadInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ImageUploadController extends Controller
{
    public function __construct(
        private readonly FileUploadInterface $fileUploadService
    ) {}

    /**
     * Serve an image by generating a temporary URL.
     * This allows authenticated users to access private images.
     */
    public function serve(Request $request, string $path): RedirectResponse|JsonResponse
    {
        // Decode the path (it may be base64 encoded or URL encoded)
        $decodedPath = base64_decode($path);

        if (!$decodedPath || !$this->fileUploadService->exists($decodedPath)) {
            abort(404, 'Image not found');
        }

        // Generate a temporary URL valid for 60 minutes
        $temporaryUrl = $this->fileUploadService->getTemporaryUrl($decodedPath, 60);

        return redirect($temporaryUrl);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120', // 5MB max
        ]);

        $user = $request->user();

        $result = $this->fileUploadService->upload(
            $request->file('image'),
            'editor-images/' . date('Y/m'),
            [
                'user_id' => $user->id,
                'context' => 'editor_image',
                'tenant_id' => $user->company_id,
                'visibility' => 'public', // Make editor images public so they're accessible to all workspace members
            ]
        );

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'url' => $result->url,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to upload image',
        ], 500);
    }

    /**
     * Download an image by proxying through the server.
     * This bypasses CORS restrictions for cross-origin images.
     */
    public function download(Request $request): Response|JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        // Security: Only allow downloads from our DigitalOcean Spaces domain
        $allowedDomains = [
            'projectblock.atl1.digitaloceanspaces.com',
            'projectblock.nyc3.digitaloceanspaces.com',
        ];

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';

        if (!in_array($host, $allowedDomains)) {
            return response()->json(['error' => 'Invalid image domain'], 403);
        }

        try {
            // Fetch the image from the remote server
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch image'], 500);
            }

            // Get content type and determine filename
            $contentType = $response->header('Content-Type') ?? 'image/png';
            $pathParts = explode('/', $parsedUrl['path'] ?? '');
            $filename = end($pathParts);
            // Remove query string from filename
            $filename = explode('?', $filename)[0];
            if (empty($filename)) {
                $filename = 'image.' . $this->getExtensionFromMimeType($contentType);
            }

            // Return the image with download headers
            return response($response->body())
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($response->body()));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Download failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get file extension from MIME type.
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];

        return $map[$mimeType] ?? 'png';
    }
}
