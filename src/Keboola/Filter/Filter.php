<?php

namespace Keboola\Filter;

use Keboola\Filter\Exception\FilterException;

/**
 * Filter objects using simple configuration strings
 */
class Filter
{
    /**
     * Column to compare
     * @var string
     */
    protected $columnName;
    /**
     * Operator to use for comparison
     * @var string
     */
    protected $operator;
    /**
     * Value to compare data against
     * @var string
     */
    protected $value;

    /**
     * @var Filter[]
     */
    protected $multiFilter = [];

    /**
     * | or &
     * @var string
     */
    protected $multiOperator;

    /**
     * Allowed multi-character comparison operators
     * @todo use array_keys from $methodList?
     * @var array
     */
    protected static $allowedMcOperators = [
        "==",
        ">=",
        "<=",
        "!=",
        "~~",
        "!~"
    ];

    /**
     * Allowed single-character comparison operators
     * @var array
     */
    protected static $allowedScOperators = [
        ">",
        "<",
    ];

    /**
     * Mapping of operators to their respective methods
     * @var array
     */
    protected static $methodList = [
        "==" => "equals",
        "!=" => "unequals",
        ">=" => "biggerOrEquals",
        "<=" => "lessOrEquals",
        ">" => "bigger",
        "<" => "less",
        "~~" => "like",
        "!~" => "unlike",
    ];

    /**
     * Allowed comparison operators
     * @var array
     */
    protected $allowedOperators = [];

    /**
     * @param string $columnName A key to use for comparison within an object
     * @param string $operator
     * @param mixed $value Value to compare against
     * @throws FilterException In case operator is unrecognized.
     */
    public function __construct($columnName, $operator, $value)
    {
        $this->columnName = $columnName;
        $this->operator = $operator;
        $this->value = $value;
        $this->allowedOperators = array_merge(self::$allowedMcOperators, self::$allowedScOperators);

        if (!in_array($operator, $this->allowedOperators)) {
            throw new FilterException("Ilegal operator '{$operator}'!");
        }
    }

    /**
     * Add more filters to an existing one
     * @param Filter $filter
     * @throws FilterException
     * @fixme this design is far from ideal!
     */
    public function addFilter(self $filter)
    {
        if (empty($this->multiOperator)) {
            throw new FilterException("MultiOperator must be set before adding multiple filters.");
        }

        // seriously, FIXME
        // Anyway, add self to the multifilter, so it is run before the next added filter
        // Could add first filter in create:: or __construct, and use a count() > 1 in compareObject
        if (empty($this->multiFilter)) {
            $this->multiFilter[] = new self($this->getColumnName(), $this->getOperator(), $this->getValue());
        }

        $this->multiFilter[] = $filter;
    }

    /**
     * Set | or & operator for multiple filters
     * @param string $operator
     * @throws FilterException
     */
    public function setMultiOperator($operator)
    {
        if (!in_array($operator, ['|', "&"])) {
            throw new FilterException("Invalid MultiOperator '{$operator}'");
        }

        $this->multiOperator = $operator;
    }

    /**
     * Compare a single value against $this->value using $this->operator
     * @param string $value
     * @return bool
     * @throws FilterException
     */
    public function compare($value)
    {
        if (!method_exists($this, self::$methodList[$this->operator])) {
            throw new FilterException("Method for {$this->operator} does not exist!");
        }

        return $this->{self::$methodList[$this->operator]}($value, $this->value);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @param string $operator
     * @return bool
     */
    public static function staticCompare($value1, $value2, $operator)
    {
        $fn = self::$methodList[$operator];
        return self::$fn($value1, $value2);
    }

    /**
     * Compare a value from within an object
     * using the $columnName, $operator and $value
     * @param \stdClass $object
     * @return bool
     * @throws FilterException
     */
    public function compareObject(\stdClass $object)
    {
        $value = \Keboola\Utils\getDataFromPath($this->columnName, $object, ".");
        if (empty($this->multiFilter)) {
            return $this->compare($value);
        } else {
            if ($this->multiOperator == "&") {
                foreach ($this->multiFilter as $filter) {
                    if (!$filter->compareObject($object)) {
                        return false;
                    }
                }
                return true;
            } elseif ($this->multiOperator == "|") {
                foreach ($this->multiFilter as $filter) {
                    if ($filter->compareObject($object)) {
                        return true;
                    }
                }
                return false;
            } else {
                throw new FilterException("MultiFilter is set but MultiOperator is not recognized.");
            }
        }
    }

    /**
     * Create a filter from a "column==value" like string
     * @param string $filterString
     * @return Filter
     * @throws FilterException
     */
    public static function create($filterString)
    {
        preg_match("(&|\\|)", $filterString, $logicalOperator);
        if (!empty($logicalOperator[0])) {
            $logicalOperator = $logicalOperator[0];
            $filterStrs = explode($logicalOperator, $filterString);
            /** @var Filter $filter */
            $filter = null;
            foreach ($filterStrs as $filterStr) {
                if (empty($filter)) {
                    $filter = self::create($filterStr);
                    $filter->setMultiOperator($logicalOperator);
                } else {
                    $filter->addFilter(self::create($filterStr));
                }
            }
            return $filter;
        }

        preg_match("(>=|<=|==|!=|~~|!~)", $filterString, $operator);

        if (!empty($operator[0]) && in_array($operator[0], self::$allowedMcOperators)) {
            $operator = $operator[0];
        } else {
            preg_match("(>|<)", $filterString, $operator);
            if (!empty($operator[0]) && in_array($operator[0], self::$allowedScOperators)) {
                $operator = $operator[0];
            }
        }

        $allowedOperators = array_merge(self::$allowedMcOperators, self::$allowedScOperators);
        if (empty($operator) || !in_array($operator, $allowedOperators)) {
            throw new FilterException(
                "Error creating a filter from {$filterString}: Operator couldn't be determined. Please use one of [" .
                join(", ", $allowedOperators) . "]"
            );
        }

        list($columnName, $value) = explode($operator, $filterString);

        return new self($columnName, $operator, $value);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function equals($value1, $value2)
    {
        return $value1 == $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function unequals($value1, $value2)
    {
        return $value1 != $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function biggerOrEquals($value1, $value2)
    {
        return $value1 >= $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function lessOrEquals($value1, $value2)
    {
        return $value1 <= $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function bigger($value1, $value2)
    {
        return $value1 > $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function less($value1, $value2)
    {
        return $value1 < $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function like($value1, $value2)
    {
        $regexp = '#^' . str_replace('%', '.*?', preg_quote($value2, '#')) . '$#';
        $ret = preg_match($regexp, $value1);
        return $ret == 1;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function unlike($value1, $value2)
    {
        $regexp = '#^' . str_replace('%', '.*?', preg_quote($value2, '#')) . '$#';
        $ret = preg_match($regexp, $value1);
        return $ret == 0;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @return string
     */
    protected function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set value to compare against
     * @param string $value
     */
    protected function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    protected function getValue()
    {
        return $this->value;
    }
}
