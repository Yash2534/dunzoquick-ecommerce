<?php
include "config.php";
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM cart WHERE id=$id";
    $conn->query($sql);
}
header("Location: cart.php");
exit;
?>
