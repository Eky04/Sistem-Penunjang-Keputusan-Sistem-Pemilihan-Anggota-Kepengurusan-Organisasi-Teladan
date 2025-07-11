<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config/database.php');
include('fpdf.php');

$pdf = new FPDF('P');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Data Anggota Kepengurusan Organisasi Teladan Permikomnas', 0, 1, 'C');

// Ambil daftar kriteria
$kriteria = [];
$qk = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY nama_kriteria ASC");
while ($kr = mysqli_fetch_assoc($qk)) {
    $kriteria[] = $kr;
}

// Hitung total lebar tabel
$total_width = 10 + 20 + 40 + 30 + 45;

// Header tabel
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX( ($pdf->GetPageWidth() - $total_width) / 2 );
$pdf->Cell(10, 12, 'No', 1, 0, 'C');
$pdf->Cell(20, 12, 'Ranking', 1, 0, 'C');
$pdf->Cell(40, 12, 'Nama', 1, 0, 'C');
$pdf->Cell(30, 12, 'Jabatan', 1, 0, 'C');
$pdf->Cell(45, 12, 'Nilai Preferensi', 1, 1, 'C');

// Query ambil data dari hasil perhitungan SAW (ranking), join mahasiswa dan jabatan
$sql = mysqli_query($conn, "SELECT h.ranking, h.nilai_preferensi, m.*, j.nama_jabatan FROM hasil_perhitungan h JOIN mahasiswa m ON h.id_mahasiswa = m.id_mahasiswa LEFT JOIN jabatan j ON m.jabatan = j.id ORDER BY h.ranking ASC");
$no = 1;
if (mysqli_num_rows($sql) > 0) {
    $pdf->SetFont('Arial', '', 8);
    while ($row = mysqli_fetch_array($sql)) {
        $pdf->SetX( ($pdf->GetPageWidth() - $total_width) / 2 );
        $pdf->Cell(10, 6, $no++ . ".", 1, 0, 'C');
        $pdf->Cell(20, 6, $row["ranking"], 1, 0, 'C');
        $pdf->Cell(40, 6, $row["nama"], 1, 0, 'C');
        $pdf->Cell(30, 6, $row["jabatan"] ?? '-', 1, 0, 'C');
        $pdf->Cell(45, 6, number_format($row["nilai_preferensi"], 6), 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->SetX( ($pdf->GetPageWidth() - $total_width) / 2 );
    $pdf->Cell($total_width, 6, 'Data tidak ditemukan.', 1, 1, 'C');
}

$pdf->Output('I', 'Laporan_Anggota_Kepengurusan.pdf');
?>
