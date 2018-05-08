<?
if (!empty($_COOKIE["EMPSESSION"])) {
    return;
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/local/libs/classes/ag/Auth.php");

$auth = new \ag\Auth();
$auth->performAuth();