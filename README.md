- [PHP Dotenv](#php-dotenv)
  - [Installation](#installation)
  - [Usage](#usage)
    - [Basic](#basic)
    - [Writing a .env file](#writing-a-env-file)
      - [Comments](#comments)
      - [Types of values](#types-of-values)
        - [String](#string)
        - [Integer](#integer)
        - [Boolean](#boolean)
        - [Array](#array)
    - [Section support](#section-support)
    - [Referring to another variable](#referring-to-another-variable)
  - [Details on retrieving environment variables](#details-on-retrieving-environment-variables)
    - [Using the `env()` function](#using-the-env-function)
  - [Details on setting environment variables](#details-on-setting-environment-variables)
    - [Using the `addEnv()` function](#using-the-addenv-function)
  - [The main class instance](#the-main-class-instance)
    - [Getting a variable](#getting-a-variable)
    - [Getting all variables](#getting-all-variables)
    - [Adding a variable](#adding-a-variable)
    - [Persisting a variable (writing in the .env file)](#persisting-a-variable-writing-in-the-env-file)
# PHP Dotenv
Get easily access to your environment variables set in your .env file.

## Installation
Open a command prompt into your project root folder and run:
```bash
composer require prinx/dotenv
```

## Usage
### Basic
```php
require_once __DIR__ . '/path/to/vendor/autoload.php';

use function Prinx\Dotenv\env;
use function Prinx\Dotenv\addEnv;

/*
 * 1. Retrieve an environment
 * 1.1. Without a default value, will return false if variable not found
 */
$hostname = env('DEV_DB_HOST');

/*
 * 1.2. Retrieve an environment variable while passing a default value
 * The default value Will be returned if the variable is not found in the .env
 */
$port = env('DEV_DB_PORT', 3306);

/*
 * 2. Set variable for the duration of the script (will not save in the .env file)
 * LOG_LEVEL will be available only for the duration of this script.
 */
addEnv('LOG_LEVEL', 'info');

/*
 * 3. Persisting variable permanently (save in the .env file) 
 * 
 * This will add the variable into the .env file.
 * If the file already contains the variable, the variable will be overwritten.
 * You can pass false as third parameter to disable the overwriting.
 */
persistEnv('LOG_LEVEL', 'warn');
persistEnv('LOG_LEVEL', 'debug', false);
persistEnv('LOG_LEVEL', 'info');

/*
 * 4. Get all variables from the .env file
 */
env()
// OR
allEnv();
```
Now let's see the format of a typical .env file.

### Writing a .env file
*Note: The basic .env file format is basically the same as a .INI file. You can skip this part and continue [here](#referring-to-another-variable) If you already know how to write a .INI file.*

The basic .env file format for most application will be:
```ini
SESSION_DRIVER=file
DEV_DB_HOST=localhost
DEV_DB_PORT=3306

PROD_DB_HOST=prod_db_ip
PROD_DB_PORT=3308 
```

#### Comments
You can write comments in your .env file by preceding the comment by a semi-colon.
Example:
```ini
; The supported driver are file|database
SESSION_DRIVER=file
```
#### Types of values
The package automatically determines the type of the variables.

##### String
```ini
; Will be get as a string
DEFAULT_TITLE=My app
; or
DEFAULT_TITLE="My app"

DB_HOST=173.0.0.0
; or
DB_HOST="173.0.0.0"
```

##### Integer
Any integer will be get as an integer:
```ini
; Will be get as an integer
DB_PORT=3306
```

If you will to get an integer as a string, you need to enclose it by quotes:
```ini
; Will be get as a string
DB_PORT="3306"
```
##### Boolean
The values `true`, `on`, `yes` will be get as the boolean `true`.
The values `false`, `off`, `no` will be get as the boolean `false`.
```ini
; Will be get as a boolean true
USE_FILE_SESSION=true
USE_FILE_SESSION=on
USE_FILE_SESSION=yes

; Will be get as a boolean false
USE_FILE_SESSION=false
USE_FILE_SESSION=off
USE_FILE_SESSION=no
```

If you will to retrieve any of these values true as string you just need to enclose them in quotes:

```ini
USE_FILE_SESSION="true" ; Will be get as the string "true"
USE_FILE_SESSION="on" ; Will be get as the string "on"
USE_FILE_SESSION="yes" ; Will be get as the string "yes"

USE_FILE_SESSION="false" ; Will be get as the string "false"
USE_FILE_SESSION="off" ; Will be get as the string "off"
USE_FILE_SESSION="no" ; Will be get as the string "no"
```

##### Array
You can get values as array by ending the name of the variables by square brackets `[]`.
In your .env:
```ini
ENGINES[]=mariadb 
ENGINES[]=innodb
```
In your code:
```php
$engines = env('ENGINES');
var_dump($engines);
echo 'The first engine is '. $engines[0];
```
```
array(2) {
    [0]=> string(7) "mariadb"
    [1]=> string(6) "innodb"
}

The first engine is mariadb
```
### Section support
The dotenv package support sections in the .env file.
You define a section by begining a line by the name of the section enclosed in square brackets. When a section is defined, anything below will be consider to be in that particular section until a new section is defined.

```ini
; HOST, USER  and DRIVER are in the DB section
[DB]
HOST=localhost
USER=db_user10
DRIVER=mysql

; DRIVER here is in the SESSION section
[SESSION]
DRIVER=file
```
In your php code:
```php
// Will return all the values contained in the section as an array
$db_params = env('DB');
var_dump($db_params)
echo "The database user's name is ".$db_params['USER'];
```
```
array(3) {
    ["HOST"]=> string(9) "localhost"
    ["USER"]=> string(9) "db_user10"
    ["DRIVER"]=> string(5) "mysql"
}

The database user's name is db_user10
```

To retrieve a specific value directly, you can use a dot to separate the section's name from the variable's name when retrieving the value.

```php
$db_driver = env('DB.DRIVER');
$session_driver = env('SESSION.DRIVER');
```
### Referring to another variable
You can refer to the value of another variable in your .env file by putting the name of the variable you are referring to variable inside ${}:
```ini
SESSION_DRIVER=MySQL
INFO=App based on ${SESSION_DRIVER} database
```
```php
// PHP
echo env('INFO'); // App based on MySQL database
```

## Details on retrieving environment variables

You need to put a the very top of the file the namespace of the function:

```php
// Top of the file
use function Prinx\Dotenv\env;
```

There are two ways of getting the environment variables: using the `env()` function directly or using the main package class.

### Using the `env()` function

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

***Note**: If the variable is not defined in the .env file and no default value has been provided, the boolean value false will be returned.*

```php
// An exception will be thrown if DEV_DB_HOST does not exist in the .env
$hostname = env('DEV_DB_HOST');
```
## Details on setting environment variables

***Note**: Setting an environment variable using the library, will not save the variable into your `.env` file. It will just make the variable accessible to you till the end of the script.*

Import the function from its namespace:

```php
// Top of the file
use function Prinx\Dotenv\env;
```

Then, there are two ways of setting the environment variables: using the `addEnv` function or using the main package class.

### Using the `addEnv()` function

The simpler way is to include the `Prinx\Dotenv\addEnv` _function namespace_ at the top of the file and use the `addEnv()` function anywhere in the file.

```php
// Top of the file
use function Prinx\Dotenv\addEnv;

// Somewhere in the code
addEnv('GUEST_NAME', 'john');
```

## The main class instance

You can also get or set a variable using the Dotenv class instance:

You access the main package class by calling the `dotenv()` function without any parameter:

```php
// Top of the file
use function Prinx\Dotenv\dotenv;
```

### Getting a variable
```php
$hostname = dotenv()->get('DEV_DB_HOST');

// With a default value
$hostname = dotenv()->get('DEV_DB_HOST', 'localhost');
```

or maybe:

```php
$env = dotenv();
$hostname = $env->get('DEV_DB_HOST', 'localhost');
```
### Getting all variables
```php
$hostname = dotenv()->all();

// or use get without any parameter
$hostname = dotenv()->get();
```

### Adding a variable

```php
dotenv()->add('GUEST_NAME', 'john');
```
### Persisting a variable (writing in the .env file)

```php
dotenv()->persist('GUEST_NAME', 'john');
```