<?php

class DbManager
{
    const CREATE_TABLE = "CREATE TABLE IF NOT EXISTS webvision_tax_changer (
                              id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                              new_tax_id INT(11) NOT NULL,
                              old_tax_id INT(11) NOT NULL,
                              field_type VARCHAR(30) NOT NULL
                          )";
    const SELECT_TAX_MAPPER = "SELECT * FROM webvision_tax_changer";
    const INSERT_TAX_MAPPER = "INSERT INTO webvision_tax_changer (new_tax_id, old_tax_id, field_type) VALUES (%s, %s, '%s')";
    const SELECT_LAST_TAX_CLASS_ID = "SELECT class_id FROM tax_class ORDER BY class_id DESC LIMIT 1";
    const INSERT_TAX_CLASS = "INSERT INTO tax_class (class_name, class_type) VALUES (%s, %s)";
    const SELECT_LAST_TAX_RATE_ID = "SELECT tax_calculation_rate_id FROM tax_calculation_rate ORDER BY tax_calculation_rate_id DESC LIMIT 1";
    const INSERT_TAX_RATE = "INSERT INTO tax_calculation_rate (tax_country_id, tax_region_id, tax_postcode, code, rate, zip_is_range, zip_from, zip_to) VALUES (%s, %s, %s, %s, %f, %s, %s, %s)";
    const SELECT_LAST_TAX_RULE_ID = "SELECT tax_calculation_rule_id FROM tax_calculation_rule ORDER BY tax_calculation_rule_id DESC LIMIT 1";
    const INSERT_TAX_RULE = "INSERT INTO tax_calculation_rule (code, priority, position, calculate_subtotal) VALUES (%s, %s, %s, %s)";
    const SELECT_ALL_TAX_CALCULATIONS = "SELECT tax_calculation.*, tax_class.*, tax_calculation_rate.*, tax_calculation_rule.tax_calculation_rule_id, tax_calculation_rule.code as rule_code, 
                                                tax_calculation_rule.priority, tax_calculation_rule.position, tax_calculation_rule.calculate_subtotal 
                                        FROM tax_calculation INNER JOIN tax_class ON tax_calculation.product_tax_class_id = tax_class.class_id INNER JOIN tax_calculation_rate 
                                        ON tax_calculation.tax_calculation_rate_id = tax_calculation_rate.tax_calculation_rate_id INNER JOIN tax_calculation_rule 
                                        ON tax_calculation.tax_calculation_rule_id = tax_calculation_rule.tax_calculation_rule_id";
    const INSERT_TAX_CALCULATION = "INSERT INTO tax_calculation (tax_calculation_rate_id, tax_calculation_rule_id, customer_tax_class_id, product_tax_class_id) VALUES (%s, %s, %s, %s)";
    const SELECT_PRODUCT_ATTRIBUTE = "SELECT * FROM eav_attribute WHERE attribute_code='tax_class_id'";
    const UPDATE_PRODUCT_CLASS = "UPDATE catalog_product_entity_int SET value=%s WHERE attribute_id=%s AND value=%s";
    const DELETE_PRODUCT_CLASS = "DELETE FROM tax_class WHERE class_id=%s";
    const DELETE_TAX_RATE = "DELETE FROM tax_calculation_rate WHERE tax_calculation_rate_id=%s";
    const DELETE_TAX_RULE = "DELETE FROM tax_calculation_rule WHERE tax_calculation_rule_id=%s";
    const DELETE_TAX_MAPPER = "DELETE FROM webvision_tax_changer";

    /**
     * Return database connection
     *
     * @param $host
     * @param $username
     * @param $password
     * @param $dbname
     * @return PDO|null
     */
    public function getConnection($host, $username, $password, $dbname)
    {
        $connection = null;

        try {
            $connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed";
        }

        return $connection;
    }

    public function createTable($connection)
    {
        try {
            $connection->exec(self::CREATE_TABLE);
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Return result of a select query
     * @param $connection
     * @param $query
     * @param bool $fetchMode
     * @return mixed
     */
    private function querySelect($connection, $query, $fetchMode = true)
    {
        $result = [];

        try {
            $result = $connection->prepare($query);
            $result->execute();

            if ($fetchMode) {
                $result->setFetchMode(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            return [];
        }

        return $result;
    }

    /**
     * Update row(s)
     * @param $connection
     * @param $array
     * @return mixed
     */
    public function updateAttribute($connection, $array)
    {
        try {
            $query = $connection->prepare(
                vsprintf(
                    self::UPDATE_PRODUCT_CLASS,
                    $array
                )
            );

            $query->execute();
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $connection
     * @param $query
     * @return bool
     */
    private function queryInsertDelete($connection, $query)
    {
        try {
            $connection->exec($query);
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Return mapper table rows
     *
     * @param $connection
     * @return array
     */
    public function getMapperTableRows($connection)
    {
        return $this->querySelect($connection, self::SELECT_TAX_MAPPER)->fetchAll();
    }

    /**
     * Return latest tax class id
     *
     * @param $connection
     * @return string
     */
    public function getLastClass($connection)
    {
        return $this->querySelect($connection, self::SELECT_LAST_TAX_CLASS_ID, false)->fetchColumn();
    }

    /**
     * Return latest tax rate id
     *
     * @param $connection
     * @return string
     */
    public function getLastRate($connection)
    {
        return $this->querySelect($connection, self::SELECT_LAST_TAX_RATE_ID, false)->fetchColumn();
    }

    /**
     * Return latest tax rula id
     *
     * @param $connection
     * @return string
     */
    public function getLastRule($connection)
    {
        return $this->querySelect($connection, self::SELECT_LAST_TAX_RULE_ID, false)->fetchColumn();
    }

    /**
     * Return latest tax rula id
     *
     * @param $connection
     * @return string
     */
    public function getTaxAttributeId($connection)
    {
        return $this->querySelect($connection, self::SELECT_PRODUCT_ATTRIBUTE, false)->fetchColumn();
    }

    /**
     * Return all tax calculations
     *
     * @param $connection
     * @return array
     */
    public function getAllCalculations($connection)
    {
        return $this->querySelect($connection, self::SELECT_ALL_TAX_CALCULATIONS)->fetchAll();
    }

    /**
     * Removes all rows from mapper table
     *
     * @param $connection
     * @return bool
     */
    public function deleteMapperRows($connection)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                self::DELETE_TAX_MAPPER
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Removes tax classes created by tax changer
     *
     * @param $connection
     * @param $values
     * @return bool
     */
    public function deleteTaxClasses($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::DELETE_PRODUCT_CLASS,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Removes tax rates created by tax changer
     *
     * @param $connection
     * @param $values
     * @return bool
     */
    public function deleteTaxRates($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::DELETE_TAX_RATE,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Removes tax rules created by tax changer
     *
     * @param $connection
     * @param $values
     * @return bool
     */
    public function deleteTaxRules($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::DELETE_TAX_RULE,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Insert mapper row
     * @param $connection
     * @param $values
     * @return string
     */
    public function insertMapper($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::INSERT_TAX_MAPPER,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Insert new tax class
     * @param $connection
     * @param $values
     * @return string
     */
    public function insertClass($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::INSERT_TAX_CLASS,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Insert new tax rate
     * @param $connection
     * @param $values
     * @return string
     */
    public function insertRate($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::INSERT_TAX_RATE,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Insert new tax rule
     * @param $connection
     * @param $values
     * @return string
     */
    public function insertRule($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::INSERT_TAX_RULE,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Insert new tax rule
     * @param $connection
     * @param $values
     * @return string
     */
    public function insertTaxCalculation($connection, $values)
    {
        try {
            $this->queryInsertDelete(
                $connection,
                vsprintf(
                    self::INSERT_TAX_CALCULATION,
                    $values
                )
            );
        } catch(PDOException $e) {
            return false;
        }

        return true;
    }
}
