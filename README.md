# Magento Tax Changer

Due to the Corona pandemic spreading across the world, the German government have decided to lower the VAT (Mehrwertsteuer / MwSt.) in Germany for a 6 month period, starting on 1st July 2020.

To our knowledge the tax changes would be like this:

* 19% Full MwSt. -> 16%
* 7% Reduced MwSt. -> 5%

At the end of this year the taxes will change back to the original percentages.

## What the script does

Tax Changer does the following: 
 
* Adds new tax classes, rules and tax rates to the Magento System
* The old tax rates don't get touched
* All products will be changed, so the new tax rates will be applied according to the former setting.

## What the script does not

The script doesn't clear caches or rebuild indexes. Please make sure, that your take care about these actions on your own. If run be command like or as CronJob, you can use the connection with `&&`for this purpose.

### Example:
```bash
php tax.php && php bin/magento indexer:reindex && php bin/magento c:c
```

You also might also need to check additional caches (eg. Redis, Varnish, CloudFlare) upfront.

## Installation

Download zip file and extract it in the Magento root.

## Usage

By default rates will be changed as specified in the script.

```php
$taxes = [
    19 => 16,
    7 => 5,
];
```

### Creating new taxes

Go to the taxchanger directory and run:

```bash
php tax.php
```
If you run into issue with timeouts or memory exhaustion, you might want to adjust the limits like this:

```bash
php -d memory_limit=1024M -d max_execution_time=0 tax.php
```
Please adjust these settings according to your needs.

Based on taxes specified in the script this will create new taxes and automatically assign them to the products.

### Disable the new taxes
The following commmand will deactivate the new tax rates and apply the old tax rates to the products. The new tax rates will be kept in the system eg. for orders during the year-end closing. 

```bash
php tax.php -b
```

### Removing new taxes

To remove the new taxes completely from the Magento system run:

```bash
php tax.php -r
```

This will remove all taxes created by taxchanger and assign old taxes to each product.\

**IMPORTANT**: All records are tracked in the *webvision_tax_changer* table. You must not change or remove records in this table.

## Notes

The script will not create tax rate titles for each store.

## Warranty
This script comes without any warranty. Please use it at your own risk and make sure to take backups and test the script in a staging / development environment before you run it on a production system.

## Donation / License
This script ist "donate ware" under GPL3.0. We would be glad, if the script is useful for you, if you could donate something via paypal. If you need an invoice, please leave a comment in your donation.

## PayPal Donation Button

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HDGBRLCFRTVPA)

