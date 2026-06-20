<?php

namespace Cencori;

class WorkflowModule
{
    public function trigger(string $workflowId, array $kwargs = []): never
    {
        throw new \RuntimeException('Workflow module coming soon');
    }
}
