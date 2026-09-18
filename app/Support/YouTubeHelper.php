<?php

namespace App\Support;

class YouTubeHelper
{
    /**
     * Extract the 11-character YouTube video ID from any format:
     * - Standard: https://www.youtube.com/watch?v=dQw4w9WgXcQ
     * - Short link: https://youtu.be/dQw4w9WgXcQ
     * - Embed: https://www.youtube.com/embed/dQw4w9WgXcQ
     * - Shorts: https://www.youtube.com/shorts/dQw4w9WgXcQ
     * - Iframe code: <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>
     */
    public static function extractVideoId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // If an iframe tag was pasted, extract src attribute first
        if (str_contains($url, '<iframe') && preg_match('/src=["\']([^"\']+)["\']/i', $url, $srcMatch)) {
            $url = $srcMatch[1];
        }

        // Match any YouTube URL variation
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/)|youtu\.be/)([^"&?/\s]{11})%i';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Convert any YouTube link (watch, youtu.be, embed, shorts, iframe) to a standard watch URL:
     * https://www.youtube.com/watch?v=VIDEO_ID
     */
    public static function toWatchUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $id = self::extractVideoId($url);

        return $id ? "https://www.youtube.com/watch?v={$id}" : self::cleanUrl($url);
    }

    /**
     * Convert any YouTube link to a standard embed URL:
     * https://www.youtube.com/embed/VIDEO_ID
     */
    public static function toEmbedUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $id = self::extractVideoId($url);

        return $id ? "https://www.youtube.com/embed/{$id}" : self::cleanUrl($url);
    }

    /**
     * Get the default high-quality thumbnail for YouTube video.
     */
    public static function getThumbnailUrl(?string $url): ?string
    {
        $id = self::extractVideoId($url);

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }

    /**
     * Clean raw pasted string (e.g. if user pastes an iframe snippet or surrounded with quotes).
     */
    public static function cleanUrl(?string $input): ?string
    {
        if (empty($input)) {
            return null;
        }

        $input = trim($input);

        if (str_contains($input, '<iframe') && preg_match('/src=["\']([^"\']+)["\']/i', $input, $match)) {
            return trim($match[1]);
        }

        return $input;
    }
}
