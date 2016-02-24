# Filter

## Description
Compare values in objects against pre-set values in the filter.
The filter should be constructed with a key, operator and value to be compared against. Then an object is passed to the filter and evaluated whether it passes the filter or not.

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
- The filter is whitespace sensitive, therefore `value == 100` will look into `value␣` for a `␣100` value, instead of `value` and `100` as likely desired.
- **Correct** use: `value==100`
- **Wrong** use: `value == 100`

## Supported comparison operators

- `==`
- `<`
- `>`
- `<=`
- `>=`
- `!=`

## Supported logical operators
- Filtering by multiple keys or values in an object
- Use if you need to compare more than one value in the object
- OR Use if you want to filter by more possible values in a key

### Supported operators
- `&`
- `|`

### Usage
- Case 1: Object's `status` must be `enabled` and `age` must be over `18`

`status==enabled&age>18`

```
    {
        'status': 'enabled',
        'age': 20
    }
```

`compareObject` on this object will return `true`.

```
    {
        'status': 'enabled',
        'age': 15
    }
```

While this will return `false`.

- Case 2: Object's `status` must be `new` or `udated`

`status==new|status==updated`

```
    {
        'status': 'new'
    }
```

...passes the filter (`true`)

```
    {
        'status': 'updated'
    }
```

...also returns `true`

```
    {
        'status': 'closed'
    }
```

...and this will return `false`

### Combining logical operators
At the current implementation, the **first** logical operator from the left is used as a "major" or operator, and then every other occurence takes precedence over the other operator.

An example should explain this better:

`a==b&c==d|e==f`

This means `a==b&(c==d|e==f)`. There's currently no way to override this behavior, brackets are not supported.

`a==b&c==d|e==f&g==h` translates into `a==b&(c==d|e==f)&g==h`
