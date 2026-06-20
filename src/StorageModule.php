<?php

namespace Cencori;

class StorageModule
{
    public VectorsSubmodule $vectors;

    public function __construct()
    {
        $this->vectors = new VectorsSubmodule();
    }
}
