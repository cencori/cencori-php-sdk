<?php

/**
 * Documents API examples.
 *
 * Run with:
 *   CENCORI_API_KEY=csk_... php examples/documents.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cencori\Cencori;

$cencori = new Cencori();

$pdfUrl = 'https://www.orimi.com/pdf-test.pdf';

// 1. Extract — native PDF text (free, no LLM)
echo "== extract ==\n";
$extracted = $cencori->documents->extract(['document_url' => $pdfUrl]);
echo "method: {$extracted['method']}\n";
echo "pages:  {$extracted['pageCount']}\n";
echo "chars:  " . strlen($extracted['text']) . "\n";
if (!empty($extracted['cost'])) {
    printf("cost:   \$%.6f\n", $extracted['cost']['cencoriChargeUsd']);
} else {
    echo "cost:   \$0 (native extraction, no LLM call)\n";
}

// 2. Summarize
echo "\n== summarize ==\n";
$summary = $cencori->documents->summarize(['document_url' => $pdfUrl]);
echo $summary['summary'] . "\n";

// 3. Query
echo "\n== query ==\n";
$answer = $cencori->documents->query([
    'document_url' => $pdfUrl,
    'question' => 'What is the main topic of this document?',
]);
echo $answer['answer'] . "\n";

// 4. From a local file
if (file_exists('sample.pdf')) {
    $b64 = base64_encode(file_get_contents('sample.pdf'));
    $local = $cencori->documents->extract([
        'document_base64' => $b64,
        'mime_type' => 'application/pdf',
    ]);
    echo "\n== local file ==\nextracted " . strlen($local['text']) . " chars via {$local['method']}\n";
}
