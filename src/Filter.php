<?php

declare(strict_types=1);

namespace Keboola\Filter;

use Keboola\Filter\Exception\FilterException;

/**
 * Filter objects using simple configuration strings
 */
class Filter implements FilterInterface
{
    /** Column to compare */
    protected string $columnName;

    /** Operator to use for comparison */
    protected string $operator;

    /** Value to compare data against */
    protected string $value;

    /** Allowed single-character comparison operators */
    protected const ALLOWED_SC_OPERATORS = [
        '>',
        '<',
    ];

    /**
     * Mapping of operators to their respective methods
     * @var array
     */
    protected static $methodList = [
        '==' => 'equals',
        '!=' => 'unequals',
        '>=' => 'biggerOrEquals',
        '<=' => 'lessOrEquals',
        '>' => 'bigger',
        '<' => 'less',
        '~~' => 'like',
        '!~' => 'unlike',
    ];

    public function __construct(string $filterString)
    {
        preg_match('(>=|<=|==|!=|~~|!~)', $filterString, $operator);

        if (!empty($operator[0]) && in_array($operator[0], array_keys(self::$methodList))) {
            $operator = $operator[0];
        } else {
            preg_match('(>|<)', $filterString, $operator);
            if (!empty($operator[0]) && in_array($operator[0], self::ALLOWED_SC_OPERATORS)) {
                $operator = $operator[0];
            }
        }

        $allowedOperators = array_merge(array_keys(self::$methodList), self::ALLOWED_SC_OPERATORS);
        if (empty($operator) || !in_array($operator, $allowedOperators)) {
            throw new FilterException(
                "Error creating a filter from {$filterString}: Operator couldn't be determined. Please use one of [" .
                implode(', ', $allowedOperators) . ']'
            );
        }

        list($columnName, $value) = explode($operator, $filterString);
        $this->columnName = $columnName;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Compare a value from within an object
     * using the $columnName, $operator and $value
     * @throws FilterException
     */
    public function compareObject(\stdClass $object): bool
    {
        $value = (string) \Keboola\Utils\getDataFromPath($this->columnName, $object, '.');
        return $this->compare($value);
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Compare a single value against $this->value using $this->operator
     * @throws FilterException
     */
    protected function compare(string $value): bool
    {
        if (!method_exists($this, self::$methodList[$this->operator])) {
            throw new FilterException("Method for {$this->operator} does not exist!");
        }

        return $this->{self::$methodList[$this->operator]}($value, $this->value);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function equals($value1, $value2): bool
    {
        return $value1 === $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function unequals($value1, $value2): bool
    {
        return $value1 !== $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function biggerOrEquals($value1, $value2): bool
    {
        return $value1 >= $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function lessOrEquals($value1, $value2): bool
    {
        return $value1 <= $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function bigger($value1, $value2): bool
    {
        return $value1 > $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function less($value1, $value2): bool
    {
        return $value1 < $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function like($value1, $value2): bool
    {
        $regexp = '#^' . str_replace('%', '.*?', preg_quote($value2, '#')) . '$#';
        $ret = preg_match($regexp, $value1);
        return $ret === 1;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return bool
     */
    protected static function unlike($value1, $value2): bool
    {
        $regexp = '#^' . str_replace('%', '.*?', preg_quote($value2, '#')) . '$#';
        $ret = preg_match($regexp, $value1);
        return $ret === 0;
    }
}
