<?php

namespace Cencori;

use Cencori\Types\{MetricsResponse};

/**
 * Module for fetching Cencori usage metrics.
 */
class MetricsModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Get metrics for a specific period.
     *
     * @param string $period Time period (e.g., "24h", "7d", "30d")
     * @return MetricsResponse
     */
    public function get(string $period): MetricsResponse
    {
        $path = '/v1/metrics?period=' . rawurlencode($period);
        $data = $this->client->request($path, 'GET');
        return MetricsResponse::fromArray($data);
    }
}
