<?php

namespace Cencori;

use Cencori\Errors\CencoriError;
use Cencori\Types\{CreateProjectParams, Project};

/** Project management reserved for a future management-auth API. */
class ProjectsModule
{
    public function __construct(Cencori $client)
    {
        unset($client);
    }

    private function unsupported(): never
    {
        throw new CencoriError(
            'Project management is not available with a project API key. ' .
            'Use the Cencori dashboard until a separately scoped management credential is supported.',
            null,
            'management_auth_required'
        );
    }

    /** @return Project[] */
    public function list(string $orgSlug): array
    {
        unset($orgSlug);
        $this->unsupported();
    }

    public function create(string $orgSlug, CreateProjectParams $params): Project
    {
        unset($orgSlug, $params);
        $this->unsupported();
    }

    public function get(string $orgSlug, string $projectSlug): Project
    {
        unset($orgSlug, $projectSlug);
        $this->unsupported();
    }

    public function update(string $orgSlug, string $projectSlug, CreateProjectParams $params): void
    {
        unset($orgSlug, $projectSlug, $params);
        $this->unsupported();
    }

    public function delete(string $orgSlug, string $projectSlug): void
    {
        unset($orgSlug, $projectSlug);
        $this->unsupported();
    }
}
