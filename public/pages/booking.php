<?php
session_start();

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userRole = $_SESSION['user_role'] ?? 'guest';
$userNama = $_SESSION['user_nama'] ?? '';
$userNoHp = $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Shift Studio</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/component.css">
</head>
<body>
    <div class="container">
        <h1>Booking Layanan</h1>
        
        <form id="bookingForm">
            <?php if (!$isLoggedIn): ?>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" id="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="no_hp" id="no_hp" placeholder="Masukkan nomor telepon" required>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label>Nama</label>
                <input type="text" value="<?php echo htmlspecialchars($userNama); ?>" disabled>
                <input type="hidden" name="nama" id="nama" value="<?php echo htmlspecialchars($userNama); ?>">
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>Pilih Layanan</label>
                <select name="layanan_id" id="layanan_id" required>
                    <option value="">-- Pilih Layanan --</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tanggal Booking</label>
                <input type="date" name="tanggal" id="tanggal" required>
                <small>Minimal H-1 (besok)</small>
            </div>
            
            <div class="form-group">
                <label>Jam</label>
                <input type="time" name="jam" id="jam" required>
            </div>
            
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="metode_bayar" id="metode_bayar" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>
            
            <div id="errorMsg" style="color: #e74c3c; margin-bottom: 10px;"></div>
            <div id="successMsg" style="color: #27ae60; margin-bottom: 10px;"></div>
            
            <button type="submit" id="submitBtn">Booking Sekarang</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="dashboard.php">Kembali ke Dashboard</a>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
    <script>
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        async function loadLayanan() {
            try {
                const response = await getLayanan();
                if (response.status === 'success') {
                    const select = document.getElementById('layanan_id');
                    response.data.forEach(l => {
                        const option = document.createElement('option');
                        option.value = l.id;
                        option.textContent = `${l.nama} - ${formatRupiah(l.harga)}`;
                        select.appendChild(option);
                    });
                }
            } catch (err) {
                console.error('Gagal load layanan:', err);
            }
        }
        
        function setMinDate() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const yyyy = tomorrow.getFullYear();
            const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
            const dd = String(tomorrow.getDate()).padStart(2, '0');
            document.getElementById('tanggal').min = `${yyyy}-${mm}-${dd}`;
        }
        
        document.getElementById('bookingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorMsg = document.getElementById('errorMsg');
            const successMsg = document.getElementById('successMsg');
            const submitBtn = document.getElementById('submitBtn');
            
            errorMsg.textContent = '';
            successMsg.textContent = '';
            
            const data = {
                layanan_id: parseInt(document.getElementById('layanan_id').value),
                tanggal: document.getElementById('tanggal').value,
                jam: document.getElementById('jam').value,
                metode_bayar: document.getElementById('metode_bayar').value
            };
            
            if (!isLoggedIn) {
                data.nama = document.getElementById('nama').value.trim();
                data.no_hp = document.getElementById('no_hp').value.trim();
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
            
            try {
                const response = await createBooking(data);
                
                if (response.status === 'success') {
                    successMsg.textContent = `Booking berhasil! Nomor antrian: ${response.data.nomor_antrian}`;
                    document.getElementById('bookingForm').reset();
                    setMinDate();
                } else {
                    errorMsg.textContent = response.message || 'Booking gagal';
                }
            } catch (err) {
                errorMsg.textContent = 'Terjadi kesalahan. Coba lagi.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Booking Sekarang';
            }
        });
        
        loadLayanan();
        setMinDate();
    </script>
</body>
</html>
