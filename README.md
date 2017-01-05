# Magento 2 Core Bug Fix for Module Catalog Import Export
This module fixes an issue where the product export would create duplicate rows when a product had HTML Entities in the description field. This Module has been tested with the following Magento versions CE 2.1.2, CE 2.1.3. 

## Installation
This module is installable via `Composer`.

```
$ cd project-root
$ composer require "wearejh/m2-core-bug-catalog-import-export"
```

Note: As these repsoitories are currently private and not available via a public package list like [Packagist](https://packagist.org/) Or [Firegento](http://packages.firegento.com") you need to add the repository to the projects `composer.json` before you require the project.

```
"repositories": [
    {
        "type": "git",
        "url": "git@github.com:wearejh/m2-core-bug-catalog-import-export"
    }
]
```

Enable the module

bin/magento module:enable Jh_CoreBugCatalogImportExport
