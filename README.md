# PHP Dotenv

Easy access to your environment variables set in the .env file.

## INSTALLATION

`composer require prinx/dotenv`

## USAGE

```php
require_once __DIR__ . '/path/to/vendor/autoload.php';

use function Prinx\Dotenv\env;
use function Prinx\Dotenv\set_env;

// CURRENT_TOKEN will be accessible as environment the till the end of the script.
set_env('CURRENT_TOKEN', 'rxLKo?LeP!FdGshvcxbs');

// Throw an exception if DEV_DB_HOST does not exist in the .env
$hostname = env('DEV_DB_HOST');

// Will return 3306 if DEV_DB_PORT does not exist in the .env
$port = env('DEV_DB_PORT', 3306);

// May be in another file:
$current_token = env('CURRENT_TOKEN');
```

Sample `.env` file:

```env
DEV_DB_HOST=dev_db_ip
DEV_DB_PORT=3306

PROD_DB_HOST=prod_db_ip
PROD_DB_PORT=3308
```

### Getting an environment variable

You need to put a the very top of the file the namespace of the function:

```php
// Top of the file
use function Prinx\Dotenv\env;
```

There is two ways of getting the environment variables:

#### 1. Using the `env()` function

The simple way is to include the `Prinx\Dotenv\env` _function namespace_ at the top of the file and use the `env()` function anywhere in the file.

```php
// Top of the file
use function Prinx\Dotenv\env;

// Somewhere in the code
$hostname = env('DEV_DB_HOST');
```

You can pass a default value that will be return if the variable is not found in the .env file:

```php
$hostname = env('DEV_DB_HOST', 'localhost');
```

### Setting an environment variable

**Note:** Setting an environment variable using the library, will not save the variable inside your `.env` file. It will just make the variable accessible to you till the end of the script.

You first need to put a the very top of the file the namespace of the function:

```php
// Top of the file
use function Prinx\Dotenv\env;
```

There is two ways of setting the environment variables:

#### 1. The simpler way: Using the `set_env()` function

The simpler way is to include the `Prinx\Dotenv\set_env` _function namespace_ at the top of the file and use the `set_env()` function anywhere in the file.

```php
// Top of the file
use function Prinx\Dotenv\set_env;

// Somewhere in the code
set_env('CURRENT_TOKEN', 'rxLKo?LeP!FdGshvcxbs');
set_env('GUEST_NAME', 'john');
```

### The Dotenv class instance

The recommended way to get or to set a variable has been seen above but you can also get or set a variable, using the Dotenv class instance:

You access the `Dotenv` class instance by calling the `env()` function without any parameter:

```php
// Top of the file
// No more need to use function Prinx\Dotenv\set_env; to be able to set a variable
use function Prinx\Dotenv\env;
```

#### Getting a variable

Then you use its `get()` static method to have access to the variables.

```php
$hostname = env()::get('DEV_DB_HOST');
```

With a default value:

```php
$hostname = env()::get('DEV_DB_HOST', 'localhost');
```

or

```php
$env = env();
$hostname = $env::get('DEV_DB_HOST', 'localhost');
```

#### Setting a variable

Use the `set()` static method to set a variable.

```php
env()::set('GUEST_NAME', 'john');
```

or

```php
$env = env();
$env::set('GUEST_NAME', 'john');
```
