<?php

declare(strict_types=1);

namespace Keboola\Filter\Tests;

use Keboola\Filter\Exception\FilterException;
use Keboola\Filter\FilterFactory;
use Keboola\Filter\CompoundFilter;
use PHPUnit\Framework\TestCase;

class CompoundFilterTest extends TestCase
{
    public function testInvalidCompoundFilter(): void
    {
        try {
            new CompoundFilter(['field1==0', '&']);
        } catch (FilterException $e) {
            self::assertStringContainsString('Invalid syntax in logical expression', $e->getMessage());
        }
    }

    public function testInvalidCompoundFilter2(): void
    {
        try {
            new CompoundFilter(['field1==0', '+', 'field2==0']);
        } catch (FilterException $e) {
            self::assertStringContainsString('Invalid logical operator: \'+\'.', $e->getMessage());
        }
    }

    public function testCompareCompoundAnd(): void
    {
        $filter = FilterFactory::create('field1==0&field2!=0');

        $object = new \stdClass();
        $object->field1 = 0;
        $object->field2 = 1;
        self::assertTrue($filter->compareObject($object));

        $object->field2 = 0;
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareCompoundOr(): void
    {
        $filter = FilterFactory::create('field1==0|field2!=0');
        $object = new \stdClass();
        $object->field1 = 0;
        $object->field2 = 1;
        self::assertTrue($filter->compareObject($object));

        $object->field2 = 0;
        self::assertTrue($filter->compareObject($object));

        $object->field1 = 1;
        $object->field2 = 0;
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareCompound(): void
    {
        $filter = FilterFactory::create('a==b&c==d|e==f');
        $object = new \stdClass();
        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'f';
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareCompoundReverse(): void
    {
        $filter = FilterFactory::create('e==f|a==b&c==d');
        $object = new \stdClass();
        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'f';
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareCompoundComplexOr(): void
    {
        $filter = FilterFactory::create('g==h|e==f|a==b&c==d');
        $object = new \stdClass();
        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'f';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'nope';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'h';
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareCompoundComplexAnd(): void
    {
        $filter = FilterFactory::create('g==h|e==f&c==d&a==b');
        $object = new \stdClass();
        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'f';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'f';
        $object->g = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'nope';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'h';
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareCompoundComplexOrReverse(): void
    {
        $filter = FilterFactory::create('a==b&c==d|g==h|e==f');
        $object = new \stdClass();
        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'f';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'nope';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'h';
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareCompoundComplexAndReverse(): void
    {
        $filter = FilterFactory::create('c==d&a==b&g==h|e==f');
        $object = new \stdClass();
        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'f';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'nope';
        $object->e = 'f';
        $object->g = 'nope';
        self::assertTrue($filter->compareObject($object));

        $object->a = 'b';
        $object->c = 'd';
        $object->e = 'nope';
        $object->g = 'nope';
        self::assertFalse($filter->compareObject($object));

        $object->a = 'nope';
        $object->c = 'nope';
        $object->e = 'nope';
        $object->g = 'h';
        self::assertFalse($filter->compareObject($object));
    }
}
