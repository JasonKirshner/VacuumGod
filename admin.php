<?php
/*
 * The admin page is intended to let the user edit a product of their choice
 * and also lets the user add a product
 */

require("LIB_project1.php");

// Instantiating Lib object
$lib = new Lib();

// Printing out admin page HTML
echo $lib->html_header("Home", "css/project.css");

echo $lib->html_adminPage();

// Checks if user has finished editing an item
if(isset($_POST['editItem'])) {
    $item = $_POST['editItem'];
    echo $lib->html_applyChanges($_GET['id'], $item[0], $item[1], $item[2], $item[3], $item[4], $item[5]);
}

// Checks if user has selected an item to edit
if(isset($_POST['Product.class'])) {
    echo $lib->html_editItem($_POST['Product.class']);
 }

 echo $lib->html_addNewItem();
 
 // Checks if user has added an item.
if(isset($_POST['addItem'])) {
    $list = $_POST['addItem'];
    echo $lib->html_addItem($list[0], $list[1], $list[2], $list[3], $list[4], $list[5]);
}

echo $lib->html_footer();
?>