# PHP Unit of Work Layer
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/stwarog/uow?style=for-the-badge)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/stwarog/uow?color=%237bfc03&style=for-the-badge&label=version)
### for ORM's using Active Record Pattern

This is the core of Framework agnostic package to split complex Active Record pattern logic to some
persistence layer, in convenient way. Using this tool you can forget about implementation details
as manipulating on relations, saving, detaching etc.

Just keep in mind that you are working on objects!

### Development
This package supports PSR-12 standard. Before each push, run this quality tools command:
```bash 
make check

# or

docker-compose run --rm composer phpcs
docker-compose run --rm composer phpstan
docker-compose run --rm composer unit
```
It will execute Code Sniffer and PhpStan validation rules.

### Config

Name | Type | Default | Description
--- | --- | --- | --- 
foreign_key_check | boolean | true | Allows to globally disable foreign check (not recommended) 
transaction | boolean | true | Allows to globally disable transaction wrapp (not recommended) 
debug | boolean | true | When enabled, all queries and debug info are stored |

### Change Log

##### 2.0.0 (2021-01-27)
* Upgrade to PHP 8.0

##### 1.4.1 (2021-01-13)
* BUGFIX isEmpty in UOW was returning invalid value when only delete added

##### 1.4.0 (2021-07-21)
* Added no longer new method to PersistAble interface (called in UOW when persisted first time)

##### 1.3.1 (2021-07-10)
* Config option to disable transaction
* Config option to disable foreign check & debug moved to decorator

##### 1.2.0 (2021-06-09)
* Dockerized tests for php v7.1
* Make file for easier development
* Static analytics tools: PHPCS and PHPSTAN for ./src directory
* Reformatted code for PSR-12
* Removed MIT license note in every file
* BUG: Added return type in ManyToMany.php
* !! Removed model to in ManyToMany.php
* Usage of Added IterableTrait in AbstractHasManyRelation.php
* Added final to all possible places
* Fixed HasManyTest.php

##### 1.1.1 (2020-01-04)
* *0abc29e8* removed exception throws on empty uow on flush

##### 1.1.0 (2020-12-28) STABLE
* *978f8db0* fixed bug with performance in entity manager
* *1ebbf704* removed webmozart/assert
