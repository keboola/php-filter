<?php

namespace Keboola\Filter\Tests;

use Keboola\Filter\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testStaticCompare()
    {
        self::assertTrue(Filter::staticCompare("test!#$#%$%{}", "test!#$#%$%{}", "=="));
        self::assertFalse(Filter::staticCompare("test", "test", "!="));
    }

    public function testCompare()
    {
        $filter = new Filter(".", ">", 1);
        self::assertTrue($filter->compare(2));
        self::assertFalse($filter->compare(0));
    }

    public function testCompareObject()
    {
        $filter = new Filter("field", "<=", 1);
        $object = new \stdClass();
        $object->field = 0;
        self::assertTrue($filter->compareObject($object));
        $object->field = 2;
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareNull()
    {
        $object = new \stdClass();
        $object->field2 = null;
        $object->field3 = 'null';

        $filter = Filter::create("field2==");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2!=");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field3==null");
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareString()
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'testing';
        $object->field3 = 'sometest';
        $object->field4 = 'sometesting';

        $filter = Filter::create("field1==test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2==test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field3==test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field4==test");
        self::assertFalse($filter->compareObject($object));

        $filter = Filter::create("field1~~test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2~~test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field3~~test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field4~~test");
        self::assertFalse($filter->compareObject($object));

        $filter = Filter::create("field1~~test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2~~%test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field2~~test%");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2~~%test%");
        self::assertTrue($filter->compareObject($object));

        $filter = Filter::create("field3~~test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field3~~test%");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field3~~%test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field3~~%test%");
        self::assertTrue($filter->compareObject($object));

        $filter = Filter::create("field4~~test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field4~~test%");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field4~~%test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field4~~%test%");
        self::assertTrue($filter->compareObject($object));
    }


    public function testCompareNegString()
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'testing';
        $object->field3 = 'sometest';
        $object->field4 = 'sometesting';

        $filter = Filter::create("field1!=test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field2!=test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field3!=test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field4!=test");
        self::assertTrue($filter->compareObject($object));

        $filter = Filter::create("field1!~test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field2!~test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field3!~test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field4!~test");
        self::assertTrue($filter->compareObject($object));

        $filter = Filter::create("field1!~test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field2!~%test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2!~test%");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field2!~%test%");
        self::assertFalse($filter->compareObject($object));

        $filter = Filter::create("field3!~test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field3!~test%");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field3!~%test");
        self::assertFalse($filter->compareObject($object));
        $filter = Filter::create("field3!~%test%");
        self::assertFalse($filter->compareObject($object));

        $filter = Filter::create("field4!~test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field4!~test%");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field4!~~%test");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field4!~%test%");
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareStringComplex()
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'terrorist';

        $filter = Filter::create("field1~~te%st");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2~~te%st");
        self::assertTrue($filter->compareObject($object));

        $object = new \stdClass();
        $object->field1 = 'testtesttest';
        $object->field2 = 'testtesttest';
        $filter = Filter::create("field1~~%st%st");
        self::assertTrue($filter->compareObject($object));
        $filter = Filter::create("field2~~te%te%");
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareMultiAnd()
    {
        $filter = Filter::create("field1==0&field2!=0");

        $object = new \stdClass();
        $object->field1 = 0;
        $object->field2 = 1;
        self::assertTrue($filter->compareObject($object));

        $object->field2 = 0;
        self::assertFalse($filter->compareObject($object));
    }

    public function testCompareMultiOr()
    {
        $filter = Filter::create("field1==0|field2!=0");
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

    public function testCompareMultiCompound()
    {
        $filter = Filter::create("a==b&c==d|e==f");
        $object = new \stdClass();
        $object->a = "b";
        $object->c = "d";
        $object->e = "nope";
        self::assertTrue($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "nope";
        self::assertFalse($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "f";
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareMultiCompoundReverse()
    {
        $filter = Filter::create("e==f|a==b&c==d");
        $object = new \stdClass();
        $object->a = "b";
        $object->c = "d";
        $object->e = "nope";
        self::assertTrue($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "nope";
        self::assertFalse($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "f";
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareMultiCompoundComplexOr()
    {
        $filter = Filter::create("g==h|e==f|a==b&c==d");
        $object = new \stdClass();
        $object->a = "b";
        $object->c = "d";
        $object->e = "nope";
        $object->g = "nope";
        self::assertTrue($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "nope";
        $object->g = "nope";
        self::assertFalse($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "f";
        $object->g = "nope";
        self::assertTrue($filter->compareObject($object));

        $object->a = "nope";
        $object->c = "nope";
        $object->e = "nope";
        $object->g = "h";
        self::assertTrue($filter->compareObject($object));
    }

    public function testCompareMultiCompoundComplexAnd()
    {
        $filter = Filter::create("g==h|e==f&c==d&a==b");
        $object = new \stdClass();
        $object->a = "b";
        $object->c = "d";
        $object->e = "f";
        $object->g = "nope";
        self::assertTrue($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "nope";
        $object->g = "nope";
        self::assertFalse($filter->compareObject($object));

        $object->a = "b";
        $object->c = "nope";
        $object->e = "f";
        $object->g = "nope";
        self::assertFalse($filter->compareObject($object));

        $object->a = "nope";
        $object->c = "nope";
        $object->e = "nope";
        $object->g = "h";
        self::assertTrue($filter->compareObject($object));
    }

}
