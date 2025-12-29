<?php
session_start();
include "config.php";

if (isset($_POST['qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = intval($id);
        $qty = intval($qty);
        if ($qty > 0) {
            $sql = "UPDATE cart SET quantity=$qty, updated_at=NOW() WHERE id=$id";
            $conn->query($sql);
        }
    }
}
header("Location: cart.php");
exit;
?>
