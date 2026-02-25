<?php

namespace App\Services;

/**
 * ImageAnalysisService
 *
 * Interface contract for image validation.
 * Currently ships a "mock" implementation that always approves images.
 *
 * ─── Integrating Google Cloud Vision ────────────────────────────────────────
 * 1. Install the SDK:
 *    composer require google/cloud-vision
 *
 * 2. Replace the body of `analyzeImage()` in GoogleVisionAnalysisService
 *    (concrete class below) with the real Vision API call.
 *
 * 3. Bind the concrete class in app/Config/Services.php:
 *    public static function imageAnalysis(bool $getShared = true): ImageAnalysisService
 *    {
 *        return static::getSharedInstance('imageAnalysis', $getShared)
 *               ?? new \App\Services\GoogleVisionAnalysisService();
 *    }
 * ────────────────────────────────────────────────────────────────────────────
 */
interface ImageAnalysisService
{
    /**
     * Analyse an uploaded image and return a result object.
     *
     * @param  string $absoluteImagePath  Server-side path to the image file
     * @return ImageAnalysisResult
     */
    public function analyzeImage(string $absoluteImagePath): ImageAnalysisResult;
}

// ─── Value object returned by analyzeImage() ────────────────────────────────

class ImageAnalysisResult
{
    public function __construct(
        public readonly bool   $isValid,
        public readonly string $reason  = '',
        public readonly float  $confidence = 1.0,
        public readonly array  $labels  = [],
    ) {}
}

// ─── Mock implementation (default) ──────────────────────────────────────────

class MockImageAnalysisService implements ImageAnalysisService
{
    /**
     * Always returns valid.
     * Swap this class for GoogleVisionAnalysisService to go live.
     */
    public function analyzeImage(string $absoluteImagePath): ImageAnalysisResult
    {
        // TODO: Replace this with a real Google Vision SafeSearch + label detection call.
        // See GoogleVisionAnalysisService stub below for the plug-in point.
        return new ImageAnalysisResult(
            isValid    : true,
            reason     : 'mock_always_valid',
            confidence : 1.0,
            labels     : ['trash', 'waste'], // stub labels
        );
    }
}

// ─── Google Vision stub (plug-in point) ─────────────────────────────────────

class GoogleVisionAnalysisService implements ImageAnalysisService
{
    /** @var string Google Cloud project credential JSON path */
    private string $credentialsPath;

    public function __construct(?string $credentialsPath = null)
    {
        $this->credentialsPath = $credentialsPath ?? WRITEPATH . 'gcp_credentials.json';
    }

    public function analyzeImage(string $absoluteImagePath): ImageAnalysisResult
    {
        // ── PLUG-IN POINT ─────────────────────────────────────────────────
        // Uncomment and complete when ready to use Google Cloud Vision:
        //
        // use Google\Cloud\Vision\V1\ImageAnnotatorClient;
        //
        // $client = new ImageAnnotatorClient([
        //     'credentials' => $this->credentialsPath,
        // ]);
        //
        // $image   = file_get_contents($absoluteImagePath);
        // $result  = $client->labelDetection($image);
        // $labels  = $result->getLabelAnnotations();
        // $client->close();
        //
        // $wasteKeywords = ['trash', 'waste', 'garbage', 'litter', 'rubbish', 'debris'];
        // foreach ($labels as $label) {
        //     if (in_array(strtolower($label->getDescription()), $wasteKeywords)) {
        //         return new ImageAnalysisResult(true, 'waste_detected', $label->getScore());
        //     }
        // }
        //
        // return new ImageAnalysisResult(false, 'no_waste_detected', 0.0);
        // ─────────────────────────────────────────────────────────────────

        // Fallback until implementation is complete
        return new ImageAnalysisResult(true, 'stub_not_yet_implemented', 1.0);
    }
}
