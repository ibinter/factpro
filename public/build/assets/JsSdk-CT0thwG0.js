import{o as l,e as g,w as p,b as t,c as n,F as d,r as c,n as m,t as a,j as o,E as i,k as x}from"./app-zI44_bhA.js";import{_ as b}from"./AuthenticatedLayout-DnM2qfyj.js";const y={class:"max-w-6xl mx-auto px-4 py-8"},f={class:"flex gap-6"},k={class:"hidden md:flex flex-col gap-1 w-44 shrink-0"},v=["onClick"],w={class:"flex-1 min-w-0 space-y-6"},h={class:"mt-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 space-y-3"},P={class:"w-full text-sm"},C={class:"divide-y divide-gray-100 dark:divide-gray-700"},E={class:"py-2 pr-4"},S={class:"text-indigo-600 dark:text-indigo-400"},F={class:"py-2 pr-4 text-gray-500"},_={class:"py-2 pr-4 text-gray-500 text-xs"},A={class:"py-2"},T={class:"grid grid-cols-1 md:grid-cols-3 gap-4"},j={class:"text-sm font-bold text-indigo-600 dark:text-indigo-400"},D={class:"ml-2 text-xs text-gray-400"},B={class:"text-sm text-gray-600 dark:text-gray-400 mt-1"},q="npm install @ibigsoft/factpro-sdk",I=`import { FactProClient } from '@ibigsoft/factpro-sdk';

const client = new FactProClient({
    apiKey: 'votre-cle-api-sanctum',
    // baseUrl: 'https://app.factpro.ibigsoft.com', // par défaut
});

// Tester la connexion
const me = await client.me();
console.log('Connecté :', me.data.user.email);`,M=`// Lister les documents
const docs = await client.documents.list({
    type: 'invoice',
    status: 'final',
    page: 1,
});

// Créer une facture
const invoice = await client.documents.create({
    type: 'invoice',
    customer_id: 42,
    issue_date: '2026-01-15',
    currency: 'XOF',
    items: [{
        description: 'Prestation de conseil',
        quantity: 5,
        unit_price: 30000,
        tax_rate: 18,
    }],
});

// Finaliser et télécharger le PDF
await client.documents.finalize(invoice.data.uuid);
const pdfBytes = await client.documents.downloadPdf(invoice.data.uuid);

// En navigateur :
const blob = new Blob([pdfBytes], { type: 'application/pdf' });
const url = URL.createObjectURL(blob);
window.open(url);`,R=`// Lister les clients
const list = await client.customers.list({ search: 'acme' });

// Créer un client
const customer = await client.customers.create({
    name:  'ACME Corp',
    email: 'contact@acme.com',
    phone: '+225 07 00 00 00 00',
    type:  'company',
});

// Récupérer les factures d'un client
const invoices = await client.customers.getInvoices(customer.data.id);

// Consulter le solde en attente
const balance = await client.customers.getBalance(customer.data.id);
console.log('Solde :', balance.data.pending_amount, 'XOF');`,L=`// Créer un produit
const product = await client.products.create({
    name:       'Abonnement mensuel',
    unit_price: 25000,
    unit:       'mois',
    tax_rate:   18,
});

// Ajuster le stock
await client.products.adjustStock(product.data.id, {
    quantity: -5,
    reason: 'Vente comptoir',
});`,O=`// Factures en retard
const overdue = await client.invoices.list({ overdue: true });

// Enregistrer un paiement
await client.invoices.registerPayment(uuid, {
    amount: 150000,
    method: 'mobile_money',
    date:   '2026-01-20',
});

// Envoyer une relance
await client.invoices.sendReminder(uuid);

// Marquer comme payée
await client.invoices.markAsPaid(uuid);`,U=`import { FactProClient, AuthError, ValidationError, FactProError } from '@ibigsoft/factpro-sdk';

try {
    await client.documents.create({});
} catch (e) {
    if (e instanceof AuthError) {
        // HTTP 401 — token invalide ou expiré
        console.error('Clé API invalide');
    } else if (e instanceof ValidationError) {
        // HTTP 422 — données invalides
        for (const [field, msgs] of Object.entries(e.errors)) {
            console.error(\`\${field}: \${msgs.join(', ')}\`);
        }
    } else if (e instanceof FactProError) {
        // Toute autre erreur API
        console.error(\`Erreur \${e.status}: \${e.message}\`);
    }
}`,H={__name:"JsSdk",setup(V){const r=x("install"),u=[{id:"install",label:"Installation"},{id:"quickstart",label:"Quick Start"},{id:"documents",label:"Documents"},{id:"customers",label:"Clients"},{id:"products",label:"Produits"},{id:"invoices",label:"Factures"},{id:"errors",label:"Erreurs"}];return($,e)=>(l(),g(b,{title:"SDK JavaScript"},{default:p(()=>[t("div",y,[e[20]||(e[20]=t("div",{class:"mb-8"},[t("div",{class:"flex items-center gap-3 mb-3"},[t("h1",{class:"text-3xl font-bold text-gray-900 dark:text-white"},"SDK JavaScript"),t("span",{class:"px-2 py-0.5 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"},"ESM"),t("span",{class:"px-2 py-0.5 text-xs font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"},"Node 18+"),t("span",{class:"px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"},"Browser")]),t("p",{class:"text-gray-600 dark:text-gray-400"}," SDK JavaScript officiel pour l'API FactPro. Vanilla ES2020+, aucune dépendance, compatible Node.js 18+ et navigateurs modernes. ")],-1)),t("div",f,[t("nav",k,[(l(),n(d,null,c(u,s=>t("button",{key:s.id,onClick:J=>r.value=s.id,class:m(["text-left px-3 py-2 rounded-lg text-sm font-medium transition",r.value===s.id?"bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300":"text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"])},a(s.label),11,v)),64))]),t("div",w,[o(t("section",null,[e[3]||(e[3]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-4"},"Installation",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5"},[e[0]||(e[0]=t("p",{class:"text-xs text-gray-400 mb-2 font-mono"},"npm",-1)),t("pre",{class:"text-green-400 font-mono text-sm"},a(q))]),t("div",h,[e[2]||(e[2]=t("h3",{class:"font-semibold text-gray-900 dark:text-white"},"Configuration",-1)),t("table",P,[e[1]||(e[1]=t("thead",null,[t("tr",{class:"text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700"},[t("th",{class:"pb-2 pr-4"},"Option"),t("th",{class:"pb-2 pr-4"},"Type"),t("th",{class:"pb-2 pr-4"},"Défaut"),t("th",{class:"pb-2"},"Description")])],-1)),t("tbody",C,[(l(),n(d,null,c([{name:"apiKey",type:"string",default:"—",desc:"Token Sanctum (requis)"},{name:"baseUrl",type:"string",default:"app.factpro.ibigsoft.com",desc:"URL de votre instance"},{name:"timeout",type:"number",default:"10000",desc:"Timeout en ms"},{name:"retries",type:"number",default:"3",desc:"Retries sur 429/503"}],s=>t("tr",{key:s.name,class:"text-gray-700 dark:text-gray-300"},[t("td",E,[t("code",S,a(s.name),1)]),t("td",F,a(s.type),1),t("td",_,a(s.default),1),t("td",A,a(s.desc),1)])),64))])])])],512),[[i,r.value==="install"]]),o(t("section",null,[e[5]||(e[5]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-4"},"Quick Start",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5"},[e[4]||(e[4]=t("p",{class:"text-xs text-gray-400 mb-2 font-mono"},"JavaScript (ESM)",-1)),t("pre",{class:"text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap"},a(I))])],512),[[i,r.value==="quickstart"]]),o(t("section",null,[e[6]||(e[6]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-1"},"client.documents",-1)),e[7]||(e[7]=t("p",{class:"text-sm text-gray-500 dark:text-gray-400 mb-4"},"Devis, factures, avoirs, bons de livraison",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4"},[t("pre",{class:"text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap"},a(M))]),e[8]||(e[8]=t("div",{class:"rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4"},[t("p",{class:"text-sm font-semibold text-gray-900 dark:text-white mb-2"},"Méthodes disponibles"),t("ul",{class:"text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono"},[t("li",null,"list({ page, type, status, customer_id, search })"),t("li",null,"get(uuid)"),t("li",null,"create(data)"),t("li",null,"update(uuid, data)"),t("li",null,"finalize(uuid)"),t("li",null,"send(uuid, { email, message })"),t("li",null,"downloadPdf(uuid) → Uint8Array")])],-1))],512),[[i,r.value==="documents"]]),o(t("section",null,[e[9]||(e[9]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-1"},"client.customers",-1)),e[10]||(e[10]=t("p",{class:"text-sm text-gray-500 dark:text-gray-400 mb-4"},"Gestion des clients et prospects",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4"},[t("pre",{class:"text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap"},a(R))]),e[11]||(e[11]=t("div",{class:"rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4"},[t("p",{class:"text-sm font-semibold text-gray-900 dark:text-white mb-2"},"Méthodes disponibles"),t("ul",{class:"text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono"},[t("li",null,"list({ page, search, type })"),t("li",null,"get(id)"),t("li",null,"create(data)"),t("li",null,"update(id, data)"),t("li",null,"getInvoices(id)"),t("li",null,"getBalance(id)")])],-1))],512),[[i,r.value==="customers"]]),o(t("section",null,[e[12]||(e[12]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-1"},"client.products",-1)),e[13]||(e[13]=t("p",{class:"text-sm text-gray-500 dark:text-gray-400 mb-4"},"Catalogue produits et services",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4"},[t("pre",{class:"text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap"},a(L))]),e[14]||(e[14]=t("div",{class:"rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4"},[t("p",{class:"text-sm font-semibold text-gray-900 dark:text-white mb-2"},"Méthodes disponibles"),t("ul",{class:"text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono"},[t("li",null,"list({ page, search, category })"),t("li",null,"get(id)"),t("li",null,"create(data)"),t("li",null,"update(id, data)"),t("li",null,"adjustStock(id, { quantity, reason })")])],-1))],512),[[i,r.value==="products"]]),o(t("section",null,[e[15]||(e[15]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-1"},"client.invoices",-1)),e[16]||(e[16]=t("p",{class:"text-sm text-gray-500 dark:text-gray-400 mb-4"},"Raccourci spécialisé pour la gestion des factures",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4"},[t("pre",{class:"text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap"},a(O))]),e[17]||(e[17]=t("div",{class:"rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4"},[t("p",{class:"text-sm font-semibold text-gray-900 dark:text-white mb-2"},"Méthodes disponibles"),t("ul",{class:"text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono"},[t("li",null,"list({ page, status, overdue })"),t("li",null,"get(uuid)"),t("li",null,"registerPayment(uuid, { amount, method, date })"),t("li",null,"sendReminder(uuid)"),t("li",null,"markAsPaid(uuid)")])],-1))],512),[[i,r.value==="invoices"]]),o(t("section",null,[e[18]||(e[18]=t("h2",{class:"text-xl font-semibold text-gray-900 dark:text-white mb-4"},"Gestion des erreurs",-1)),t("div",{class:"rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4"},[t("pre",{class:"text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap"},a(U))]),t("div",T,[(l(),n(d,null,c([{name:"FactProError",status:"4xx/5xx",desc:"Erreur de base. Propriétés : message, status, code."},{name:"AuthError",status:"401",desc:"Token invalide ou expiré. Extends FactProError."},{name:"ValidationError",status:"422",desc:"Données invalides. Propriété .errors par champ."}],s=>t("div",{key:s.name,class:"rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4"},[t("code",j,a(s.name),1),t("span",D,"HTTP "+a(s.status),1),t("p",B,a(s.desc),1)])),64))])],512),[[i,r.value==="errors"]]),e[19]||(e[19]=t("div",{class:"pt-4 flex gap-3 flex-wrap"},[t("a",{href:"https://www.npmjs.com/package/@ibigsoft/factpro-sdk",target:"_blank",class:"inline-flex items-center px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition text-sm"}," npm package "),t("a",{href:"/api/openapi.json",download:"factpro-openapi.json",class:"inline-flex items-center px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg transition text-sm"}," Spec OpenAPI ")],-1))])])])]),_:1}))}};export{H as default};
