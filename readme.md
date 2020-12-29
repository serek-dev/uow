# PHP Unit of Work Layer
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/stwarog/uow?style=for-the-badge)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/stwarog/uow?color=%237bfc03&style=for-the-badge&label=version)
### for ORM's using Active Record Pattern

This is the core of Framework agnostic package to split complex Active Record pattern logic to some
persistence layer, in convenient way. Using this tool you can forget about implementation details
as manipulating on relations, saving, detaching etc.

Just keep in mind that you are working on objects!

### Config

Name | Type | Default | Description
--- | --- | --- | --- 
foreign_key_check | boolean | true | Allows to globally disable foreign check (not recommended) 
