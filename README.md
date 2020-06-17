# Magento Tax Changer

Tax Changer is a script which easily switch tax classes. It creates new classes, rates and rules based on existing ones and change the tax rate to the rate specified in the script.

##Installation

Download zip file and extract it in the Magento root.

##Usage

By default rates will be changed as specified in the script.

```php
$taxes = [
    19 => 16,
    7 => 5,
];
```

####Creating new taxes

Go to the taxchanger directory and run:

```bash
php tax.php
```

Based on taxes specified in a script this will create new taxes and automatically assign them to the products.

####Removing new taxes

To remove new and assign previous taxes to the products run:

```bash
php tax.php -d
```

This will remove all taxes created by taxchanger and assign old taxes to each product.\
IMPORTANT: All records are tracked in the *webvision_tax_changer* table. You must not change or remove records in this table.

##Notes

The script will not create tax rate titles for each store.
