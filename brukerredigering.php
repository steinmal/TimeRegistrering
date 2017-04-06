<?php
spl_autoload_register(function ($class_name) {
    require_once 'classes/' . $class_name . '.class.php';
});

require_once 'vendor/autoload.php';
include('auth.php');
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);
$UserReg = new UserRegister($db);
$mismatch = "";
$error = "";

session_start();

if(!isset($_SESSION['innlogget']) || $_SESSION['innlogget'] == false){
    header("Location: index.php?error=ikkeInnlogget");
    return;
}

if((!isset($_SESSION['brukerTilgang']) || $_SESSION['brukerTilgang']->isBrukeradmin() != true)
        && $_REQUEST['brukerId'] != $_SESSION['bruker']->getId()){
    header("Location: index.php?error=manglendeRettighet&side=brred");
    //echo "Du har ikke tilgang til Brukerredigering";
    //Foreslår returnering til index.php?error=noAccess eller lignende
    return;
}

if(!isset($_REQUEST['brukerId'])){
    header("Location: brukeradministrering.php?error=noRadio");
    return;
}
if(isset($_REQUEST['action'])){
    $bruker = $UserReg->hentBruker($_REQUEST['brukerId']);
    switch ($_REQUEST['action']) {
        case 'Rediger':
            break;
        case 'Lagre':
            if($_SESSION['brukerTilgang']->isBrukeradmin()){
                $bruker->setNavn($_POST['navn']);
                $bruker->setBrukertype($_POST['type']);
            }
            echo $bruker->getBrukertype();
            $bruker->setEpost($_POST['epost']);
            $bruker->setTelefon($_POST['telefon']);
            
            $UserReg->redigerBruker($bruker);
            if($_SESSION['brukerTilgang']->isBrukeradmin()){
                header("Location: brukeradministrering.php?error=lagret");
            }
            else{
                header("Location: index.php?error=lagret");
            }
            return;
        case 'Bekreft':
            if($_POST['nytt_pass'] != $_POST['nytt_pass2']){
                header("Location: brukerredigering.php?action=Rediger&brukerId=" . $_REQUEST['brukerId'] . "&error=mismatch");
                return;
            }
            $bruker->setPassord($_POST['nytt_pass']);
            $UserReg->redigerBruker($bruker);
            break;
    }
}

if(isset($_REQUEST['deaktiver'])) {
    $brukerID = $_REQUEST['brukerId'];
    $UserReg->deaktiverBruker($brukerID);
    if($_SESSION['brukerTilgang']->isBrukeradmin()){
        header("Location: brukeradministrering.php?error=deaktivert");
        return;
    }
}

$typer = $UserReg->getAlleBrukertyper();
if(isset($_GET['error'])){
    if($_GET['error'] == "mismatch"){
        $mismatch = 1;
    }
    $error = $_GET['error'];
}

echo $twig->render('brukerredigering.html', array('mismatch'=>$mismatch, 'innlogget'=>$_SESSION['innlogget'], 'bruker'=>$bruker,  'error'=>$error, 'typer'=>$typer, 'userReg'=>$UserReg, 'brukerTilgang'=>$_SESSION['brukerTilgang']));

?>