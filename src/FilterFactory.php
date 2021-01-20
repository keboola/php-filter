<?php

declare(strict_types=1);

namespace Keboola\Filter;

class FilterFactory
{
    public static function create(string $filterString): FilterInterface
    {
        /** @var array $logicalExpressions */
        $logicalExpressions = preg_split(
            '#(&|\\|)#',
            $filterString,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        return count($logicalExpressions) > 1 ?
            new CompoundFilter($logicalExpressions) : new Filter($logicalExpressions[0]);
    }
}
