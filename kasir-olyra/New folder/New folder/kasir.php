<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Kasir</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .menu-item {
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Kasir</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="kasir.php">Kasir</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Daftar Menu</h1>
        <div class="row" id="menu-container"></div>

        <h2>Pesanan Anda</h2>
        <table class="table" id="order-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <p>Total: <span id="total-price">0</span></p>

        <h2>Kasir</h2>
        <p id="checkout-message" style="display:none;"></p>
        <button id="checkout-btn" class="btn btn-success">Bayar</button>
    </div>

    <script>
        const menuItems = <?php
            include 'db.php';
            $result = $conn->query("SELECT * FROM produk");
            $produkList = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $produkList[] = $row;
                }
            }
            echo json_encode($produkList);
            $conn->close();
        ?>;

        let order = {};

        function renderMenuItems() {
            const menuContainer = document.getElementById('menu-container');
            menuItems.forEach(item => {
                const menuItem = document.createElement('div');
                menuItem.className = 'col-md-3 menu-item';
                menuItem.innerHTML = `
                    <img src="${item.gambar}" alt="${item.nama}" class="img-fluid">
                    <p>${item.nama} - $${item.harga}</p>
                `;
                menuItem.addEventListener('click', () => addToOrder(item.nama, item.harga));
                menuContainer.appendChild(menuItem);
            });
        }

        function addToOrder(name, price) {
            if (order[name]) {
                order[name].quantity++;
            } else {
                order[name] = { price, quantity: 1 };
            }
            renderOrder();
        }

        function renderOrder() {
            const orderTableBody = document.querySelector('#order-table tbody');
            orderTableBody.innerHTML = '';
            let total = 0;
            for (const itemName in order) {
                const item = order[itemName];
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${itemName}</td>
                    <td>$${item.price}</td>
                    <td>
                        <button onclick="changeQuantity('${itemName}', -1)">-</button>
                        ${item.quantity}
                        <button onclick="changeQuantity('${itemName}', 1)">+</button>
                    </td>
                    <td>$${item.price * item.quantity}</td>
                `;
                orderTableBody.appendChild(row);
                total += item.price * item.quantity;
            }
            document.getElementById('total-price').textContent = total;
        }

        function changeQuantity(itemName, change) {
            if (order[itemName]) {
                order[itemName].quantity += change;
                if (order[itemName].quantity <= 0) {
                    delete order[itemName];
                }
                renderOrder();
            }
        }

        document.getElementById('checkout-btn').addEventListener('click', () => {
            const total = Object.values(order).reduce((sum, item) => sum + item.price * item.quantity, 0);
            const amountPaid = prompt("Masukkan jumlah yang dibayar:");
            
            if (amountPaid) {
                const change = amountPaid - total;
                if (change < 0) {
                    alert("Jumlah yang dibayar tidak cukup.");
                } else {
                    const message = `Pembayaran berhasil! Total: $${total}, Kembalian: $${change}`;
                    document.getElementById('checkout-message').textContent = message;
                    document.getElementById('checkout-message').style.display = 'block';
                    order = {};
                    renderOrder();
                }
            }
        });

        renderMenuItems();
    </script>
</body>

</html>
