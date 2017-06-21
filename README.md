# Filter

## Description
Compare values in objects against pre-set values in the filter.
The filter is constructed from a string with a key (field name in 
the the object), operator and a value to be compared against. 
Then an object is passed to the filter and evaluated whether it 
passes the filter or not.

## Usage

```php
use Keboola\Filter\Filter;

// Compare the `shoeSize` property of John
$john = new \stdClass();
$john->shoeSize = 45;
$filter = FilterFactory::create("shoeSize>42");
$filter->compareObject($john); // true

// Multiple conditions can be used
$filter = FilterFactory::create("field1==0&field2!=0");
$object = (object) [
    'field1' => 0,
    'field2' => 1
];
$result = $filter->compareObject($object); // true
```

- The filter is whitespace sensitive, therefore `value == 100` will look 
into `value␣` for a `␣100` value, instead of `value` and `100` as likely desired.
- **Correct** use: `value==100`
- **Wrong** use: `value == 100`

## Supported comparison operators

- `<` -- less than
- `>` -- greater than
- `==` -- equals
- `<=` -- less or equals
- `>=` -- greater or equals
- `!=` -- not equals
- `~~` -- like
- `!~` -- not like

### Like operator
Like (and not like) operator allows you to use partial matching. Use a 
percent `%` character in the target value to match any number of characters, e.g.:
 
```php
use Keboola\Filter\Filter;

// Compare the `shoeSize` property of John
$john = new \stdClass();
$john->name = "Johnny";
$filter = FilterFactory::create("name~~Johnny");
$filter->compareObject($john); // true
```

## Supported logical operators
With logical operators you can combine multiple conditions together.
You can combine both conditions with different values and conditions
with different keys. Supported logical operators:

- `&` -- logical and
- `|` -- logical or

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

`compareObject` on this object will return `false`.

- Case 2: Object's `status` must be `new` or `udated`

`status==new|status==updated`

```
{
    'status': 'new'
}
```

`compareObject` on this object will return `true`.

```
{
    'status': 'updated'
}
```

`compareObject` on this object will return `true`.

```
{
    'status': 'closed'
}
```

`compareObject` on this object will return `false`.

### Combining logical operators
Parentheses are not supported, however the standard operator precedence is
applied (`&` precedes `|`). For example, the expression
`a==b&c==d|e==f` is interpreted as `(a==b&c==d)|e==f` and the  
expression `a==b&c==d|e==f&g==h` translates into `(a==b&c==d)|(e==f&g==h)`.
