
<?php
spl_autoload_register(function ($class_name) {
    require_once 'classes/' . $class_name . '.class.php';
});
require_once 'vendor/autoload.php';
include_once(dirname(__FILE__) . '/vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php');

session_start();
$error = "";

if($_SESSION['innlogget'] && $_SESSION['brukerTilgang']->isBrukeradmin()) {
    try {
        $dump = new Ifsnop\Mysqldump\Mysqldump('mysql:dbname=stud_v17_gruppe2;host=kark.hin.no', 'stud_v17_gruppe2', 'gruppe2');
        $dump->start('DataBaseDump.sql');
    } catch (Exception $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    $file_url = 'DataBaseDump.sql';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . date('d-m-Y') . basename($file_url) . "\"");
    readfile($file_url);

    unlink($file_url); //Sletter filen fra server etter download.
}
else {
    header("Location: index.php?error=manglendeRettighet");
}
//echo $twig->render('index.html', array(
//    'error'=>$error));

?>