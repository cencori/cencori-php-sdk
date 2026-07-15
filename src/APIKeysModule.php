<?php

namespace Cencori;

use Cencori\Errors\CencoriError;
use Cencori\Types\{APIKey, CreateAPIKeyParams, KeyUsageStats};

/** API-key management reserved for a future management-auth API. */
class APIKeysModule
{
    public function __construct(Cencori $client)
    {
        unset($client);
    }

    private function unsupported(): never
    {
        throw new CencoriError(
            'API-key management is not available with a project API key. ' .
            'Use the Cencori dashboard until a separately scoped management credential is supported.',
            null,
            'management_auth_required'
        );
    }

    /** @return APIKey[] */
    public function list(string $projectId, string $environment): array
    {
        unset($projectId, $environment);
        $this->unsupported();
    }

    public function create(string $projectId, CreateAPIKeyParams $params): APIKey
    {
        unset($projectId, $params);
        $this->unsupported();
    }

    public function revoke(string $projectId, string $keyId): void
    {
        unset($projectId, $keyId);
        $this->unsupported();
    }

    public function getStats(string $projectId, string $keyId): KeyUsageStats
    {
        unset($projectId, $keyId);
        $this->unsupported();
    }
}
