<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
class MediaService
{
    const STORAGE_PATH = 'realisations';
    const IMAGE_MAX_WIDTH = 1200;
    const THUMBNAIL_WIDTH = 400;
    const IMAGE_QUALITY = 80;
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const VIDEO_EXTENSIONS = ['mp4', 'mov', 'avi', 'webm'];
    public function processUploadedFile(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, self::IMAGE_EXTENSIONS)) return $this->processImage($file);
        if (in_array($ext, self::VIDEO_EXTENSIONS)) return $this->processVideo($file);
        throw new \InvalidArgumentException("Type de fichier non supporté: {$ext}");
    }
    protected function processImage(UploadedFile $file): array
    {
        $filename = 'img_' . Str::uuid() . '.jpg';
        $thumbFilename = 'thumb_' . $filename;
        $dir = self::STORAGE_PATH . '/' . date('Y/m');
        Storage::disk('public')->makeDirectory($dir);
        $image = Image::read($file->getRealPath());
        if ($image->width() > self::IMAGE_MAX_WIDTH) {
            $image->scaleDown(width: self::IMAGE_MAX_WIDTH);
        }
        $mainPath = $dir . '/' . $filename;
        $image->toJpeg(self::IMAGE_QUALITY)->save(Storage::disk('public')->path($mainPath));
        $thumb = Image::read($file->getRealPath());
        $thumb->scaleDown(width: self::THUMBNAIL_WIDTH);
        $thumbPath = $dir . '/' . $thumbFilename;
        $thumb->toJpeg(self::IMAGE_QUALITY)->save(Storage::disk('public')->path($thumbPath));
        return [
            'type' => 'image',
            'path' => $mainPath,
            'thumbnail' => $thumbPath,
            'original_name' => $file->getClientOriginalName(),
            'size' => Storage::disk('public')->size($mainPath),
        ];
    }
    protected function processVideo(UploadedFile $file): array
    {
        $filename = 'vid_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $dir = self::STORAGE_PATH . '/' . date('Y/m');
        Storage::disk('public')->makeDirectory($dir);
        $path = $file->storeAs($dir, $filename, 'public');
        return [
            'type' => 'video',
            'path' => $path,
            'thumbnail' => null,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }
    public function deleteMedia(array $item): void
    {
        if (!empty($item['path'])) Storage::disk('public')->delete($item['path']);
        if (!empty($item['thumbnail'])) Storage::disk('public')->delete($item['thumbnail']);
    }
    public function deleteAllMedia(array $list): void
    {
        foreach ($list as $m) $this->deleteMedia($m);
    }
    public static function getPublicUrl(?string $path): ?string
    {
        if (!$path) return null;
        return Storage::disk('public')->url($path);
    }
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
