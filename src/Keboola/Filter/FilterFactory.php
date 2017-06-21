<?php

namespace Keboola\Filter;

class FilterFactory
{
    /**
     * @param string $filterString
     * @return FilterInterface
     */
    public static function create(string $filterString)
    {
        $logicalExpressions = preg_split("#(&|\\|)#", $filterString, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (count($logicalExpressions) > 1) {
            $filter = new CompoundFilter($logicalExpressions);
        } else {
            $filter = new Filter($logicalExpressions[0]);
        }
        return $filter;
    }
}
