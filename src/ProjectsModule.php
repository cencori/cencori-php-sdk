<?php

namespace Cencori;

use Cencori\Types\{CreateProjectParams, Project};

/**
 * Module for managing Cencori projects.
 */
class ProjectsModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * List all projects for an organization.
     *
     * @param string $orgSlug The organization slug identifier
     * @return Project[]
     */
    public function list(string $orgSlug): array
    {
        $path = "/api/organizations/{$orgSlug}/projects";
        $data = $this->client->request($path, 'GET');

        $projects = [];
        foreach ($data['projects'] ?? [] as $p) {
            $projects[] = Project::fromArray($p);
        }
        return $projects;
    }

    /**
     * Create a new project.
     *
     * @param string $orgSlug The organization slug
     * @param CreateProjectParams $params Project creation parameters
     * @return Project
     */
    public function create(string $orgSlug, CreateProjectParams $params): Project
    {
        $path = "/api/organizations/{$orgSlug}/projects";
        $data = $this->client->request($path, 'POST', $params->toArray());
        return Project::fromArray($data);
    }

    /**
     * Get a project by slug.
     *
     * @param string $orgSlug The organization slug
     * @param string $projectSlug The project slug
     * @return Project
     */
    public function get(string $orgSlug, string $projectSlug): Project
    {
        $path = "/api/organizations/{$orgSlug}/projects/{$projectSlug}";
        $data = $this->client->request($path, 'GET');
        return Project::fromArray($data);
    }

    /**
     * Update a project.
     *
     * @param string $orgSlug The organization slug
     * @param string $projectSlug The project slug
     * @param CreateProjectParams $params Parameters to update
     */
    public function update(string $orgSlug, string $projectSlug, CreateProjectParams $params): void
    {
        $path = "/api/organizations/{$orgSlug}/projects/{$projectSlug}";
        $this->client->request($path, 'PATCH', $params->toArray());
    }

    /**
     * Delete a project.
     *
     * @param string $orgSlug The organization slug
     * @param string $projectSlug The project slug
     */
    public function delete(string $orgSlug, string $projectSlug): void
    {
        $path = "/api/organizations/{$orgSlug}/projects/{$projectSlug}";
        $this->client->request($path, 'DELETE');
    }
}
