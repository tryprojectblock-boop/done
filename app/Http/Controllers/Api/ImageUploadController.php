<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Core\Contracts\FileUploadInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
