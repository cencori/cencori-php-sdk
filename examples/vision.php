<?php

/**
 * Vision API examples.
 *
 * Run with:
 *   CENCORI_API_KEY=csk_... php examples/vision.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cencori\Cencori;

$cencori = new Cencori();

$imageUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Cat03.jpg/1200px-Cat03.jpg';

// 1. Analyze from a URL with a custom prompt
$analysis = $cencori->vision->analyze([
    'image_url' => $imageUrl,
    'prompt' => 'Describe this animal and estimate its age.',
]);
echo "== analyze ==\n";
echo $analysis['analysis'] . "\n";
printf("cost: \$%.6f\n", $analysis['cost']['cencoriChargeUsd']);

// 2. Describe (preset prompt)
$described = $cencori->vision->describe(['image_url' => $imageUrl]);
echo "\n== describe ==\n";
echo $described['description'] . "\n";

// 3. OCR from a local file (if present)
if (file_exists('sample_receipt.png')) {
    $b64 = base64_encode(file_get_contents('sample_receipt.png'));
    $ocr = $cencori->vision->ocr([
        'image_base64' => $b64,
        'mime_type' => 'image/png',
    ]);
    echo "\n== ocr ==\n";
    echo $ocr['text'] . "\n";
}

// 4. Classify — structured JSON output
$classified = $cencori->vision->classify(['image_url' => $imageUrl]);
echo "\n== classify ==\n";
print_r($classified['classification']);
