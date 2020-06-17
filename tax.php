<?php
if (!PHP_SAPI === 'cli') { die(); }

define('DB_CONFIG_DIRECTORY', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'etc');

$magento2conf = DB_CONFIG_DIRECTORY . DIRECTORY_SEPARATOR . 'env.php';
$magento1conf = DB_CONFIG_DIRECTORY . DIRECTORY_SEPARATOR . 'local.xml';
$host = '';
$username = '';
$password = '';
$db = '';

if (file_exists($magento2conf)) {
    $configuration = include $magento2conf;
    $dbData = $configuration['db']['connection']['default'];
    $host = $dbData['host'];
    $username = $dbData['username'];
    $password = $dbData['password'];
    $db = $dbData['dbname'];
} else if (file_exists($magento1conf)) {
    $configuration = simplexml_load_file($magento1conf);
    $dbData = $configuration->global->resources->default_setup->connection;
    $host = (string) $dbData->host;
    $username = (string) $dbData->username;
    $password = (string) $dbData->password;
    $db = (string) $dbData->dbname;
} else {
    die('Configuration file with db credentials can\'t be found.');
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'DbManager.php';

$connector = new DbManager();
$connection = $connector->getConnection($host, $username, $password, $db);

if (!$connection) {
    die();
}

if (!$connector->createTable($connection)) {
    die('Can\'t create a table');
}


function parseNullVal($value)
{
    if ($value === null) {
        return 'NULL';
    }

    return $value;
}

function getRate($value, $taxes)
{
    foreach ($taxes as $prevTax => $newTax) {
        if ($prevTax == $value) {
            return $newTax;
        }
    }

    return null;
}

$taxes = [
    19 => 16,
    7 => 5,
];
$taxClasses = [];
$taxRates = [];
$taxRules = [];
$allCalculations = $connector->getAllCalculations($connection);
$attributeId = $connector->getTaxAttributeId($connection);
$error = false;

if (in_array('-b', $argv)) {
    foreach ($connector->getMapperTableRows($connection) as $row) {
        switch ($row['field_type']) {
            case 'CLASS':
                $connector->updateAttribute($connection, [$row['old_tax_id'], $attributeId, $row['new_tax_id']]);
                break;
            default:
                break;
        }
    }

    die('Products have been updated.');
}

if (in_array('-r', $argv)) {
    foreach ($connector->getMapperTableRows($connection) as $row) {
        switch ($row['field_type']) {
            case 'RATE':
                $connector->deleteTaxRates($connection, [$row['new_tax_id']]);
                break;
            case 'CLASS':
                $connector->updateAttribute($connection, [$row['old_tax_id'], $attributeId, $row['new_tax_id']]);
                $connector->deleteTaxClasses($connection, [$row['new_tax_id']]);
                break;
            case 'RULE':
                $connector->deleteTaxRules($connection, [$row['new_tax_id']]);
                break;
            default:
                break;
        }
    }

    $connector->deleteMapperRows($connection);
    die('All changed taxes are removed and products have been updated.');
}

if (count($connector->getMapperTableRows($connection))) {
    die('Taxes created by tax changer still exist. Please remove them by adding "-r" argument.');
}

foreach ($allCalculations as $row) {
    $rate = getRate($row['rate'], $taxes);

    if ($rate) {
        $taxValues = [
            parseNullVal(str_replace(array_keys($taxes), array_values($taxes), $row['class_name'])),
            parseNullVal($row['class_type'])
        ];
        $taxRateValues = [
            parseNullVal($row['tax_country_id']),
            parseNullVal($row['tax_region_id']),
            parseNullVal($row['tax_postcode']),
            parseNullVal(str_replace(array_keys($taxes), array_values($taxes), $row['code'])),
            parseNullVal($rate),
            parseNullVal($row['zip_is_range']),
            parseNullVal($row['zip_from']),
            parseNullVal($row['zip_to'])
        ];
        $taxRuleValues = [
            parseNullVal(str_replace(array_keys($taxes), array_values($taxes), $row['rule_code'])),
            parseNullVal($row['priority']),
            parseNullVal($row['position']),
            parseNullVal($row['calculate_subtotal'])
        ];

        //@TODO Add tax rate titles for each store

        if (!in_array($row['class_id'], array_keys($taxClasses))) {
            if ($connector->insertClass($connection, $taxValues)) {
                $lastClass = $connector->getLastClass($connection);
                $taxClasses[$row['class_id']] = $lastClass;
                $connector->insertMapper($connection, [
                    $lastClass,
                    $row['class_id'],
                    'CLASS'
                ]);
            } else {
                echo 'Something went wrong while inserting tax class.' . PHP_EOL;
                $error = true;
            }
        }

        if (!in_array($row['tax_calculation_rate_id'], array_keys($taxRates))) {
            if ($connector->insertRate($connection, $taxRateValues)) {
                $lastRate = $connector->getLastRate($connection);
                $taxRates[$row['tax_calculation_rate_id']] = $lastRate;
                $connector->insertMapper($connection, [
                    $lastRate,
                    $row['tax_calculation_rate_id'],
                    'RATE'
                ]);
            } else {
                echo 'Something went wrong while inserting tax rate.' . PHP_EOL;
                $error = true;
            }
        }

        if (!in_array($row['tax_calculation_rule_id'], array_keys($taxRules))) {
            if ($connector->insertRule($connection, $taxRuleValues)) {
                $lastRule = $connector->getLastRule($connection);
                $taxRules[$row['tax_calculation_rule_id']] = $lastRule;
                $connector->insertMapper($connection, [
                    $lastRule,
                    $row['tax_calculation_rule_id'],
                    'RULE'
                ]);
            } else {
                echo 'Something went wrong while inserting tax rule.' . PHP_EOL;
                $error = true;
            }
        }

        $taxCalculationValues = [
            parseNullVal($taxRates[$row['tax_calculation_rate_id']]),
            parseNullVal($taxRules[$row['tax_calculation_rule_id']]),
            parseNullVal($row['customer_tax_class_id']),
            parseNullVal($taxClasses[$row['class_id']])
        ];

        if ($connector->insertTaxCalculation($connection, $taxCalculationValues)) {
            //
        } else {
            echo 'Something went wrong while inserting tax calculation.' . PHP_EOL;
            $error = true;
        }
    }
}

foreach ($connector->getMapperTableRows($connection) as $row) {
    if (!$error && $row['field_type'] === 'CLASS') {
        $connector->updateAttribute($connection, [$row['new_tax_id'], $attributeId, $row['old_tax_id']]);
    }
}

die('New taxes are inserted and products have been updated.');
