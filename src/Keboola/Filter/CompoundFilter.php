<?php

namespace Keboola\Filter;

use Keboola\Filter\Exception\FilterException;

class CompoundFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $expression;

    /**
     * CompoundFilter constructor.
     * @param array $logicalExpression
     */
    public function __construct(array $logicalExpression)
    {
        $this->expression = $this->processExpressionArray($logicalExpression);
    }

    /**
     * @param array $expression
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
            $i = 1;
            while ($i < count($expression)) {
                $item = $expression[$i];
                if ($item === '|') {
                    return [
                        'operator' => '|',
                        'op1' => $this->processExpressionArray(array_slice($expression, 0, $i)),
                        'op2' => $this->processExpressionArray(array_slice($expression, $i + 1))
                    ];
                }
                $i = $i + 2;
            }
            $i = 1;
            while ($i < count($expression)) {
                $item = $expression[$i];
                if ($item === '&') {
                    return [
                        'operator' => '&',
                        'op1' => $this->processExpressionArray(array_slice($expression, 0, $i)),
                        'op2' => $this->processExpressionArray(array_slice($expression, $i + 1))
                    ];
                }
                $i = $i + 2;
            }
            throw new FilterException("Invalid logical operator: '" . $expression[1] . "'.");
        }
    }

    /**
     * @param \stdClass $object
     * @param array|Filter $operand
     * @return bool
     */
    private function evaluate(\stdClass $object, $operand)
    {
        if ($operand instanceof Filter) {
            return $operand->compareObject($object);
        } else {
            return $this->compareExpression($object, $operand);
        }
    }

    /**
     * @param \stdClass $object
     * @param $expression
     * @return bool
     */
    private function compareExpression(\stdClass $object, $expression)
    {
        if ($expression['operator'] == '&') {
            return $this->evaluate($object, $expression['op1']) && $this->evaluate($object, $expression['op2']);
        } else {
            return $this->evaluate($object, $expression['op1']) || $this->evaluate($object, $expression['op2']);
        }
    }

    /**
     * @param \stdClass $object
     * @return bool
     */
    public function compareObject(\stdClass $object)
    {
        return $this->compareExpression($object, $this->expression);
    }
}
