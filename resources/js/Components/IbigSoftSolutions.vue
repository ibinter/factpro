<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const CDN = 'https://ibigsoft.com/uploads/logos/';

const solutions = [
    { cle:'stockflow',      nom:'STOCKFLOW ERP',          mono:'SF', secteur:'Commerce & stock',    desc:"ERP commercial multi-tenant : stock, ventes, achats, POS, paiements et logistique.", url:'https://stockflow.ibigsoft.com',         logo: CDN+'logo_20260527_160550_9dbee6a47a.png', teinte:'#1D6FE0', bientot:false },
    { cle:'gescomxel',      nom:'GESCOMXEL',              mono:'GX', secteur:'Gestion commerciale', desc:"Gestion commerciale, CRM et facturation sur Excel automatisé et application web.",  url:'https://ibigsoft.com/gescomxel.php',     logo: CDN+'logo_20260608_031430_8988fcd86e.png', teinte:'#0F9D58', bientot:false },
    { cle:'scolaby',        nom:'Scolaby',                mono:'SC', secteur:'Éducation',           desc:"Gestion scolaire web et mobile, de la maternelle au supérieur : scolarité, notes, paiements.", url:'https://scolaby.com/',          logo: CDN+'logo_20260620_063019_2519c04616.png', teinte:'#7C3AED', bientot:false },
    { cle:'lokativo',       nom:'Lokativo',               mono:'LK', secteur:'Immobilier',          desc:"Gestion immobilière pour agences, propriétaires et syndics : biens, baux, loyers.", url:'https://lokativo.com/',                 logo: CDN+'logo_20260620_064052_6ab46f00c1.png', teinte:'#E4572E', bientot:false },
    { cle:'ibigfleet360',   nom:'IBIG Fleet 360',         mono:'F3', secteur:'Flotte & parc auto',  desc:"Gestion de flotte : véhicules, entretiens, carburant, coût au km et chauffeurs.",  url:'https://ibigfleet360.com/',             logo: CDN+'logo_20260620_063923_7cd5ae832d.png', teinte:'#0EA5A4', bientot:false },
    { cle:'zelivry',        nom:'Zelivry',                mono:'ZL', secteur:'Livraison',           desc:"Gestion des livraisons en PWA : commandes, clients, livreurs, stock et encaissements.", url:'https://zelivry.com/',             logo: CDN+'logo_20260620_062120_1332f49f8b.png', teinte:'#F4A300', bientot:false },
    { cle:'construiro',     nom:'CONSTRUIRO ERP',         mono:'CO', secteur:'BTP & Construction',  desc:"ERP BTP : projets, chantiers, RH, stock, équipements, finance et comptabilité.",   url:'https://construiro.com/',               logo: CDN+'logo_20260717_160556_664d2719fb.png', teinte:'#F58220', bientot:false },
    { cle:'santarex',       nom:'SANTAREX ERP',           mono:'SX', secteur:'Santé',               desc:"Gestion hospitalière : dossiers patients, pharmacie, laboratoire, urgences, facturation.", url:'https://santarex.ibigsoft.com/',  logo: CDN+'logo_20260717_160519_6fd4162296.png', teinte:'#DC2626', bientot:false },
    { cle:'gestmoney',      nom:'GESTMONEY',              mono:'GM', secteur:'Mobile Money',        desc:"Gestion des réseaux Mobile Money : transactions, float, commissions, KYC et fraude.", url:'https://gestmoney.ibigsoft.com/',    logo: CDN+'logo_20260717_160348_e29eb350a7.png', teinte:'#059669', bientot:false },
    { cle:'agrifrik',       nom:'AGRIFRIK',               mono:'AF', secteur:'Agriculture',         desc:"ERP agricole : cultures, élevage, pisciculture, intrants, exportation et SYSCOHADA.", url:'https://agrifrik.ibigsoft.com/',     logo: CDN+'logo_20260717_160429_6e0dee1b1c.png', teinte:'#65A30D', bientot:false },
    { cle:'anouanze',       nom:'ANOUANZÊ ERP',           mono:'AN', secteur:'ONG & Associations',  desc:"ERP associatif : membres, cotisations, dons, projets et comptabilité SYCEBNL (OHADA).", url:'https://anouanze.ibigsoft.com/',  logo: CDN+'logo_20260717_160233_9ff3c7a8ae.png', teinte:'#B45309', bientot:false },
    { cle:'docpro',         nom:'IBIG DocPro',            mono:'DP', secteur:'Documents',           desc:"Génération intelligente de documents conformes OHADA : contrats, statuts, CV, baux.", url:'https://docpro.ibigsoft.com/',       logo:'https://docpro.ibigsoft.com/logo-icone.svg', teinte:'#4F46E5', bientot:false },
    { cle:'secretis',       nom:'SECRETIS ERP',           mono:'SE', secteur:'Secrétariat',         desc:"Gestion du courrier et du secrétariat : arrivée, départ, parapheur et archivage.", url:'https://secretis.ibigsoft.com/',       logo:'', teinte:'#9333EA', bientot:false },
    { cle:'residencepro',   nom:'IBIG Residence Pro',     mono:'RP', secteur:'Résidences meublées', desc:"Réservations, séjours et facturation pour résidences meublées et appart-hôtels.",  url:'https://residencepro.ibigsoft.com/',   logo:'', teinte:'#DB2777', bientot:true  },
    { cle:'businessplanpro',nom:'IBIG Business Plan Pro', mono:'BP', secteur:'Conseil',             desc:"Rédaction et chiffrage de business plans bancables, prêts pour les financeurs.",   url:'https://businessplanpro.ibigsoft.com/', logo:'', teinte:'#334155', bientot:true  },
];

const track = ref(null);
let animId = null;
let isPaused = false;
let pauseTimer = null;
let scrollX = 0;
const SPEED = 45; // px/sec
let lastTime = null;

function step(ts) {
    if (!track.value) return;
    if (lastTime === null) lastTime = ts;
    const dt = (ts - lastTime) / 1000;
    lastTime = ts;
    if (!isPaused && document.visibilityState === 'visible') {
        scrollX += SPEED * dt;
        const half = track.value.scrollWidth / 2;
        if (half > 0 && scrollX >= half) scrollX -= half;
        track.value.style.transform = `translateX(-${scrollX}px)`;
    }
    animId = requestAnimationFrame(step);
}

function pause(ms) {
    isPaused = true;
    clearTimeout(pauseTimer);
    pauseTimer = setTimeout(() => { isPaused = false; }, ms || 1500);
}

function scroll(dir) {
    scrollX = Math.max(0, scrollX + dir * 284);
    pause(1600);
}

function onImgError(e) {
    const img = e.target;
    const mono = img.getAttribute('data-mono') || '';
    const parent = img.parentNode;
    if (parent) {
        parent.textContent = mono;
        parent.style.background = img.getAttribute('data-tint') || '#0284C7';
        parent.style.color = '#fff';
        parent.style.display = 'grid';
        parent.style.placeItems = 'center';
        parent.style.fontWeight = '700';
        parent.style.fontSize = '16px';
    }
}

onMounted(() => { animId = requestAnimationFrame(step); });
onUnmounted(() => { cancelAnimationFrame(animId); clearTimeout(pauseTimer); });
</script>

<template>
    <section style="background:#F5F7FA;padding:64px 0 56px;overflow:hidden;">
        <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
            <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:flex-end;justify-content:space-between;margin-bottom:32px;">
                <div>
                    <p style="display:inline-flex;align-items:center;gap:8px;font-size:12px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;color:#1D6FE0;margin:0 0 10px;">
                        <span style="width:22px;height:2px;background:#1D6FE0;display:block;"></span>
                        L'écosystème IBIG SOFT
                    </p>
                    <h2 style="font-size:clamp(26px,3.4vw,40px);line-height:1.12;font-weight:700;margin:0;letter-spacing:-.02em;color:#0B1220;">
                        {{ solutions.length }} logiciels de gestion,<br>un seul éditeur.
                    </h2>
                    <p style="margin:12px 0 0;color:#4A5568;max-width:52ch;font-size:15px;">
                        Chaque métier a son outil. Parcourez les solutions IBIG SOFT et ouvrez celle qui correspond à votre activité.
                    </p>
                </div>
                <div style="display:flex;gap:8px;">
                    <button @click="scroll(-1)" type="button" title="Précédent"
                        style="width:42px;height:42px;border-radius:50%;border:1px solid rgba(11,18,32,.1);background:#fff;cursor:pointer;display:grid;place-items:center;transition:.18s;">
                        <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#0B1220"><path d="M15 5 8 12l7 7z"/></svg>
                    </button>
                    <button @click="scroll(1)" type="button" title="Suivant"
                        style="width:42px;height:42px;border-radius:50%;border:1px solid rgba(11,18,32,.1);background:#fff;cursor:pointer;display:grid;place-items:center;transition:.18s;">
                        <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#0B1220"><path d="M9 5l7 7-7 7z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Carrousel -->
        <div style="overflow:hidden;" @mouseenter="pause(99999)" @mouseleave="isPaused=false">
            <div ref="track" style="display:flex;gap:16px;padding:6px 2px 18px;width:max-content;will-change:transform;">
                <!-- Double liste pour boucle infinie -->
                <template v-for="pass in 2" :key="pass">
                    <a v-for="s in solutions" :key="s.cle + '-' + pass"
                       :href="s.url" target="_blank" rel="noopener"
                       :style="`--tint:${s.teinte};flex:0 0 268px;background:#fff;border:1px solid rgba(11,18,32,.10);border-radius:14px;padding:18px;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:12px;position:relative;transition:transform .22s,box-shadow .22s;`"
                       @mouseenter="e => e.currentTarget.style.transform='translateY(-4px)'"
                       @mouseleave="e => e.currentTarget.style.transform=''"
                    >
                        <!-- Barre couleur gauche -->
                        <span :style="`position:absolute;left:0;top:16px;bottom:16px;width:3px;border-radius:0 3px 3px 0;background:${s.teinte};opacity:.85;`"></span>

                        <!-- Badge bientôt -->
                        <span v-if="s.bientot"
                              style="position:absolute;top:14px;right:14px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 8px;border-radius:999px;background:#FEF3C7;color:#92400E;">
                            Bientôt
                        </span>

                        <!-- Logo ou monogramme -->
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span v-if="s.logo"
                                  style="width:52px;height:52px;flex:0 0 52px;border-radius:12px;display:grid;place-items:center;overflow:hidden;background:#fff;border:1px solid rgba(11,18,32,.1);padding:6px;">
                                <img :src="s.logo" :data-mono="s.mono" :data-tint="s.teinte"
                                     @error="onImgError"
                                     style="max-width:100%;max-height:100%;object-fit:contain;display:block;" alt="">
                            </span>
                            <span v-else
                                  :style="`width:52px;height:52px;flex:0 0 52px;border-radius:12px;display:grid;place-items:center;font-weight:700;font-size:16px;color:#fff;background:${s.teinte};`">
                                {{ s.mono }}
                            </span>
                            <span>
                                <span style="font-weight:600;font-size:16px;display:block;line-height:1.2;color:#0B1220;">{{ s.nom }}</span>
                                <span style="font-size:11.5px;color:#4A5568;text-transform:uppercase;letter-spacing:.08em;margin-top:3px;display:block;">{{ s.secteur }}</span>
                            </span>
                        </div>

                        <p style="font-size:13.5px;color:#4A5568;margin:0;flex:1;line-height:1.5;">{{ s.desc }}</p>

                        <span :style="`display:flex;align-items:center;justify-content:space-between;font-size:12.5px;font-weight:600;color:${s.teinte};`">
                            <span>Découvrir</span>
                            <span style="transition:transform .2s;">→</span>
                        </span>
                    </a>
                </template>
            </div>
        </div>

        <div style="max-width:1200px;margin:0 auto;padding:0 20px;">
            <p style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;margin-top:6px;font-size:13px;color:#4A5568;">
                Besoin d'aide pour choisir ?
                <a href="mailto:support@ibigsoft.com" style="color:#1D6FE0;font-weight:600;text-decoration:none;">support@ibigsoft.com</a>
                ou WhatsApp
                <a href="https://wa.me/2250778882592" target="_blank" rel="noopener" style="color:#1D6FE0;font-weight:600;text-decoration:none;">+225 07 78 88 25 92</a>
            </p>
        </div>
    </section>
</template>
