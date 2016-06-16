<?php

use Keboola\Filter\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticCompare()
    {
        $this->assertEquals(Filter::staticCompare("test!#$#%$%{}", "test!#$#%$%{}", "=="), true);
        $this->assertEquals(Filter::staticCompare("test", "test", "!="), false);
    }

    public function testCompare()
    {
        $filter = new Filter(".", ">", 1);
        $this->assertEquals($filter->compare(2), true);
        $this->assertEquals($filter->compare(0), false);
    }

    public function testCompareObject()
    {
        $filter = new Filter("field", "<=", 1);
        $object = new \stdClass();
        $object->field = 0;
        $this->assertEquals($filter->compareObject($object), true);
        $object->field = 2;
        $this->assertEquals($filter->compareObject($object), false);
    }

    public function testCompareString()
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'testing';
        $object->field3 = 'sometest';
        $object->field4 = 'sometesting';

        $filter = Filter::create("field1==test");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field2==test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field3==test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field4==test");
        $this->assertEquals($filter->compareObject($object), false);

        $filter = Filter::create("field1~~test");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field2~~test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field3~~test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field4~~test");
        $this->assertEquals($filter->compareObject($object), false);

        $filter = Filter::create("field1~~test");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field2~~%test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field2~~test%");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field2~~%test%");
        $this->assertEquals($filter->compareObject($object), true);

        $filter = Filter::create("field3~~test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field3~~test%");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field3~~%test");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field3~~%test%");
        $this->assertEquals($filter->compareObject($object), true);

        $filter = Filter::create("field4~~test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field4~~test%");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field4~~%test");
        $this->assertEquals($filter->compareObject($object), false);
        $filter = Filter::create("field4~~%test%");
        $this->assertEquals($filter->compareObject($object), true);
    }

    public function testCompareStringComplex()
    {
        $object = new \stdClass();
        $object->field1 = 'test';
        $object->field2 = 'terrorist';

        $filter = Filter::create("field1~~te%st");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field2~~te%st");
        $this->assertEquals($filter->compareObject($object), true);

        $object = new \stdClass();
        $object->field1 = 'testtesttest';
        $object->field2 = 'testtesttest';
        $filter = Filter::create("field1~~%st%st");
        $this->assertEquals($filter->compareObject($object), true);
        $filter = Filter::create("field2~~te%te%");
        $this->assertEquals($filter->compareObject($object), true);
    }

    public function testCompareMulti()
    {
        // &
        $filter = Filter::create("field1==0&field2!=0");

        $object = new \stdClass();
        $object->field1 = 0;
        $object->field2 = 1;
        $this->assertEquals($filter->compareObject($object), true);

        $object->field2 = 0;
        $this->assertEquals($filter->compareObject($object), false);

        // |
        $filter = Filter::create("field1==0|field2!=0");

        $object = new \stdClass();
        $object->field1 = 0;
        $object->field2 = 1;
        $this->assertEquals($filter->compareObject($object), true);

        $object->field2 = 0;
        $this->assertEquals($filter->compareObject($object), true);

        $object->field1 = 1;
        $object->field2 = 0;
        $this->assertEquals($filter->compareObject($object), false);

        // & + |
        $filter = Filter::create("a==b&c==d|e==f");
        $object = new \stdClass();
        $object->a = "b";
        $object->c = "d";
        $object->e = "nope";
        $this->assertEquals($filter->compareObject($object), true);

        $object->a = "b";
        $object->c = "nope";
        $object->e = "nope";
        $this->assertEquals($filter->compareObject($object), false);
    }
}
