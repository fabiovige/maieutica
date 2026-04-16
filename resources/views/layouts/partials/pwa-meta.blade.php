{{-- PWA Meta Tags --}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#AD6E9B">

{{-- Apple iOS PWA Support --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Maieutica">
<link rel="apple-touch-icon" href="/images/icons/icon-152x152.png">

{{-- Microsoft Tile --}}
<meta name="msapplication-TileImage" content="/images/icons/icon-144x144.png">
<meta name="msapplication-TileColor" content="#AD6E9B">

{{-- Favicon --}}
<link rel="icon" type="image/png" sizes="96x96" href="/images/icons/icon-96x96.png">
<link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-72x72.png">

{{-- Service Worker Registration --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>
