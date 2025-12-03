<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FileUpload extends Component
{
    public function __construct(
        public string $name,
        public string $id = '',
        public string $accept = '',
        public bool $multiple = false,
        public int $maxSize = 10485760, // 10MB default
        public int $maxFiles = 10,
        public bool $preview = true,
        public string $uploadUrl = '',
        public string $deleteUrl = '',
        public array $existingFiles = [],
        public string $label = '',
        public string $hint = '',
        public bool $required = false,
        public bool $disabled = false,
        public string $context = '', // e.g., 'workspace', 'profile', 'document'
    ) {
        $this->id = $this->id ?: $this->name;
    }

    public function render(): View|Closure|string
    {
        return view('components.file-upload');
    }

    public function acceptedTypes(): string
    {
        if ($this->accept) {
            return $this->accept;
        }

        return implode(',', [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            '.doc',
            '.docx',
            '.xls',
            '.xlsx',
            '.ppt',
            '.pptx',
            '.zip',
        ]);
    }

    public function maxSizeFormatted(): string
    {
        return $this->formatBytes($this->maxSize);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}
