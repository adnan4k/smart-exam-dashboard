<?php

namespace App\Http\Traits;

use Illuminate\Http\Response;

/**
 * Emits JSON responses with an explicit Content-Length header.
 *
 * The mobile app relies on Content-Length to drive its download progress bar
 * (Dio's onReceiveProgress reports 0 as the total when a response is sent with
 * chunked transfer encoding). Laravel's default response()->json() does not set
 * it, so every endpoint the app consumes must go through this trait.
 *
 * Extracted from the identical private helpers that used to live in
 * QuestionController and NoteController.
 */
trait RespondsWithJson
{
    /**
     * @param  mixed  $data
     */
    protected function jsonResponse($data, int $status = 200): Response
    {
        // Any buffered output would be prepended to the body and corrupt the
        // byte count we are about to advertise.
        while (ob_get_level()) {
            ob_end_clean();
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $uncompressedLength = strlen($json);

        $acceptEncoding = request()->header('Accept-Encoding', '');
        $useGzip = stripos($acceptEncoding, 'gzip') !== false;

        if ($useGzip) {
            $compressed = gzencode($json, 6); // Balance of speed and ratio.

            $response = response($compressed, $status)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Encoding', 'gzip')
                ->header('Content-Length', (string) strlen($compressed))
                ->header('X-Uncompressed-Size', (string) $uncompressedLength);
        } else {
            $response = response($json, $status)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Length', (string) $uncompressedLength);
        }

        $response->headers->remove('Transfer-Encoding');

        return $response;
    }
}
