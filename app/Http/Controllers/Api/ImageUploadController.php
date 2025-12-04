<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Core\Contracts\FileUploadInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function __construct(
        private readonly FileUploadInterface $fileUploadService
    ) {}

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
