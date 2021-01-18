<?php

declare(strict_types=1);

namespace Keboola\Filter;

interface FilterInterface
{
    public function compareObject(\stdClass $object): bool;
}
