# json-rpc-simple

This is a request-to-class mapping extension for the [php-json-rpc](https://github.com/datto/php-json-rpc) library. Its purpose is to eliminate the need to write manual mapping functions for API endpoints by providing an automatic mapping of the JSON-RPC `method` and `params` arguments to a matching PHP class, method and parameters.

Requirements
------------
* PHP >= 5.3

License
-------
This package is released under an open-source license: [LGPL-3.0](https://www.gnu.org/licenses/lgpl-3.0.html)

Examples
--------
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

Then use the API (with the default namespace `Datto\API`):

```php
// This will instantiate an object of the type `Datto\API\Math`,
// call the `subtract` method, and return a corresponding JSON-RPC response.

$server = new Server(new Simple\Evaluator());
echo $server->reply('{"jsonrpc": "2.0", "method": "math/subtract", "params": {"a": 3, "b": 2}, "id": 1}');
```

Or to use a custom root namespace (here: `Datto\NodeAPI`):

```php
$server = new Server(new Simple\Evaluator(new Simple\Mapper('Datto\\NodeAPI')));
echo $server->reply('...');
```

Installation
------------
```javascript
"require": {
  "datto/json-rpc-simple": "~4.0"
}
```   

Author
------
[Philipp C. Heckel](https://github.com/binwiederhier)
