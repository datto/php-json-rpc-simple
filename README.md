# json-rpc-simple

This is a request-to-class mapping extension for the [php-json-rpc](https://github.com/datto/php-json-rpc) library. Its purpose is to eliminate the need to write manual mapping functions for API endpoints.

Installation
------------
```javascript
"require": {
  "datto/json-rpc-simple": "~3.0"
}
```    

Usage
-----
First write an API end point:

```php
<?php

namespace Datto\API;

class Math
{
    public function subtract($a, $b)
    {
        return $a - $b;
    }
}
```

Then use the API:

```php
$server = new Server(new Simple\Evaluator());
echo $server->reply('{"jsonrpc": "2.0", "method": "math/subtract", "params": {"a": 3, "b": 2}, "id": 1}');
```

To use a custom root namespace:

```php
$server = new Server(new Simple\Evaluator(new Simple\Mapper('Datto\\NodeAPI')));
echo $server->reply('...');
```
