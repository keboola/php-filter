# Filter

## Description
Compare values in objects against pre-set values in the filter.

## Usage

```php
        use Keboola\Filter\Filter;

        // Compare the `.` object (can be a scalar) and check if it's larger than 1
        $filter = new Filter(".", ">", 1);
        $filter->compare(2); // true
        $filter->compare(0); // false

        // Multiple values to compare from a filter string
        $filter = Filter::create("field1==0&field2!=0");

        $object = (object) [
            'field1' => 0,
            'field2' => 1
        ];
        $result = $filter->compareObject($object); // true
```

