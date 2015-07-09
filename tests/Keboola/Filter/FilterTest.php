<?php
use	Keboola\Filter\Filter;

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
