<script setup>
const year = new Date().getFullYear();
</script>

<template>
    <footer style="background:#0a0f1e;color:rgba(255,255,255,.65)">
        <div class="mx-auto max-w-7xl px-6 py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-6">

                <!-- Colonne 1 — Marque -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <img src="/logo_icon.svg" alt="IBIG FactPro" class="h-8 w-auto" />
                        <span class="font-extrabold text-white text-sm">IBIG FactPro</span>
                    </div>
                    <p class="text-xs leading-relaxed mb-5">
                        La facturation professionnelle pour les entrepreneurs et PME d'Afrique et du monde. Conforme OHADA, multi-devises, Mobile Money, QR anti-falsification.
                    </p>
                    <!-- Réseaux sociaux -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <a href="https://www.facebook.com/ibigsoft" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="Facebook">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://www.linkedin.com/company/ibigsoft/" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="LinkedIn">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://www.youtube.com/@IBIGSOFT" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="YouTube">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M21.6 8.2a2.5 2.5 0 0 0-1.7-1.8C18.3 6 12 6 12 6s-6.3 0-7.9.4A2.5 2.5 0 0 0 2.4 8.2 26 26 0 0 0 2 12c0 1.3.1 2.6.4 3.8a2.5 2.5 0 0 0 1.7 1.8C5.7 18 12 18 12 18s6.3 0 7.9-.4a2.5 2.5 0 0 0 1.7-1.8c.3-1.2.4-2.5.4-3.8s-.1-2.6-.4-3.8ZM10 15V9l5.2 3L10 15Z"/></svg>
                        </a>
                        <a href="https://www.tiktok.com/@ibigsoft" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="TikTok">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M14 3h3a5 5 0 0 0 4 4v3a8 8 0 0 1-4-1.2V15a6 6 0 1 1-6-6c.3 0 .7 0 1 .1v3.2A2.8 2.8 0 1 0 14 15V3Z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/ibigsoft/" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="Instagram">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm0 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM17 6.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2ZM7.5 3h9A4.5 4.5 0 0 1 21 7.5v9a4.5 4.5 0 0 1-4.5 4.5h-9A4.5 4.5 0 0 1 3 16.5v-9A4.5 4.5 0 0 1 7.5 3Zm0 2A2.5 2.5 0 0 0 5 7.5v9A2.5 2.5 0 0 0 7.5 19h9a2.5 2.5 0 0 0 2.5-2.5v-9A2.5 2.5 0 0 0 16.5 5h-9Z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/groups/1655325562202049" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="Groupe Facebook">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M8.5 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5 0a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM8.5 12.5c-3 0-5.5 1.6-5.5 3.6V19h11v-2.9c0-2-2.5-3.6-5.5-3.6Zm7.5.5c-.7 0-1.3.1-1.9.3 1 .9 1.6 2 1.6 3.3V19h5v-2.4c0-1.9-2.2-3.1-4.7-3.1Z"/></svg>
                        </a>
                        <a href="https://whatsapp.com/channel/0029VbD8TIr9xVJmniJ8m81w" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-white/20"
                           style="background:rgba(255,255,255,.08)" aria-label="Chaîne WhatsApp">
                            <svg class="h-4 w-4 fill-white/60" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>
                    <div class="text-xs space-y-1">
                        <div><a href="mailto:factpro@ibigsoft.com" class="hover:text-white transition">📧 factpro@ibigsoft.com</a></div>
                        <div><a href="tel:+2252722276014" class="hover:text-white transition">📞 +225 27 22 27 60 14</a></div>
                        <div><a href="tel:+2250555059901" class="hover:text-white transition">📱 +225 05 55 05 99 01</a></div>
                        <div><a href="https://wa.me/2250778882592" target="_blank" rel="noopener" class="hover:text-white transition">💬 WhatsApp +225 07 78 88 25 92</a></div>
                    </div>
                </div>

                <!-- Colonne 2 — Produit -->
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-white">Produit</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/#fonctionnalites" class="hover:text-white transition">Fonctionnalités</a></li>
                        <li><a href="/pricing" class="hover:text-white transition">Tarifs</a></li>
                        <li><a href="/demo-login" class="hover:text-white transition">Démonstration</a></li>
                        <li><a href="/register" class="hover:text-white transition font-semibold" style="color:#F0C040">Essai gratuit 7 jours</a></li>
                        <li><a href="/login" class="hover:text-white transition">Se connecter</a></li>
                    </ul>
                </div>

                <!-- Colonne 3 — Ressources -->
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-white">Ressources</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/blog" class="hover:text-white transition">Blog</a></li>
                        <li><a href="/roadmap" class="hover:text-white transition">Roadmap</a></li>
                        <li><a href="/help" class="hover:text-white transition">Centre d'aide</a></li>
                        <li><a href="/#faq" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="/help/guide" class="hover:text-white transition">Guide utilisateur</a></li>
                        <li><a href="/changelog" class="hover:text-white transition">Nouveautés</a></li>
                        <li><a href="/status" class="hover:text-white transition">Statut des services</a></li>
                    </ul>
                </div>

                <!-- Colonne 4 — IBIG Soft -->
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-white">IBIG Soft</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/temoignages" class="hover:text-white transition">Témoignages clients</a></li>
                        <li><a href="/a-propos" class="hover:text-white transition">À propos d'IBIG Soft</a></li>
                        <li><a href="/contact" class="hover:text-white transition">Contact</a></li>
                        <li><a href="/partenaires" class="hover:text-white transition">Programme partenaires</a></li>
                        <li><a href="https://ibigpartners.com/" target="_blank" rel="noopener" class="hover:text-white transition">Programme IBIG PARTNERS</a></li>
                        <li><a href="https://ibigsoft.com" target="_blank" rel="noopener" class="hover:text-white transition">Autres logiciels</a></li>
                        <li><a href="mailto:support@ibigsoft.com" class="hover:text-white transition">Support technique</a></li>
                    </ul>
                </div>

                <!-- Colonne 5 — Légal -->
                <div>
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-white">Légal</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/legal/mentions" class="hover:text-white transition">Mentions légales</a></li>
                        <li><a href="/legal/cgu" class="hover:text-white transition">CGU</a></li>
                        <li><a href="/legal/confidentialite" class="hover:text-white transition">Confidentialité</a></li>
                        <li><a href="/legal/cookies" class="hover:text-white transition">Cookies</a></li>
                        <li><a href="/legal/pi" class="hover:text-white transition">Propriété intellectuelle</a></li>
                        <li><a href="/legal/resiliation" class="hover:text-white transition">Résiliation</a></li>
                        <li><a href="/securite-confiance" class="hover:text-white transition">Sécurité &amp; Confiance</a></li>
                    </ul>
                </div>
            </div>

            <!-- IBIG PARTNERS CTA -->
            <div class="mt-12 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4"
                 style="background:rgba(240,192,64,.08);border:1px solid rgba(240,192,64,.2)">
                <div>
                    <div class="text-sm font-bold text-white">🤝 Devenez partenaire IBIG</div>
                    <div class="text-xs mt-0.5">Recommandez FactPro et touchez jusqu'à 20 % de commission sur 3 niveaux. Inscription 100 % gratuite.</div>
                </div>
                <a href="https://ibigpartners.com/" target="_blank" rel="noopener"
                   class="flex-shrink-0 rounded-xl px-5 py-2.5 text-sm font-bold transition hover:scale-105"
                   style="background:#F0C040;color:#001d3d">
                    Rejoindre IBIG Partners →
                </a>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t" style="border-color:rgba(255,255,255,.07)">
            <div class="mx-auto max-w-7xl px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs">
                    <div class="space-y-1">
                        <div class="text-white/50">© {{ year }} IBIG FactPro. Tous droits réservés.</div>
                        <div class="text-white/30">Logiciel conçu, édité et exploité par IBIG Soft, une marque de IBIG SARL – Intermark Business International Group.</div>
                    </div>
                    <div class="flex flex-wrap gap-4 text-white/40">
                        <a href="/legal/mentions" class="hover:text-white transition">Mentions légales</a>
                        <a href="/legal/cgu" class="hover:text-white transition">CGU</a>
                        <a href="/legal/confidentialite" class="hover:text-white transition">Confidentialité</a>
                        <a href="/legal/cookies" class="hover:text-white transition">Cookies</a>
                        <a href="https://ibigsoft.com" target="_blank" rel="noopener" class="hover:text-white transition">ibigsoft.com ↗</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
