<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const GA4_ID   = page.props.analytics?.ga4_id  || 'G-XXXXXXXXXX';
const PIXEL_ID = page.props.analytics?.pixel_id || '000000000000000';

function loadGA4() {
    if (window.__ga4Loaded) return;
    window.__ga4Loaded = true;
    const s = document.createElement('script');
    s.src = `https://www.googletagmanager.com/gtag/js?id=${GA4_ID}`;
    s.async = true;
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function() { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', GA4_ID, { anonymize_ip: true });
}

function loadMetaPixel() {
    if (window.__metaPixelLoaded) return;
    window.__metaPixelLoaded = true;
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
    window.fbq('init', PIXEL_ID);
    window.fbq('track', 'PageView');
}

function checkConsent() {
    try {
        const prefs = JSON.parse(localStorage.getItem('factpro_cookies') || '{}');
        if (prefs.statistics) loadGA4();
        if (prefs.marketing) loadMetaPixel();
    } catch {}
}

function onCookieUpdate() { checkConsent(); }

onMounted(() => {
    checkConsent();
    window.addEventListener('cookie:updated', onCookieUpdate);
});

onUnmounted(() => {
    window.removeEventListener('cookie:updated', onCookieUpdate);
});
</script>

<template><div style="display:none"></div></template>
