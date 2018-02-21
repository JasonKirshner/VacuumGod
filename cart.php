<?php
/*
 * This page displays the current products in the cart
 * and lets the user empty it with a button
 */
require("LIB_project1.php");

// Instantiating Lib object
$lib = new Lib();

// Print out header HTML
echo $lib->html_header("Home", "css/project.css");

// Checks to see if user had pressed the empty cart button
if(isset($_POST['emptyCart'])) {
    echo $lib->html_emptiedCart();
}

// Displays items currently within the cart
echo $lib->html_allCartItems();

echo $lib->html_footer();
?>