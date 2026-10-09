<?php

namespace App\Services\Accounts;

use App\Models\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReaderAvatar
{
    public static function safe(?string $path, int $readerId): bool
    {
        return $path !== null && preg_match('~^'. $readerId .'/[a-f0-9-]{36}\.jpg$~D', $path) === 1;
    }

    public function read(Reader $reader): ?string
    {
        if (! self::safe($reader->avatar_path, $reader->id)) return null;
        $disk = Storage::disk('reader_avatars');
        return $disk->exists($reader->avatar_path) ? base64_encode($disk->get($reader->avatar_path)) : null;
    }

    public function replace(Reader $reader, ?string $encoded): void
    {
        $disk = Storage::disk('reader_avatars');
        $new = null;
        if ($encoded !== null) {
            $bytes = base64_decode($encoded, true);
            $info = $bytes === false ? false : @getimagesizefromstring($bytes);
            if (! $info || strlen($bytes) > 2 * 1024 * 1024 || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
                || $info[0] > 4096 || $info[1] > 4096 || $info[0] * $info[1] > 16000000) {
                throw ValidationException::withMessages(['photo' => 'Choose a JPEG, PNG or WebP photo up to 2 MB and 4096 pixels.']);
            }
            $image = @imagecreatefromstring($bytes);
            if (! $image) throw ValidationException::withMessages(['photo' => 'This photo cannot be read.']);
            // Bound storage/rendering cost, flatten alpha, strip original EXIF and metadata.
            $ratio = min(1, 512 / max($info[0], $info[1]));
            $width = max(1, (int) round($info[0] * $ratio));
            $height = max(1, (int) round($info[1] * $ratio));
            $clean = imagecreatetruecolor($width, $height);
            imagefill($clean, 0, 0, imagecolorallocate($clean, 255, 255, 255));
            imagecopyresampled($clean, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
            ob_start();
            try { imagejpeg($clean, null, 88); $jpeg = ob_get_contents(); }
            finally { ob_end_clean(); imagedestroy($clean); imagedestroy($image); }
            $new = $reader->id.'/'.Str::uuid().'.jpg';
            $disk->put($new, $jpeg);
        }
        try {
            $old = DB::transaction(function () use ($reader, $new) {
                $locked = Reader::whereKey($reader->id)->lockForUpdate()->firstOrFail();
                $old = $locked->avatar_path;
                $locked->forceFill(['avatar_path' => $new])->save();
                return $old;
            });
        } catch (\Throwable $error) {
            if ($new !== null) $disk->delete($new);
            throw $error;
        }
        if (self::safe($old, $reader->id)) $disk->delete($old);
    }

    public function erase(Reader $reader): void
    {
        Storage::disk('reader_avatars')->deleteDirectory((string) $reader->id);
    }
}
