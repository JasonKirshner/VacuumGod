<?php
require('DB.class.php');

/*
 * Class Lib is intended to query the database and return it's values through HTML
 */

class Lib {

    private $db;
    private $page;
    private $data;
    private $resultsNum;

    // Upon Lib object instantiation the constructor instantiates a Database class
    function __construct() {
        $this->resultsNum = 5;
        $this->db = new DB();
    }

    // The header to be used on each page
    static function html_header($title="Untitiled", $styles="") {
        $string = <<<END
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>$title</title>
        <link href="$styles" type="text/css" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    </head>
    <body>
        <div class="navbar">
            <div class="nav">
                <h1>Vacuum God</h1>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="admin.php">Admin</a></li>
                </ul>
            </div>
        </div>\n
END;
        return $string;
    }

    // Footer to be used at the bottom of each page
    static function html_footer() {
        $string = "\n</body></html>";
        return $string;
    }

     // The sales function ruquires the current page
     // number calls a function from the db object
     // that returns all products from the database
    function html_products($page) {
        $startNum = ($page-1) * $this->resultsNum;
        $this->data = $this->db->getAllProducts($startNum, $this->resultsNum);
        $string = '<div class="products">';
        $string .= "<h1>Catalog Section</h1>";
        foreach($this->data as $val) {
            $string .= "<div class='product'>";
            $string .= "<img class='prod-info' alt='prod' src='media/".$val['imageName']."'/><div class='prod-info'>";
            $string .= "<h2>".$val['name']."</h2>";
            $string .= "<p>".$val['description']."</p>";
            $string .= "<p>In stock:".$val['quantity']."</p>";
            $string .= "<h4>$".$val['price']."</h4>";
            $string .= "<form method='post' class='buy-form' action='index.php?id=".$val['id']."'>";
            $string .= "<input type='submit' value='Buy'/></form></div></div>";
        }
        $string .= "</div>";
        return $string;
    }

    // The cart function requires an id to reference the items that are currently within the cart and returns the cart items
    function html_cart($id) {
        $this->data = $this->db->getCartProduct($id);
        $string = "<div class='cart-add'><p>";
        foreach($this->data as $val) {
            $this->db->insertIntoCart($val['name'], $val['price'], $val['description']);
            if($val['quantity'] > 0) {
                $this->db->updateProductQuantity($val['name'], $val['quantity']);
                $string .= "The item has been added to your cart!</p></div>";
            }
            else {
                $string .= "This product cannot be added to the cart.</p></div>";
            }
        }
        return $string;
    }

    // The allCartItems function collects the products that are currently available and then are returned
    function html_allCartItems() {
        $this->data = $this->db->getAllCartProducts();
        if(!empty($this->data)) {
            $string = "<div class='cart'>";
            $price = 0;
            foreach($this->data as $val) {
                $string .= "<div class='cart-item'><h2>".$val['name']."</h2>";
                $string .= "<br><p>".$val['description']."</p>";
                $string .= "<br><p>Price: ".$val['price'];
                $string .= " Quantity: 1</p></div>";
                $price += $val['price'];
            }
            $string .= "<p>Total Cost: {$price}</p>".$this->html_emptyCartBtn()."</div>";
            return $string;
        }
        return "Nothing in cart";
    }

    // The emptyCartBtn function returns a button that is used to empty the cart
    function html_emptyCartBtn() {
        $string = "<form action='cart.php' method='post'>
                    <input class='btn-empty' type='submit' name='emptyCart' value='Empty Cart' />
                    </form>";
        return $string;
    }

    // This function is called once the user has emptied the cart and returns a confirmation message to the user
    function html_emptiedCart() {
        $string = "Cart has been emptied";
        $this->db->emptyCart();
        return $string;
    }

    // This function is used to call the database function that returns the number of pages per 5 products
    function html_pages($page) {
        $string = "<div class='page-links'>";
        $pages = $this->db->getAllPages($this->resultsNum);
        $string .= "<a href='index.php?page=1'>&lt;&lt;</a>";
        for ($i=1; $i<=$pages; $i++) {
            if($page != 1 && $i == 1) {
                $back = $page - 1;
                $string .= "<a href='index.php?page={$back}'>&lt;</a>";
            }
            $string .= "<a href='index.php?page=".$i."'";
            if ($i==$page) {
                $string .= " class='curPage'";
            }
            $string .= ">".$i."</a>";
            if($i == ($pages) && $page != $pages) {
                $next = $page + 1;
                $string .= "<a href='index.php?page={$next}'>&gt;</a>";
            }
        }
        $string .= "<a href='index.php?page={$pages}'>&gt;&gt;</a>";
        $string .= "</div>";
        return $string;
    }

    // The adminPage function calls a database function which receieves
    // the names of all products and are then entered as an option within a select menu.
    // Also returning a form in order to edit products
    function html_adminPage() {
        $data = $this->db->getAllProductNames();
        $string = "<div class='admin'><h2>Edit an Item</h2><form action='admin.php' method='post'><select name='product'>";
        foreach($data as $val) {
            $string .= "<option value='{$val}'>{$val}</option>";
        }
        $string .= "</select><input type='submit' value='Edit'/></form>";
        return $string;
    }

    function html_addNewItem() {
        $string = "<h2>Add an Item</h2><form action='admin.php' method='post' class='admin-form'><input name='addItem[]' placeholder='Name'/>".
        "<textarea rows='4' cols='30' name='addItem[]' placeholder='Description'></textarea>".
        "<input name='addItem[]' placeholder='Price'/><input name='addItem[]' placeholder='Quantity'/>".
        "<input name='addItem[]' placeholder='Sale Price'/>".
        "<input name='addItem[]' placeholder='Password'/>".
        "<input type='submit' id='submit' value='Submit Item'/></form></div>";
        return $string;
    }

    // The addItem function requires all product information besides the product id and then calls
    // a database function, which returns the product id.
    function html_addItem($name, $desc, $price, $quantity, $sp, $pw) {
        $fname = filter_var($name, FILTER_SANITIZE_STRING);
        $fdesc = filter_var($desc, FILTER_SANITIZE_STRING);
        $fprice = filter_var($price, FILTER_SANITIZE_STRING);
        $fquant = filter_var($quantity, FILTER_SANITIZE_STRING);
        $fsp = filter_var($sp, FILTER_SANITIZE_STRING);
        $fpw = filter_var($pw, FILTER_SANITIZE_STRING);
        if($fpw = "289ppwl0") {
            if((sizeof($this->db->getAllSaleProducts())+1) <= 5) {
                $this->db->insertIntoProducts($fname, $fdesc, $fprice, $fquant, $fsp);
                $string = "<p class='confirm'>Item has been added to the database!</p>";
                return $string;
            } else {
                return "<p class='confirm'>Max Items On Sale!</p>";
            }
        } else {
            return "<p class='confirm'>Password is incorrect!";
        }
    }

    // The editItem function requires the name of a product and calls a database function with the name
    // returning a form filled out with that product's info.
    function html_editItem($name) {
        $data = $this->db->getProductByName($name);
        $string = "<div class='edit-admin'><form action='admin.php?id=".$data['id']."' method='post' class='admin-form'><p>Name</p><input value='{$name}' name='editItem[]'/>".
                    "<p>Description</p><textarea rows='4' cols='30' name='editItem[]'>".$data['description']."</textarea>".
                    "<p>Price</p><input name='editItem[]' value='".$data['price']."'/><p>Quantity</p><input name='editItem[]' value='".$data['quantity']."'/>".
                    "<p>Sale Price</p><input name='editItem[]' value='".$data['salePrice']."'/>".
                    "<p>Password:</p><input name='editItem[]' />".
                    "<input id='apply' type='submit' value='Apply Changes'/></form></div>";
        return $string;
    }

    // The applyChanges function requiring all product info calls a database function with the product info
    function html_applyChanges($id, $name, $desc, $price, $quantity, $sp, $pw) {
        $fid = filter_var($id, FILTER_SANITIZE_STRING);
        $fname = filter_var($name, FILTER_SANITIZE_STRING);
        $fdesc = filter_var($desc, FILTER_SANITIZE_STRING);
        $fprice = filter_var($price, FILTER_SANITIZE_STRING);
        $fquant = filter_var($quantity, FILTER_SANITIZE_STRING);
        $fsp = filter_var($sp, FILTER_SANITIZE_STRING);
        $fpw = filter_var($pw, FILTER_SANITIZE_STRING);
        if($fpw == "289ppwl0") {
            $string = "<p class='confirm'>";
            $string .= $this->db->updateProduct($fid, $fname, $fdesc, $fprice, $fquant, $fsp);
            $string .= " Affected Rows</p>";
            return $string;
        } else {
            return "<p class='confirm'>Incorrect Password</p>";
        }
    }

    // The saleItems function calls a database function which returns an array of all items that are currently on sale.
    // The function then returns the product info.
    function html_saleItems() {
        $data = $this->db->getAllSaleProducts();
        $string = '<div class="products">';
        $string .= "<h1>Sales Section</h1>";
        foreach($data as $val) {
            $string .= "<div class='product sale-prod'>";
            $string .= "<img alt='prod' class='prod-info' src='media/".$val['imageName']."'/><div class='prod-info'>";
            $string .= "<h2>".$val['name']."</h2>";
            $string .= "<p>".$val['description']."</p>";
            $string .= "<p>In stock:".$val['quantity']."</p>";
            $string .= "<h4><del>$".$val['price']."</del></h4>";
            $string .= "<h4>$".$val['salePrice']."</h4>";
            $string .= "<form method='post' class='buy-form' action='index.php?id=".$val['id']."'>";
            $string .= "<input type='submit' value='Buy'/></form></div></div>";
        }
        $string .= "</div><hr>";
        return $string;
    }
}
?>
