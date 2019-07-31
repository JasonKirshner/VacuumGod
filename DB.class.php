<?php
/*
 * This class is intended to query a products and cart table in
 * a MySQL database using mysqli
 */
class DB
{
    private $connection;

    // This constructor creates a connection to the database and returns an error if connection fails
    function __construct()
    {
        $url = env('CLEARDB_DATABASE_URL');
        $parts = parse_url($url);
        $host = $parts["host"];
        $username = $parts["user"];
        $password = $parts["pass"];
        $database = substr($parts["path"], 1);

        $this->connection = new mysqli($host, $username, $password, $database);

        if ($this->connection->connect_error) {
            echo "Connection failed: " . mysqli_connect_error();
            die();
        }
    } // Constructor

    // Function To Get All Products In Database
    function getAllProducts($startNum, $resultsNum)
    {
        $data = array();

        if ($stmt = $this->connection->prepare("SELECT * FROM products WHERE SalePrice = 0.0 ORDER BY ProductName ASC LIMIT " . $startNum . ", " . $resultsNum)) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $name, $desc, $price, $quantity, $img, $sp);

            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    $data[] = array('id' => $id, 'name' => $name, 'description' => $desc, 'price' => $price, 'quantity' => $quantity, 'imageName' => $img, 'salePrice' => $sp);
                }
            }
        }
        return $data;
    } // getAllProducts

    // This function querys the products table to obtain items which 
    // are on sale storing it within an array which is then returned
    function getAllSaleProducts()
    {
        $data = array();

        if ($stmt = $this->connection->prepare("SELECT * FROM products WHERE SalePrice > 0.0")) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $name, $desc, $price, $quantity, $img, $sp);

            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    $data[] = array('id' => $id, 'name' => $name, 'description' => $desc, 'price' => $price, 'quantity' => $quantity, 'imageName' => $img, 'salePrice' => $sp);
                }
            }
        }
        return $data;
    } // getAllSaleProducts

    // This function querys the products table to obtain all product names which
    // is then stored within an array which is then returned
    function getAllProductNames()
    {
        $data = array();

        if ($stmt = $this->connection->prepare("SELECT ProductName FROM products")) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name);

            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    $data[] = $name;
                }
            }
        }
        return $data;
    } // getAllProductNames

    // This function querys the products table to obtain name, price, description, quantity 
    // depending on the id and are stored within an array which is then returned
    function getCartProduct($id)
    {
        if ($stmt = $this->connection->prepare("SELECT ProductName, Price, Description, Quantity FROM products WHERE id = {$id}")) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $price, $desc, $quantity);

            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    $data[] = array('name' => $name, 'price' => $price, 'description' => $desc, 'quantity' => $quantity);
                }
            }
        }
        return $data;
    } // getCartProduct

    // This function querys the cart table to obtain all items which 
    // are in the cart and are stored within an array which is then returned
    function getAllCartProducts()
    {
        $data = array();

        if ($stmt = $this->connection->prepare("SELECT * FROM cart")) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $name, $price, $desc);

            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    $data[] = array('id' => $id, 'name' => $name, 'price' => $price, 'description' => $desc);
                }
            }
        }
        return $data;
    } // getAllCartProducts

    // This function querys the products table to obtain the total count of products
    // in the table and is equated into total amount of pages per # of products which is returned
    function getAllPages($resultsNum)
    {
        $sql = "SELECT COUNT(ProductName) AS total FROM products";
        if ($result = $this->connection->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $pages = ceil($row["total"] / $resultsNum);
            }
        }
        return $pages;
    } // getAllPages

    // This function Deletes all rows in the cart table
    function emptyCart()
    {
        $queryString = "DELETE FROM cart";
        $numRows = 0;

        if ($stmt = $this->connection->prepare($queryString)) {
            $stmt->execute();
            $stmt->store_result();
            $numRows = $stmt->affected_rows;
        }
        return $numRows;
    }

    // This function inserts a product into the cart table
    function insertIntoCart($name, $price, $desc)
    {
        $queryString = "INSERT INTO cart (Name, Price, Description) VALUES (?, ?, ?)";
        $insertId = -1;
        if ($stmt = $this->connection->prepare($queryString)) {
            $stmt->bind_param("sds", $name, $price, $desc);
            $stmt->execute();
            $stmt->store_result();
            $insertId = $stmt->insert_id;
        }
        return $insertId;
    } // insertIntoCart

    // This function inserts products into the product table
    function insertIntoProducts($name, $desc, $price, $quantity, $sp)
    {
        $queryString = "INSERT INTO products (ProductName, Description, Price, Quantity, SalePrice) VALUES (?, ?, ?, ?, ?)";
        $insertId = -1;

        if ($stmt = $this->connection->prepare($queryString)) {
            $stmt->bind_param("ssdid", $name, $desc, $price, $quantity, $sp);
            $stmt->execute();
            $stmt->store_result();
            $insertId = $stmt->insert_id;
        }
        return $insertId;
    } // insertIntoProducts

    // This function querys the products table to obtain item info
    // pertaining to a specific id and returns the info in an array
    function getProductByName($name)
    {
        if ($stmt = $this->connection->prepare("SELECT id, Description, Price, Quantity, SalePrice FROM products WHERE ProductName = '{$name}'")) {
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $desc, $price, $quantity, $sp);

            if ($stmt->num_rows > 0) {
                while ($stmt->fetch()) {
                    $data = array('id' => $id, 'description' => $desc, 'price' => $price, 'quantity' => $quantity, 'salePrice' => $sp);
                }
            }
        }
        return $data;
    } // getProductByName

    // This function updates a product according to a specific id
    function updateProduct($id, $name, $desc, $price, $quantity, $sp)
    {
        if ($stmt = $this->connection->prepare("UPDATE products SET ProductName = ?, Description = ?, Price = ?, Quantity = ?, SalePrice = ? WHERE id = ?")) {
            $stmt->bind_param("ssdidi", $name, $desc, $price, $quantity, $sp, $id);
            $stmt->execute();
            $stmt->store_result();

            $numRows = $stmt->affected_rows;
        }
        return $numRows;
    } // updateProduct

    function updateProductQuantity($name, $quantity)
    {
        if ($stmt = $this->connection->prepare("UPDATE products SET Quantity = ? WHERE ProductName = ?")) {
            $q = $quantity;
            if ($quantity != 0) {
                $q = $quantity - 1;
            }
            $stmt->bind_param("is", $q, $name);
            $stmt->execute();
            $stmt->store_result();

            $numRows = $stmt->affected_rows;
        }
        return $numRows;
    }
}
