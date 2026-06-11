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
