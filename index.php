<?php
class User
{
    protected $nama;
    protected $noHp;

    public function __construct($nama, $noHp)
    {
        $this->nama = $nama;
        $this->noHp = $noHp;
    }


    public function getNama()
    {
        return $this->nama;
    }
    public function getStatus()
    {
        return "Status user";
    }

     public function getNoHP() {
        return $this->noHp;
    }
}

class Pelanggan extends User
{
    private $poin = 0;

    public function __construct($nama, $noHp)
    {
        parent::__construct($nama, $noHp);
    }

    public function tambahPoin($totalPembayaran)
    {
        $this->poin = floor($totalPembayaran / 10000);
    }

    public function getPoin()
    {
        return $this->poin;
    }
}
class Layanan
{
    public $jenisLayanan;
    public $tarif;

    public function __construct($jenisLayanan)
    {
        $this->jenisLayanan = $jenisLayanan;
        if ($jenisLayanan == "goRide Reguler") {
            $this->tarif = 2500;
        } elseif ($jenisLayanan == "goRide Prioritas") {
            $this->tarif = 3000;
        } elseif ($jenisLayanan == "goCar") {
            $this->tarif = 4500;
        } elseif ($jenisLayanan == "goCar XL") {
            $this->tarif = 6000;
        } else {
            $this->tarif = 2000;
        }
    }
}
class Voucher
{
    public $kodeVoucher;
    public $diskonPersen;

    public function __construct($kodeVoucher)
    {
        $this->kodeVoucher = $kodeVoucher;

        if ($kodeVoucher == "HEMAT10") {
            $this->diskonPersen = 0.10;
        } else if ($kodeVoucher == "HEMAT20") {
            $this->diskonPersen = 0.20;
        } else if ($kodeVoucher == "HEMAT30") {
            $this->diskonPersen = 0.30;
        } else {
            $this->diskonPersen = 0;
        }
    }

    public function hitungDiskon($subtotal)
    {
        return $subtotal * ($this->diskonPersen / 100);
    }
}

class Pembayaran
{
    public $metode;

    public function __construct($metode) {
        $this->metode = $metode;
    }

    public function getMetode() {
        return 0;
    }
}

class Ewallet extends Pembayaran
{
    #[Override]
    public function getMetode()
    {
        return 1000;
    }
}

class Transferbank extends Pembayaran
{
    #[Override]
    public function getMetode()
    {
        return 2500;
    }
}

class Cash extends Pembayaran
{
    #[Override]
    public function getMetode()
    {
        return 0;
    }
}

class Transaksi
{
    public $pelanggan;
    public $layanan;
    public $pembayaran;
    public $voucher;
    public $jarakTempuh;

    public function __construct($pelanggan, $layanan, $pembayaran, $voucher, $jarakTempuh)
    {
        $this->pelanggan = $pelanggan;
        $this->layanan = $layanan;
        $this->pembayaran = $pembayaran;
        $this->voucher = $voucher;
        $this->jarakTempuh = $jarakTempuh;
    }

    public function hitungSubtotal()
    {
        return $this->jarakTempuh * $this->layanan->tarif;
    }

    public function hitungDiskon()
    {
        $subtotal = $this->hitungSubtotal();
        if ($subtotal > 50000) {
            return $subtotal * 0.5;
        }
        return 0;
    }

    public function hitungTotal()
    {
        $subtotal = $this->hitungSubtotal();
        $diskonMember = $this->hitungDiskon();
        $diskonVoucher = $this->voucher->hitungDiskon($subtotal);
        $biayaAdmin = $this->pembayaran->getBiayaAdmin();

        $total = $subtotal - $diskonMember - $diskonVoucher + $biayaAdmin;

        if ($total < 0) {
            return 0;
        } else {
            return $total;
        }
    }
}

$cetakNama = "";
$cetakNoHP = "";
$cetakLayanan = "";
$cetakJarak = 0;
$cetakPembayaran = "";
$cetakVoucher = "";
$cetakTotal = 0;
$cetakPoin = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namaInput = $_POST['nama'];
    $noHPInput = $_POST['noHP'];
    $layananInput = $_POST['layanan'];
    $pembayaranInput = $_POST['pembayaran'];
    $voucherInput = $_POST['voucher'];
    $jarakInput = $_POST['jarak'];

    if (empty(trim($namaInput))) {
        $pesanError = "Gagal: Nama tidak boleh kosong!";
    } elseif (strlen($noHPInput) < 10) {
        $pesanError = "Gagal: Nomor HP harus minimal 10 digit!";
    } elseif ($jarakInput <= 0) {
        $pesanError = "Gagal: Jarak tempuh harus lebih dari 0 KM!";
    } else {
        $pelangganBaru = new Pelanggan($namaInput, $noHPInput);
        $layananBaru = new Layanan($layananInput);
        $voucherBaru = new Voucher($voucherInput);

        if ($pembayaranInput == "eWallet") {
            $pembayaranBaru = new EWallet($pembayaranInput);
        } elseif ($pembayaranInput == "Transfer Bank") {
            $pembayaranBaru = new TransferBank($pembayaranInput);
        } else {
            $pembayaranBaru = new Cash($pembayaranInput);
        }

        $transaksiBaru = new Transaksi($pelangganBaru, $layananBaru, $pembayaranBaru, $voucherBaru, floatval($jarakInput));
        
        $cetakTotal = $transaksiBaru->hitungTotal();
        $pelangganBaru->tambahPoin($cetakTotal);

        $cetakNama = $pelangganBaru->getNama();
        $cetakNoHP = $pelangganBaru->getNoHP();
        $cetakLayanan = $layananBaru->jenisLayanan;
        $cetakJarak = $transaksiBaru->jarakTempuh;
        $cetakPembayaran = $pembayaranBaru->metode;
        $cetakVoucher = $voucherBaru->kodeVoucher;
        $cetakPoin = $pelangganBaru->getPoin();

        $tampilkanHasil = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pemesanan Transportasi</title>
    <style>
        :root {
            --primary-color: #00aa5b;
            --primary-hover: #008f4c;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --error-color: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 550px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .form-content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 170, 91, 0.1);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .alert-error {
            background-color: #fee2e2;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #f87171;
            font-weight: 500;
        }

        .receipt {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: #f9fafb;
            border: 1px dashed #9ca3af;
            border-radius: 8px;
        }

        .receipt h3 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .receipt-label {
            color: var(--text-muted);
        }

        .receipt-value {
            font-weight: 600;
            text-align: right;
        }

        .receipt-total {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 2px dashed var(--border-color);
            font-size: 1.25rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Pemesanan Transportasi</h1>
    </div>
    
    <div class="form-content">
        <?php if (!empty($pesanError)): ?>
            <div class="alert-error">
                <?php echo $pesanError; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">Nama Pelanggan</label>
                <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama Anda" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="noHP">Nomor HP</label>
                <input type="number" id="noHP" name="noHP" class="form-control" placeholder="Contoh: 081234567890" value="<?php echo isset($_POST['noHP']) ? htmlspecialchars($_POST['noHP']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="layanan">Jenis Layanan</label>
                <select id="layanan" name="layanan" class="form-control">
                    <option value="goRide Reguler">goRide Reguler</option>
                    <option value="goRide Prioritas">goRide Prioritas</option>
                    <option value="goCar">goCar</option>
                    <option value="goCar XL">goCar XL</option>
                </select>
            </div>

            <div class="form-group">
                <label for="jarak">Jarak Tempuh (KM)</label>
                <input type="number" step="0.1" id="jarak" name="jarak" class="form-control" placeholder="Contoh: 5" value="<?php echo isset($_POST['jarak']) ? htmlspecialchars($_POST['jarak']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="pembayaran">Metode Pembayaran</label>
                <select id="pembayaran" name="pembayaran" class="form-control">
                    <option value="Cash">Cash / Tunai</option>
                    <option value="eWallet">e-Wallet</option>
                    <option value="Transfer Bank">Transfer Bank</option>
                </select>
            </div>

            <div class="form-group">
                <label for="voucher">Kode Voucher (Opsional)</label>
                <select id="voucher" name="voucher" class="form-control">
                    <option value="">-- Tanpa Voucher --</option>
                    <option value="HEMAT10">HEMAT10</option>
                    <option value="HEMAT20">HEMAT20</option>
                    <option value="HEMAT30">HEMAT30</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Hitung Total</button>
        </form>

        <?php if (!empty($tampilkanHasil) && $tampilkanHasil == true): ?>
            <div class="receipt">
                <h3>Detail Transaksi</h3>
                
                <div class="receipt-row">
                    <span class="receipt-label">Nama</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($cetakNama); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">No. HP</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($cetakNoHP); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Layanan</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($cetakLayanan); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Jarak</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($cetakJarak); ?> KM</span>
                </div>
                
                <?php if(!empty($cetakVoucher)): ?>
                <div class="receipt-row">
                    <span class="receipt-label">Voucher</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($cetakVoucher); ?></span>
                </div>
                <?php endif; ?>

                <div class="receipt-row receipt-total">
                    <span class="receipt-label" style="color: var(--primary-color); font-weight: bold;">Total Pembayaran</span>
                    <span class="receipt-value">Rp <?php echo number_format((float)$cetakTotal, 0, ',', '.'); ?></span>
                </div>
                
                <div class="receipt-row" style="margin-top: 10px;">
                    <span class="receipt-label">Poin Member</span>
                    <span class="receipt-value" style="color: #f59e0b;">+<?php echo $cetakPoin; ?></span>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
