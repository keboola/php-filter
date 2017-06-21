<?php

namespace Keboola\Filter;

interface FilterInterface
{
    /**
     * @param \stdClass $object
     * @return bool
     */
    public function compareObject(\stdClass $object);
}
