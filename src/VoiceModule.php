<?php

namespace Cencori;

use Cencori\Errors\AuthenticationError;
use Cencori\Errors\CencoriError;
use Cencori\Errors\InsufficientCreditsError;
use Cencori\Errors\ProviderError;
use Cencori\Errors\RateLimitError;
use Cencori\Errors\SafetyError;
use Psr\Http\Message\ResponseInterface;

/**
 * Voice module — text-to-speech and speech-to-text across providers.
 *
 * The provider is inferred from the model (TTS default `tts-1`; STT default
 * `whisper-1`).
 *
 * @example
 * // Text-to-speech
 * $result = $cencori->voice->speak([
 *     'input' => 'Hello from Cencori.',
 *     'model' => 'aura-asteria-en', // Deepgram; provider inferred from model
 * ]);
 * file_put_contents('hello.mp3', $result['audio']);
 *
 * @example
 * // Speech-to-text
 * $t = $cencori->voice->transcribe('hello.mp3', ['model' => 'nova-3']);
 * echo $t['text'];
 *
 * @example
 * // With speaker labels
 * $d = $cencori->voice->diarize('meeting.mp3', ['model' => 'assemblyai-universal']);
 * foreach ($d['segments'] as $seg) {
 *     echo $seg['speaker'] . ': ' . $seg['text'] . "\n";
 * }
 */
class VoiceModule
{
    private Cencori $client;

    /** @var array<int,string> */
    private const TEXT_FORMATS = ['text', 'srt', 'vtt'];

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Synthesize speech. Returns ['audio' => binary string, 'content_type', 'provider'].
     * Provider is inferred from `model` (default `tts-1`).
     *
     * @param array{input:string,model?:string,voice?:string,provider?:string,response_format?:string,speed?:float,language?:string} $params
     * @return array{audio:string,content_type:string,provider:string}
     */
    public function speak(array $params): array
    {
        if (empty($params['input']) || trim($params['input']) === '') {
            throw new CencoriError(message: 'voice.speak requires non-empty input');
        }

        $body = array_filter([
            'input' => $params['input'],
            'model' => $params['model'] ?? null,
            'voice' => $params['voice'] ?? null,
            'provider' => $params['provider'] ?? null,
            'response_format' => $params['response_format'] ?? null,
            'speed' => $params['speed'] ?? null,
            'language' => $params['language'] ?? null,
        ], fn ($v) => $v !== null);

        $response = $this->client->getHttpClient()->request(
            'POST',
            $this->client->getBaseUrl() . '/api/ai/audio/speech',
            [
                'headers' => array_merge(
                    ['Content-Type' => 'application/json', 'CENCORI_API_KEY' => $this->client->getApiKey()],
                    $this->client->getHeaders(),
                ),
                'json' => $body,
                'http_errors' => false,
            ],
        );

        $this->throwIfError($response);

        return [
            'audio' => (string) $response->getBody(),
            'content_type' => $response->getHeaderLine('Content-Type'),
            'provider' => $response->getHeaderLine('X-Provider'),
        ];
    }

    /**
     * Transcribe audio. `$audio` may be a file path or raw binary string.
     * Provider is inferred from `model` (default `whisper-1`).
     *
     * @param string $audio File path or raw audio bytes.
     * @param array{model?:string,provider?:string,language?:string,prompt?:string,temperature?:float,diarize?:bool,response_format?:string,filename?:string} $options
     * @return array<string,mixed>
     */
    public function transcribe(string $audio, array $options = []): array
    {
        $format = $options['response_format'] ?? 'json';
        $filename = $options['filename'] ?? 'audio.mp3';

        if ($audio !== '' && @is_file($audio)) {
            $filename = $options['filename'] ?? basename($audio);
            $contents = file_get_contents($audio);
        } else {
            $contents = $audio;
        }

        $multipart = [
            ['name' => 'file', 'contents' => $contents, 'filename' => $filename],
            ['name' => 'response_format', 'contents' => $format],
        ];
        foreach (['model', 'provider', 'language', 'prompt'] as $key) {
            if (isset($options[$key])) {
                $multipart[] = ['name' => $key, 'contents' => (string) $options[$key]];
            }
        }
        if (isset($options['temperature'])) {
            $multipart[] = ['name' => 'temperature', 'contents' => (string) $options['temperature']];
        }
        if (!empty($options['diarize'])) {
            $multipart[] = ['name' => 'diarize', 'contents' => 'true'];
        }

        $response = $this->client->getHttpClient()->request(
            'POST',
            $this->client->getBaseUrl() . '/api/ai/audio/transcriptions',
            [
                'headers' => array_merge(
                    ['CENCORI_API_KEY' => $this->client->getApiKey()],
                    $this->client->getHeaders(),
                ),
                'multipart' => $multipart,
                'http_errors' => false,
            ],
        );

        $this->throwIfError($response);

        if (in_array($format, self::TEXT_FORMATS, true)) {
            return ['text' => (string) $response->getBody(), 'provider' => $response->getHeaderLine('X-Provider')];
        }
        return (array) json_decode((string) $response->getBody(), true);
    }

    /**
     * Transcribe with speaker labels. Use a diarization-capable model
     * (`nova-3`, `assemblyai-universal`).
     *
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    public function diarize(string $audio, array $options = []): array
    {
        $options['diarize'] = true;
        $options['response_format'] = 'verbose_json';
        return $this->transcribe($audio, $options);
    }

    /**
     * List available voice models.
     *
     * @return array{tts:array<int,mixed>,stt:array<int,mixed>}
     */
    public function listModels(): array
    {
        return [
            'tts' => $this->getModels('/api/ai/audio/speech'),
            'stt' => $this->getModels('/api/ai/audio/transcriptions'),
        ];
    }

    /**
     * @return array<int,mixed>
     */
    private function getModels(string $path): array
    {
        $response = $this->client->getHttpClient()->request(
            'GET',
            $this->client->getBaseUrl() . $path,
            [
                'headers' => array_merge(['CENCORI_API_KEY' => $this->client->getApiKey()], $this->client->getHeaders()),
                'http_errors' => false,
            ],
        );
        if ($response->getStatusCode() >= 300) {
            return [];
        }
        $data = json_decode((string) $response->getBody(), true);
        return is_array($data) && isset($data['models']) ? $data['models'] : [];
    }

    private function throwIfError(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            return;
        }
        if ($status === 401) {
            throw new AuthenticationError();
        }
        if ($status === 429) {
            throw new RateLimitError();
        }
        if ($status === 402) {
            throw new InsufficientCreditsError();
        }
        if ($status === 502) {
            throw new ProviderError();
        }
        $data = json_decode((string) $response->getBody(), true);
        if ($status === 400 && is_array($data) && isset($data['reasons'])) {
            throw new SafetyError(message: $data['error'] ?? 'Content safety violation', reasons: $data['reasons']);
        }
        $message = is_array($data) ? ($data['message'] ?? $data['error'] ?? 'Request failed') : 'Request failed';
        throw new CencoriError(message: $message, statusCode: $status);
    }
}
