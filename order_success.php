<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Successful - DUNZO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f9f9ff; }
        .card { max-width: 500px; margin: 100px auto; text-align: center; padding: 40px 20px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .icon { font-size: 5rem; color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon mb-3">&#10004;</div>
            <h1 class="mb-3">Order Placed Successfully!</h1>
            <p class="text-muted">Thank you for your purchase. Your order is being processed.</p>
            <?php if(isset($_GET['order_id'])): ?>
                <p>Your Order ID is: <strong>#<?= htmlspecialchars($_GET['order_id']) ?></strong></p>
            <?php endif; ?>
            <a href="product.php" class="btn btn-primary mt-3">Continue Shopping</a>
        </div>
    </div>
</body>
</html>