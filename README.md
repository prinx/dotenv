<div align="center">
<h1>PHP Dotenv</h1>

<a href="https://travis-ci.com/prinx/dotenv"><img src="https://travis-ci.com/prinx/dotenv.svg?branch=main"></a>
<a href="https://travis-ci.com/prinx/dotenv"><img src="https://img.shields.io/badge/License-MIT-yellow.svg"></a>
</div>

Get easily access to environment variables.

## Installation

Open a command prompt into your project root folder and run:

```console
composer require prinx/dotenv
```

## Usage

### Quick start

```php
// Require composer autoload file if it has not been done yet.
require_once __DIR__ . '/path/to/vendor/autoload.php';

/*
 * Retrieve an environment variable. Returns null if variable not found.
 */
$hostname = env('DEV_DB_HOST');

/*
 * Retrieve an environment variable. Returns default value passed as second argument if variable not found
 */
$port = env('DEV_DB_PORT', 3306);

/*
 * Add a variable to the current loaded environment (will not save in the .env file)
 */
addenv('LOG_LEVEL', 'info');

/*
 * Wrtie variable to the env file. 
 * Will also automatically load the variable into the current environment.
 * If the file already contains the variable, the variable will be overwritten.
 */
persistenv('LOG_LEVEL', 'warn');
persistenv('LOG_LEVEL', 'debug');
persistenv('LOG_LEVEL', 'info');

/*
 * Get all environment variables
 */
env()
// OR
allenv();
```

### Writing a .env file

The .env file format will be:

```ini
VARIABLE_NAME=value
```

For example:

```ini
SESSION_DRIVER=file
DEV_DB_HOST=localhost
DEV_DB_PORT=3306

PROD_DB_HOST=prod_db_ip
PROD_DB_PORT=3308 
```

> As standard, the variable name is capital letter with underscores to separate words.

#### Comments

You can write comments in your .env file by preceding the comment by a hash (`#`).
Example:

```ini
# Supported: file|database
SESSION_DRIVER=file
```

#### Types of values

By default, env variable will be retrieved as string, except booleans, and null.

##### String

You can use quotes to surround strings.

```ini
APP_NAME=My app
# or
APP_NAME="My app"

DB_HOST=173.0.0.0
# or
DB_HOST="173.0.0.0"
```

##### Boolean

The values `true`, `"true"` or `'true'`, will be got as the boolean `true`.
The values `false`, `"false"` or `'false'` will be got as the boolean `false`.

```ini
# Will be got as a boolean true
APP_DEBUG=true

# Will be got as a boolean false
APP_DEBUG=false
```

Same as:

```ini
APP_DEBUG="true"

APP_DEBUG="false"
```

##### Null

The values `null`, `"null"` or `'null'`, will be got as `null`.

```ini
# Will be got as a null
APP_DEBUG=null

APP_DEBUG="null"
```

### Referring to another variable

You can refer to the value of another variable in your .env file by putting the name of the variable you are referring to variable inside ${}:

```ini
# .env
SESSION_DRIVER=mysql
MESSAGE=App based on ${SESSION_DRIVER} database
```

```php
// PHP
echo env('MESSAGE'); // App based on mysql database
```

## Loading a specific .env file

By default, the package automatically look for the .env file in the project root folder. But you can load the env file from anywhere by using the `loadenv` function:

```php
// Require composer autoload file if it has not been done yet.
require_once __DIR__ . '/path/to/vendor/autoload.php';

loadenv('/path/to/somewhere/.env');

// Then everything goes as usual
$apiKey = env('API_KEY');
```

## Using the Dotenv instance

You can also get or set a variable using the Dotenv class instance:

The Dotenv instance can be accessed by calling the `dotenv()` function:

```php
$dotenv = dotenv();
```

### Getting a variable

```php
$hostname = dotenv()->get('DEV_DB_HOST');

// With a default value
$hostname = dotenv()->get('DEV_DB_HOST', 'localhost');
```

### Getting all variables

```php
$hostname = dotenv()->all();

// or use get without any parameter
$hostname = dotenv()->get();
```

### Adding a variable to the current loaded environment

```php
dotenv()->add('GUEST_NAME', 'john');
```

### Writing a variable to the .env file

```php
dotenv()->persist('GUEST_NAME', 'john');
```

## Create your own Dotenv instance

You can create your own Dotenv instance just by using the `Dotenv` class:

```php
use Prinx\Dotenv;

$dotenv = new Dotenv('path/to/.env');

$dotenv->get('VARIABLE');
```

## Contributing

- Give a star to the repo :grin:
- Fork the repo.
- Correct a bug, add a new feature.
- Write tests :fire:
- Create a pull request.

## License

[MIT][LICENSE]
