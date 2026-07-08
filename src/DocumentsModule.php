<?php

namespace Cencori;

/**
 * Documents module — extract text from PDFs and images, summarize, query.
 *
 * All methods accept either `document_url` or `document_base64` (+ `mime_type`).
 *
 * @example
 * // Extract text (native PDF parse, no LLM cost)
 * $result = $cencori->documents->extract([
 *     'document_url' => 'https://example.com/contract.pdf'
 * ]);
 * echo $result['text'];
 *
 * @example
 * // Summarize
 * $summary = $cencori->documents->summarize([
 *     'document_url' => 'https://example.com/report.pdf'
 * ]);
 * echo $summary['summary'];
 *
 * @example
 * // Query
 * $answer = $cencori->documents->query([
 *     'document_url' => 'https://example.com/report.pdf',
 *     'question' => 'What is the total revenue for Q3?',
 * ]);
 * echo $answer['answer'];
 */
class DocumentsModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Extract text from a PDF or image.
     *
     * PDFs use native text extraction (free — no LLM). Images route through Vision OCR.
     */
    public function extract(array $params): array
    {
        return $this->client->request('/api/ai/documents/extract', 'POST', $this->buildBody($params));
    }

    /**
     * Extract then summarize the document.
     */
    public function summarize(array $params): array
    {
        return $this->client->request('/api/ai/documents/summarize', 'POST', $this->buildBody($params));
    }

    /**
     * Extract then answer a question about the document. Requires a `question` field.
     */
    public function query(array $params): array
    {
        if (empty($params['question'])) {
            throw new \InvalidArgumentException('documents.query requires a `question`');
        }
        return $this->client->request('/api/ai/documents/query', 'POST', $this->buildBody($params));
    }

    private function buildBody(array $params): array
    {
        $documentUrl = $params['document_url'] ?? null;
        $documentBase64 = $params['document_base64'] ?? null;

        if (!$documentUrl && !$documentBase64) {
            throw new \InvalidArgumentException(
                'documents request requires document_url or document_base64'
            );
        }

        $body = [];
        if ($documentUrl) {
            $body['document_url'] = $documentUrl;
        } else {
            $body['document_base64'] = $documentBase64;
            if (!empty($params['mime_type'])) {
                $body['mime_type'] = $params['mime_type'];
            }
            if (!empty($params['filename'])) {
                $body['filename'] = $params['filename'];
            }
        }

        foreach (['prompt', 'model', 'question'] as $key) {
            if (array_key_exists($key, $params) && $params[$key] !== null) {
                $body[$key] = $params[$key];
            }
        }
        if (isset($params['max_tokens'])) {
            $body['max_tokens'] = (int) $params['max_tokens'];
        }
        if (isset($params['temperature'])) {
            $body['temperature'] = (float) $params['temperature'];
        }

        return $body;
    }
}
