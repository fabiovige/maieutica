// Maieutica PWA Service Worker
// Minimo necessario para habilitar "Instalar como App" no Chrome/Android
// NAO cacheia nada — o sistema clinico precisa de dados em tempo real

self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

// Handler fetch obrigatorio para o Chrome considerar a PWA instalavel
// Apenas repassa todas as requisicoes para a rede
self.addEventListener('fetch', function (event) {
    event.respondWith(fetch(event.request));
});
