<?php
$connection = new mysqli($_SERVER['DB_SERVER'], $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD'], $_SERVER['DB']);

if ($connection->connect_error) {
    echo "Connection failed: ".mysqli_connect_error();
    die();
}

$results_per_page = 5;

if (isset($_GET["page"])) { 
    $page  = $_GET["page"]; 
} else { 
    $page=1; 
}

$start_from = ($page-1) * $results_per_page;
$sql = "SELECT * FROM products ORDER BY ProductName ASC LIMIT $start_from, ".$results_per_page;
if ($stmt = $connection->prepare("SELECT * FROM products ORDER BY ProductName ASC LIMIT ".$start_from.", ".$results_per_page)) {
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name, $desc, $price, $quantity, $img, $sp);

    if ($stmt->num_rows > 0) {
        while ($stmt->fetch()) {
            echo $name;
        }
    }
} //if $stmt
if($rs_result = $connection->query($sql)) {
?> 
<table border="1" cellpadding="4">
<tr>
    <td bgcolor="#CCCCCC"><strong>Name</strong></td>
    <td bgcolor="#CCCCCC"><strong>desc</strong></td>
    <td bgcolor="#CCCCCC"><strong>Price</strong></td>
    <td bgcolor="#CCCCCC"><strong>count</strong></td>
    <td bgcolor="#CCCCCC"><strong>imgname</strong></td>
    <td bgcolor="#CCCCCC"><strong>sale</strong></td>
</tr>
<?php 
 while($row = $rs_result->fetch_assoc()) {
?> 
            <tr>
            <td><?php echo $row["ProductName"]; ?></td>
            <td><?php echo $row["Description"]; ?></td>
            <td><?php echo $row["Price"]; ?></td>
            <td><?php echo $row["Quantity"]; ?></td>
            <td><?php echo $row["ImageName"]; ?></td>
            <td><?php echo $row["SalePrice"]; ?></td>
            </tr>
<?php 
}
}
?> 
</table>

<?php 
$sql = "SELECT COUNT(ProductName) AS total FROM products";
if($result = $connection->query($sql)) {
    while($row = $result->fetch_assoc()) {
        $total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
        
        for ($i=1; $i<=$total_pages; $i++) {  // print links for all pages
            echo "<a href='test.php?page=".$i."'";
            if ($i==$page)  echo " class='curPage'";
            echo ">".$i."</a> ";
        }
    }
}
?>