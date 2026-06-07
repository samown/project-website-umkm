<?php
// src/pages/public/contact.php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/config/database.php';
require_once dirname(__DIR__, 3) . '/src/includes/helpers.php';

$appName = $_ENV['APP_NAME'] ?? 'Toko Rini';
$address = $_ENV['STORE_ADDRESS'] ?? 'Surakarta, Jawa Tengah';
$mapsUrl = $_ENV['MAPS_EMBED_URL'] ?? 'https://maps.google.com/maps?q=-7.5755,110.8243&z=15&output=embed';
$waNum   = $_ENV['WHATSAPP_NUMBER'] ?? '6285249296758';

$pageTitle = 'Kontak';
require_once dirname(__DIR__, 3) . '/src/includes/header_public.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>📞 Hubungi Kami</h1>
        <p>Kami siap membantu Anda setiap hari</p>
    </div>
</div>

<div class="container contact-page">
    <div class="contact-grid">
        <div class="contact-info">
            <h2>Informasi Kontak</h2>

            <div class="contact-item">
                <span class="contact-icon">📍</span>
                <div>
                    <strong>Alamat</strong>
                    <p><?= e($address) ?></p>
                </div>
            </div>

            <div class="contact-item">
                <span class="contact-icon">⏰</span>
                <div>
                    <strong>Jam Operasional</strong>
                    <p>Senin – Sabtu: 07.00 – 20.00 WIB<br>Minggu: 08.00 – 17.00 WIB</p>
                </div>
            </div>

            <div class="contact-item">
                <span class="contact-icon">📱</span>
                <div>
                    <strong>WhatsApp</strong>
                    <p>+<?= e($waNum) ?></p>
                </div>
            </div>

            <div class="wa-block">
                <h3>Pesan Langsung via WhatsApp</h3>
                <p>Klik tombol di bawah untuk langsung chat dengan kami. Kami respon cepat!</p>
                <a href="<?= e(waUrl('Halo Toko Rini! Saya ingin bertanya.')) ?>"
                   class="btn-wa-xl" target="_blank" rel="noopener noreferrer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Chat di WhatsApp Sekarang
                </a>

                <div class="wa-templates">
                    <p>Pesan cepat:</p>
                    <a href="<?= e(waUrl('Halo Toko Rini, saya ingin cek stok produk.')) ?>"
                       class="wa-template" target="_blank">Cek Stok Produk</a>
                    <a href="<?= e(waUrl('Halo Toko Rini, saya ingin memesan produk.')) ?>"
                       class="wa-template" target="_blank">Pesan Produk</a>
                    <a href="<?= e(waUrl('Halo Toko Rini, saya ingin tanya harga.')) ?>"
                       class="wa-template" target="_blank">Tanya Harga</a>
                </div>
            </div>
        </div>

        <div class="contact-map">
            <h2>Lokasi Toko</h2>
            <iframe src="<?= e($mapsUrl) ?>"
                    width="100%" height="400" style="border:0; border-radius:12px;"
                    allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            <p class="map-note">📍 <?= e($address) ?></p>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 3) . '/src/includes/footer_public.php'; ?>
