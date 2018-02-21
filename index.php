<?php
/*
 * This page displays all products along with products which are on sale
 */
require("LIB_project1.php");

session_start();

// Instantiates Lib object
$lib = new Lib();

echo $lib->html_header("Home", "css/project.css");

// Determines what the page number is and stores it in a variable
if (isset($_GET["page"])) { 
    $page  = $_GET["page"]; 
} else { 
    $page=1;
}

// Checks if a product has been added to the cart and then calls a Lib function
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    echo $lib->html_cart($id);
}

// Displays all products and pagel links
echo $lib->html_saleItems();
echo $lib->html_products($page);
echo $lib->html_pages($page);

echo $lib->html_footer();
?>

