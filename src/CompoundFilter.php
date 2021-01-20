<?php

declare(strict_types=1);

namespace Keboola\Filter;

use Keboola\Filter\Exception\FilterException;

class CompoundFilter implements FilterInterface
{
    /**
     * @var array|Filter
     */
    private $expression;

    public function __construct(array $logicalExpression)
    {
        $this->expression = $this->processExpressionArray($logicalExpression);
    }

    /**
     * @return array|Filter
     * @throws FilterException
     */
    private function processExpressionArray(array $expression)
    {
        if (count($expression) % 2 === 0) {
            throw new FilterException("Invalid syntax in logical expression: '" . implode('', $expression) . "'.");
        } elseif (count($expression) === 1) {
            return new Filter($expression[0]);
        } else {
            /*
             * Here we look for the first operator in the array. & takes precedence over |, so we first start with
             * | as that is the last one to be evaluated. Then slice the array and process each slice
             * recursively. Only process & if there is no |. The actual order of evaluation is full right-to-left.
             */
            $result = $this->checkOperator('|', $expression);
            if ($result) {
                return $result;
            }
            $result = $this->checkOperator('&', $expression);
            if ($result) {
                return $result;
            }
            throw new FilterException("Invalid logical operator: '" . $expression[1] . "'.");
        }
    }

    /**
     * @param mixed $operator
     * @param mixed $expression
     * @return array|null
     */
    private function checkOperator($operator, $expression): ?array
    {
        $i = 1;
        while ($i < count($expression)) {
            $item = $expression[$i];
            if ($item === $operator) {
                return [
                    'operator' => $operator,
                    'op1' => $this->processExpressionArray(array_slice($expression, 0, $i)),
                    'op2' => $this->processExpressionArray(array_slice($expression, $i + 1)),
                ];
            }
            $i = $i + 2;
        }
        return null;
    }

    /**
     * @param \stdClass $object
     * @param array|Filter $operand
     * @return bool
     */
    private function evaluate(\stdClass $object, $operand): bool
    {
        if ($operand instanceof Filter) {
            return $operand->compareObject($object);
        } else {
            return $this->compareExpression($object, $operand);
        }
    }

    /**
     * @param mixed $expression
     */
    private function compareExpression(\stdClass $object, $expression): bool
    {
        if ($expression['operator'] === '&') {
            return $this->evaluate($object, $expression['op1']) && $this->evaluate($object, $expression['op2']);
        } else {
            return $this->evaluate($object, $expression['op1']) || $this->evaluate($object, $expression['op2']);
        }
    }

    public function compareObject(\stdClass $object): bool
    {
        return $this->compareExpression($object, $this->expression);
    }
}
