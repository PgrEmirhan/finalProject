<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['files'])) {
    $files = $_POST['files'];

    $zip = new ZipArchive();
    $zipName = 'arsiv_' . time() . '.zip';
    $zipPath = 'uploads/' . $zipName;

    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        foreach ($files as $file) {
            $filePath = realpath($file);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, basename($filePath));
            }
        }
        $zip->close();

        // İndirme başlat
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);

        // Geçici ZIP silinsin istiyorsan:
        unlink($zipPath);
        exit;
    } else {
        echo "ZIP oluşturulamadı.";
    }
} else {
    echo "Hiç dosya seçilmedi.";
}
?>