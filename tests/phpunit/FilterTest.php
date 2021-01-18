<?php

declare(strict_types=1);

namespace Keboola\Filter\Tests;

use Keboola\Filter\Exception\FilterException;
use Keboola\Filter\Filter;
use Keboola\Filter\FilterFactory;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testGetters(): void
    {
        $filter = new Filter('field>=1');
        self::assertEquals('field', $filter->getColumnName());
        self::assertEquals('>=', $filter->getOperator());
        self::assertEquals('1', $filter->getValue());
    }

    public function testInvalidOp(): void
    {
        try {
            new Filter('field+1');
        } catch (FilterException $e) {
            self::assertStringContainsString('Error creating a filter from field+1', $e->getMessage());
        }
    }

    public function testCompareLessThanOrEquals(): void
    {
        $filter = new Filter('field<=1');
        $object = new \stdClass();
        $object->field = 0;
        self::assertTrue($filter->compareObject($object));
        $object->field = 1;
        self::assertTrue($filter->compareObject($object));
        $object->field = 2;
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareGreaterThanOrEquals(): void
    {
        $filter = new Filter('field>=1');
        $object = new \stdClass();
        $object->field = 0;
        self::assertFalse($filter->compareObject($object));
        $object->field = 1;
        self::assertTrue($filter->compareObject($object));
        $object->field = 2;
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareLessThan(): void
    {
        $filter = new Filter('field<1');
        $object = new \stdClass();
        $object->field = 0;
        self::assertTrue($filter->compareObject($object));
        $object->field = 1;
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareGreaterThan(): void
    {
        $filter = new Filter('field>1');
        $object = new \stdClass();
        $object->field = 1;
        self::assertFalse($filter->compareObject($object));
        $object->field = 2;
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareNull(): void
    {
        $object = new \stdClass();
        $object->field2 = null;
        $object->field3 = 'null';

        $filter = FilterFactory::create('field2==');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2!=');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field3==null');
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareString(): void
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'testing';
        $object->field3 = 'sometest';
        $object->field4 = 'sometesting';

        $filter = FilterFactory::create('field1==test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2==test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field3==test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field4==test');
        self::assertFalse($filter->compareObject($object));

        $filter = FilterFactory::create('field1~~test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2~~test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field3~~test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field4~~test');
        self::assertFalse($filter->compareObject($object));

        $filter = FilterFactory::create('field1~~test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2~~%test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field2~~test%');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2~~%test%');
        self::assertTrue($filter->compareObject($object));

        $filter = FilterFactory::create('field3~~test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field3~~test%');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field3~~%test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field3~~%test%');
        self::assertTrue($filter->compareObject($object));

        $filter = FilterFactory::create('field4~~test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field4~~test%');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field4~~%test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field4~~%test%');
        self::assertTrue($filter->compareObject($object));
    }


    public function testCompareNegString(): void
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'testing';
        $object->field3 = 'sometest';
        $object->field4 = 'sometesting';

        $filter = FilterFactory::create('field1!=test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field2!=test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field3!=test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field4!=test');
        self::assertTrue($filter->compareObject($object));

        $filter = FilterFactory::create('field1!~test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field2!~test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field3!~test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field4!~test');
        self::assertTrue($filter->compareObject($object));

        $filter = FilterFactory::create('field1!~test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field2!~%test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2!~test%');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field2!~%test%');
        self::assertFalse($filter->compareObject($object));

        $filter = FilterFactory::create('field3!~test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field3!~test%');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field3!~%test');
        self::assertFalse($filter->compareObject($object));
        $filter = FilterFactory::create('field3!~%test%');
        self::assertFalse($filter->compareObject($object));

        $filter = FilterFactory::create('field4!~test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field4!~test%');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field4!~~%test');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field4!~%test%');
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareStringComplex(): void
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'terrorist';

        $filter = FilterFactory::create('field1~~te%st');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2~~te%st');
        self::assertTrue($filter->compareObject($object));

        $object = new \stdClass();
        $object->field1 = 'testtesttest';
        $object->field2 = 'testtesttest';
        $filter = FilterFactory::create('field1~~%st%st');
        self::assertTrue($filter->compareObject($object));
        $filter = FilterFactory::create('field2~~te%te%');
        self::assertTrue($filter->compareObject($object));
    }
}
