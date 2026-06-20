<?php

namespace Cencori;

class VectorsSubmodule
{
    public function search(string $query, array $kwargs = []): never
    {
        throw new \RuntimeException('Storage module coming soon');
    }

    public function upsert(mixed $vectors, array $kwargs = []): never
    {
        throw new \RuntimeException('Storage module coming soon');
    }
}
