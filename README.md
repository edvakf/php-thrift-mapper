# PHP-Thrift-Mapper

Convert a PHP array into an Apache Thrift struct type.

## What is this?

A Thrift struct;

```
struct Bonk
{
  1: string message,
  2: i32 type
}
```

generates a PHP source like the following.


```php
class Bonk {
  static $_TSPEC;

  /**
   * @var string
   */
  public $message = null;
  /**
   * @var int
   */
  public $type = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'message',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['message'])) {
        $this->message = $vals['message'];
      }
      if (isset($vals['type'])) {
        $this->type = $vals['type'];
      }
    }
  }

  public function getName() {
    return 'Bonk';
  }
```

Now, if I want to convert my PHP array to this class, there is no easy way.

## Here comes the ThriftMapper

It populates the Thrift object with the PHP array.

```
$ary = [
  "message" => "Hello!",
  "type" => 123,
];

$bonk = ThriftMapper::map(new Bonk(), $ary);
```
