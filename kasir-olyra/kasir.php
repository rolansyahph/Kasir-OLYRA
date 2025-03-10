<?php
include 'db.php';

// Ambil daftar produk
$result = $conn->query("SELECT * FROM produk");
$produkList = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $produkList[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Kasir</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
            transition: transform 0.3s ease; /* Animasi efek klik */
        }

        .card-img-top {
            max-height: 180px;
            object-fit: cover;
            width: 100%;
            height: 180px;
        }

        .card-body {
            padding: 0;
            position: relative;
        }

        .card-title {
            font-size: 1rem;
            font-weight: bold;
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.7); /* Background putih transparan */
            text-align: center;
            padding: 5px 0;
        }

        .card-price {
            font-size: 1rem;
            color: black;
            text-align: center;
            padding: 10px 0;
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
        }

        .menu-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 20px;
            justify-content: space-between;
        }

        .menu-item {
            flex: 0 0 calc(25% - 1rem);
            box-sizing: border-box;
            position: relative;
        }

        .menu-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease; /* Animasi untuk gambar */
        }

        .menu-item.active img {
            transform: scale(1.1); /* Efek zoom in */
            opacity: 0.8;
        }

        .menu-item.active .card {
            background-color: #f0f0f0; /* Background berubah saat dipilih */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .menu-item {
                flex: 0 0 calc(33.33% - 1rem);
            }
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination button {
            margin: 0 5px;
        }

        .order-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
        }

        .order-item .action-btns {
            display: flex;
            align-items: center;
        }

        .order-item span {
            margin: 0 5px;
        }

        .remove-icon {
            position: absolute;
            bottom: 5px;
            right: 5px;
            color: red;
            cursor: pointer;
        }

        .btn-money {
            flex: 1;
            margin: 5px;
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
                <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1>Daftar Menu</h1>
                <input type="text" id="search-input" class="form-control mb-3" placeholder="Cari produk...">
                <div class="menu-container" id="menu-container"></div>
                <div class="pagination text-center mt-4">
                    <button id="prev-btn" class="btn btn-secondary" style="display:none;">Sebelumnya</button>
                    <span id="page-numbers" class="mx-2"></span>
                    <button id="next-btn" class="btn btn-secondary">Selanjutnya</button>
                </div>
            </div>
            <div class="col-md-4">
                <h2>Pesanan Anda</h2>
                <div id="order-container"></div>
                <p>Total: <span id="total-price">0</span></p>
                <button id="clear-btn" class="btn btn-danger">Clear Pesanan</button>
                <button id="checkout-btn" class="btn btn-success" data-toggle="modal" data-target="#paymentModal">Bayar</button>
                <p id="checkout-message" style="display:none;"></p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Total yang harus dibayar: <strong id="total-modal"></strong></p>
                    <div class="form-group">
                        <label for="amount-paid">Masukkan Jumlah yang Dibayar:</label>
                        <input type="text" id="amount-paid" class="form-control" placeholder="Masukkan Pembayaran.." oninput="formatRupiah(this)">
                    </div>
                    <div class="btn-group-vertical w-100">
                        <button class="btn btn-success btn-money" id="btn-uang-pas">Uang Pas</button>
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-primary btn-money" onclick="setAmount(10000)">10,000</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary btn-money" onclick="setAmount(20000)">20,000</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary btn-money" onclick="setAmount(50000)">50,000</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary btn-money" onclick="setAmount(100000)">100,000</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirm-payment">Konfirmasi Pembayaran</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let order = {};
        let currentPage = 0;
        const itemsPerPage = 12;
        const allItems = <?php echo json_encode($produkList); ?>;
        let filteredItems = allItems;

        function formatCurrency(value) {
            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function formatRupiah(input) {
            const value = input.value.replace(/\D/g, '');
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function setAmount(amount) {
            document.getElementById('amount-paid').value = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        document.getElementById('btn-uang-pas').addEventListener('click', () => {
            const total = Object.values(order).reduce((sum, item) => sum + item.price * item.quantity, 0);
            setAmount(total);
        });

        function renderMenu() {
            const menuContainer = document.getElementById('menu-container');
            menuContainer.innerHTML = '';
            const start = currentPage * itemsPerPage;
            const end = Math.min(start + itemsPerPage, filteredItems.length);

            for (let i = start; i < end; i++) {
                const item = filteredItems[i];
                menuContainer.innerHTML += `
                    <div class="menu-item" onclick="selectMenuItem(this, '${item.id}', '${item.nama}', ${item.harga})">
                        <div class="card">
                            <img src="${item.gambar}" alt="${item.nama}" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title">${item.nama}</h5>
                            </div>
                            <div class="card-price">${formatCurrency(item.harga)}</div>
                        </div>
                    </div>
                `;
            }

            document.getElementById('prev-btn').style.display = currentPage > 0 ? 'block' : 'none';
            document.getElementById('next-btn').style.display = currentPage < Math.ceil(filteredItems.length / itemsPerPage) - 1 ? 'block' : 'none';
            renderPageNumbers();
        }

        function selectMenuItem(menuItem, id, name, price) {
            const allMenuItems = document.querySelectorAll('.menu-item');
            allMenuItems.forEach(item => item.classList.remove('active'));
            menuItem.classList.add('active');
            addToOrder(id, name, price);
        }

        function renderPageNumbers() {
            const pageNumbersContainer = document.getElementById('page-numbers');
            pageNumbersContainer.innerHTML = '';
            const totalFilteredPages = Math.ceil(filteredItems.length / itemsPerPage);
            for (let i = 0; i < totalFilteredPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.innerText = i + 1;
                pageButton.className = 'btn btn-link';
                pageButton.onclick = () => {
                    currentPage = i;
                    renderMenu();
                };
                if (i === currentPage) {
                    pageButton.disabled = true;
                    pageButton.className += ' font-weight-bold';
                }
                pageNumbersContainer.appendChild(pageButton);
            }
        }

        document.getElementById('prev-btn').addEventListener('click', () => {
            if (currentPage > 0) {
                currentPage--;
                renderMenu();
            }
        });

        document.getElementById('next-btn').addEventListener('click', () => {
            const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
            if (currentPage < totalPages - 1) {
                currentPage++;
                renderMenu();
            }
        });

        function addToOrder(id, name, price) {
            if (order[id]) {
                order[id].quantity++;
            } else {
                order[id] = { id, name, price, quantity: 1 };
            }
            renderOrder();
        }

        function renderOrder() {
            const orderContainer = document.getElementById('order-container');
            orderContainer.innerHTML = '';
            let total = 0;
            for (const itemId in order) {
                const item = order[itemId];
                const orderItem = document.createElement('div');
                orderItem.className = 'order-item';
                orderItem.innerHTML = `
                    <div><strong>Nama:</strong> ${item.name}</div>
                    <div><strong>Harga:</strong> ${formatCurrency(item.price)}</div>
                    <div class="action-btns">
                        <button onclick="changeQuantity('${item.id}', -1)" class="btn btn-sm"><i class="fas fa-minus"></i></button>
                        <span> ${item.quantity} </span>
                        <button onclick="changeQuantity('${item.id}', 1)" class="btn btn-sm"><i class="fas fa-plus"></i></button>
                    </div>
                    <div><strong>Jumlah:</strong> ${formatCurrency(item.price * item.quantity)}</div>
                    <span class="remove-icon" onclick="removeFromOrder('${item.id}')"><i class="fas fa-times"></i></span>
                `;
                orderContainer.appendChild(orderItem);
                total += item.price * item.quantity;
            }
            document.getElementById('total-price').textContent = formatCurrency(total);
            document.getElementById('total-modal').textContent = formatCurrency(total);
        }

        function changeQuantity(itemId, change) {
            if (order[itemId]) {
                order[itemId].quantity += change;
                if (order[itemId].quantity <= 0) {
                    delete order[itemId];
                }
                renderOrder();
            }
        }

        function removeFromOrder(itemId) {
            delete order[itemId];
            renderOrder();
        }

        document.getElementById('clear-btn').addEventListener('click', () => {
            order = {};
            renderOrder();
        });

        document.getElementById('confirm-payment').addEventListener('click', () => {
            const total = Object.values(order).reduce((sum, item) => sum + item.price * item.quantity, 0);
            const amountPaid = document.getElementById('amount-paid').value.replace(/,/g, '');
            const change = parseInt(amountPaid) - total;

            if (change < 0) {
                alert("Jumlah yang dibayar tidak cukup.");
            } else {
                for (const itemId in order) {
                    const item = order[itemId];
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "db.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.send(`record_sale=1&product_id=${item.id}&quantity=${item.quantity}&total=${item.price * item.quantity}`);
                }

                const message = `Pembayaran berhasil! Total: ${formatCurrency(total)}, Kembalian: ${formatCurrency(change)}`;
                document.getElementById('checkout-message').textContent = message;
                document.getElementById('checkout-message').style.display = 'block';
                order = {};
                renderOrder();
                $('#paymentModal').modal('hide');
            }
        });

        document.getElementById('search-input').addEventListener('input', (event) => {
            const searchTerm = event.target.value.toLowerCase();
            filteredItems = allItems.filter(item => item.nama.toLowerCase().includes(searchTerm));
            currentPage = 0;
            renderMenu();
        });

        renderMenu();
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
