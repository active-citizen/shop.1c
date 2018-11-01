<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
//    echo "<pre>";
//    print_r($arResult);
//    echo "</pre>";
?>

<div class="container-fluide">
  <div class="row">
    <? include("blocks/errors.inc.php"); ?>

    <? include("blocks/leftbar.inc.php"); ?>
    <? include("blocks/mainpic.inc.php"); ?>


      <!--@ Описание товара @-->
    <? include("blocks/desc.inc.php"); ?>
  </div>


<? include("blocks/accord.inc.php");?>
<? include("blocks/recomends.inc.php");?>

<? include("blocks/modal.inc.php")?>

