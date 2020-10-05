# PDF Invoice Module

## Description

PDF invoice module for OXID eShop.

### Requirements

* OXID eShop v6.*

## Installation

Please proceed with one of the following ways to install the module:

### Module installation via composer

In order to install the module via composer, run the following commands in commandline of your shop base directory 
(where the shop's composer.json file resides).

```
composer require oxid-projects/pdf-invoice-module
```

### Module installation via repository cloning

Clone the module to your OXID eShop **modules/oe/** directory:
```
git clone https://github.com/OXIDprojects/pdf-invoice-module.git invoicepdf
```

Now you have to install the module with the oe-console, via command-prompt respectively ssh,
in order to get it listet in the backend under modules:

````
vendor/bin/oe-console oe:module:install-configuration source/modules/oe/invoicepdf
````


### Module installation from zip package

* Make a new folder "invoicepdf" in the **modules/oe/ directory** of your shop installation. 
* Download the https://github.com/OXIDprojects/pdf-invoice-module/archive/master.zip file and unpack it into the created folder.

Now you have to install the module with the oe-console, via command-prompt respectively ssh,
in order to get it listet in the backend under modules:

````
vendor/bin/oe-console oe:module:install-configuration source/modules/oe/invoicepdf
````


## Activate Module

- Activate the module in the administration panel.

## Uninstall

Disable the module in administration area and delete the module folder.