<?php

include_once "PDO.php";

/**
 * Console program to create model.
 */
if (php_sapi_name() !== 'cli') {
    echo "This is CLI program!"; exit;
}

echo "Want to create new FSBO\\ORM model? Type 'yes' to continue: ";
if (trim(fgets(STDIN)) != 'yes') exit(0);

echo "Enter table name? ";
$table = trim(fgets(STDIN), "\n");

echo "Enter model name? (for example: PhotoListing): ";
$model = trim(fgets(STDIN), "\n");

$conn = FSBO\PDO::getInstance();
$sql = "SHOW COLUMNS FROM {$table}";

$primaryKey = null;
$fields = [];
$properties = [];
$updater = [];

$results = $conn->query($sql);

if (!$results) {
    echo "Can't find this table.\n";
    exit(0);
}

foreach ($conn->query($sql, \PDO::FETCH_ASSOC) as $column) {

    $original = $column['Field'];

    if ($column['Key'] == "PRI") {
        $primaryKey = $original;
        $original = 'id';
    }

    $snaked = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $original)), '_');
    $snaked = preg_replace(["/_i_d$/", "/^(i_|d_|sz_|b_|f_|dt_)/", "/u_r_l/"], ["ID", "", "URL"], $snaked);
    $snaked = explode("_", $snaked);
    $snaked = lcfirst(implode("", array_map(function ($e) {
        return ucfirst($e);
    }, $snaked)));

    $number = 2;
    while (in_array($snaked, $fields)) {
        $snaked .= $number;
        $number++;
    }

    $default = !empty($column['Default']) ? " = {$conn->quote($column['Default'])};" : ";";
    $default = $default == ";" && $column['Null'] == "YES" ? " = null;" : $default;

    $properties[] = "/** @var [{$column['Type']}] \${$snaked} */\n\tpublic \${$snaked}{$default}";

    if ($original != 'id') {
        $fields[] = $snaked;
        $selector[] = "'$original' => '{$snaked}'";
        $updater[] = "'$original' => \$this->{$snaked}";
    } else {
        $selector[] = "'{$primaryKey}' => '$original'";
    }

}

$properties = implode("\n\n\t", $properties);
$selector = implode(",\n\t\t", $selector);
$updater = implode(",\n\t\t", $updater);
$date = date(\DateTime::RFC850);

$class = <<<"FILE"
<?php
/**
* Class {$model} generated {$date}.
*/
namespace FSBO\\ORM;

use FSBO\\ORM;

class {$model} extends ORM {

    const TABLE = '{$table}';

    const PRIMARY_KEY = '{$primaryKey}';

    {$properties}

    static function selector() {
        return [
            {$selector}
        ];
    }

    protected function updater() {
        return [
            {$updater}
        ];
    }

}
FILE;

echo $class . "\n\n";
$dir = __DIR__;

echo "Enter location dir (relative to this dir: {$dir}) to save {$model}.php ,no trailing slash. Enter nothing to cancel.";

$location = trim(fgets(STDIN));
if (empty($location)) exit(0);

if ($written = file_put_contents("{$location}/{$model}.php", $class)) {
    echo "Save successful. Written: {$written} bytes \n";
} else {
    echo "Save failed.\n";
}
exit(0);
