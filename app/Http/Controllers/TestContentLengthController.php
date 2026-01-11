<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestContentLengthController extends Controller
{
    public function testSmall()
    {
        $data = ['message' => 'Small response', 'size' => 'small'];
        $json = json_encode($data);
        $contentLength = strlen($json);

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Length', (string)$contentLength)
            ->header('X-Debug', 'Small-Response');
    }

    public function testLarge()
    {
        // Simulate a large response like getQuestionsByType
        $data = [
            'status' => 'success',
            'questions' => array_fill(0, 1000, [
                'id' => 1,
                'question' => 'This is a test question with some content',
                'choices' => [
                    ['id' => 1, 'text' => 'Choice A'],
                    ['id' => 2, 'text' => 'Choice B'],
                    ['id' => 3, 'text' => 'Choice C'],
                    ['id' => 4, 'text' => 'Choice D'],
                ],
            ])
        ];

        $json = json_encode($data);
        $contentLength = strlen($json);

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Length', (string)$contentLength)
            ->header('X-Debug', 'Large-Response')
            ->header('X-Size-Bytes', (string)$contentLength);
    }

    public function testWithBuffering()
    {
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }

        $data = [
            'status' => 'success',
            'questions' => array_fill(0, 500, [
                'id' => 1,
                'question' => 'Test with buffering disabled',
            ])
        ];

        $json = json_encode($data);
        $contentLength = strlen($json);

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Length', (string)$contentLength)
            ->header('X-Debug', 'Buffering-Disabled');
    }

    public function testWithGzip()
    {
        // Disable output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Create large test data
        $data = [
            'status' => 'success',
            'questions' => array_fill(0, 1000, [
                'id' => 1,
                'question_text' => 'This is a test question that will compress well because it has repetitive content',
                'choices' => [
                    ['id' => 1, 'text' => 'Choice A with some text'],
                    ['id' => 2, 'text' => 'Choice B with some text'],
                    ['id' => 3, 'text' => 'Choice C with some text'],
                    ['id' => 4, 'text' => 'Choice D with some text'],
                ],
            ])
        ];

        $json = json_encode($data);
        $uncompressedLength = strlen($json);

        // Check if client accepts gzip
        $acceptEncoding = request()->header('Accept-Encoding', '');
        $useGzip = stripos($acceptEncoding, 'gzip') !== false;

        if ($useGzip) {
            // Compress the JSON
            $compressed = gzencode($json, 6);
            $compressedLength = strlen($compressed);
            $compressionRatio = round(($compressedLength / $uncompressedLength) * 100, 2);

            return response($compressed)
                ->header('Content-Type', 'application/json')
                ->header('Content-Encoding', 'gzip')
                ->header('Content-Length', (string)$compressedLength)
                ->header('X-Uncompressed-Size', (string)$uncompressedLength)
                ->header('X-Compression-Ratio', $compressionRatio . '%')
                ->header('X-Debug', 'Gzip-Enabled');
        } else {
            return response($json)
                ->header('Content-Type', 'application/json')
                ->header('Content-Length', (string)$uncompressedLength)
                ->header('X-Debug', 'Gzip-Not-Supported');
        }
    }
}
