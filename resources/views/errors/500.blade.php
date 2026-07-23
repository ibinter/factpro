<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Erreur serveur | IBIG FactPro</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #001d3d;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        .stars { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.7);
            animation: twinkle var(--dur, 4s) ease-in-out infinite;
            animation-delay: var(--delay, 0s);
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0.1; transform: scale(1); }
            50%       { opacity: 0.8; transform: scale(1.4); }
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            animation: drift var(--dur, 20s) ease-in-out infinite alternate;
        }
        @keyframes drift {
            from { transform: translate(0, 0); }
            to   { transform: translate(var(--tx, 40px), var(--ty, 30px)); }
        }

        .card {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 3rem 2.5rem;
            max-width: 560px;
            width: 90%;
        }

        .brand {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 2.5rem;
        }
        .brand span { color: #F0C040; }

        .error-code {
            font-size: clamp(6rem, 20vw, 10rem);
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 40px rgba(99,102,241,0.6));
            animation: pulse-glow 3s ease-in-out infinite;
            margin-bottom: 0.5rem;
        }
        @keyframes pulse-glow {
            0%, 100% { filter: drop-shadow(0 0 30px rgba(99,102,241,0.4)); }
            50%       { filter: drop-shadow(0 0 60px rgba(99,102,241,0.9)); }
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            animation: spin-wobble 5s ease-in-out infinite;
        }
        @keyframes spin-wobble {
            0%   { transform: rotate(0deg); }
            25%  { transform: rotate(-10deg); }
            50%  { transform: rotate(10deg); }
            75%  { transform: rotate(-5deg); }
            100% { transform: rotate(0deg); }
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.75rem;
        }

        p.message {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .btn-gold {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1.4rem;
            background: #F0C040;
            color: #001d3d;
            font-weight: 700;
            font-size: 0.875rem;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 20px rgba(240,192,64,0.35);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px rgba(240,192,64,0.55);
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1.4rem;
            background: transparent;
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 8px;
            text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.35);
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }
        .btn-outline:hover {
            border-color: rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.06);
        }

        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #6366f1, #F0C040);
            border-radius: 2px;
            margin: 1.5rem auto;
        }

        .support-line {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.35);
        }
        .support-line a {
            color: #F0C040;
            text-decoration: none;
        }
        .support-line a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .card { padding: 2rem 1.5rem; }
            .btns { flex-direction: column; align-items: center; }
            .btn-gold, .btn-outline { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="stars" id="stars"></div>

    <div class="orb" style="width:400px;height:400px;background:#6366f1;top:-100px;right:-80px;--dur:28s;--tx:-60px;--ty:50px;opacity:0.1;"></div>
    <div class="orb" style="width:320px;height:320px;background:#002D5B;bottom:-70px;left:-70px;--dur:20s;--tx:50px;--ty:-40px;opacity:0.15;"></div>

    <div class="card">
        <div class="brand">IBIG <span>FactPro</span></div>

        <span class="icon">⚙️</span>
        <div class="error-code">500</div>

        <div class="divider"></div>

        <h1>Erreur serveur</h1>
        <p class="message">
            Une erreur inattendue s'est produite. Notre équipe technique a été notifiée.
            Veuillez réessayer dans quelques instants.

            @php
                if (!app()->environment('production')  && isset($exception)) {
                    echo '<br><br><small style="font-size:0.75rem;color:rgba(255,100,100,0.7);">' . e($exception->getMessage()) . '</small>';
                }
            @endphp
        </p>

        <div class="btns">
            <button onclick="window.history.back()" class="btn-gold">
                ↩ Réessayer
            </button>
            <a href="/" class="btn-outline">
                ← Retour à l'accueil
            </a>
        </div>

        <p class="support-line">
            Si le problème persiste, contactez-nous à
            <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>
        </p>
    </div>

    <script>
        const container = document.getElementById('stars');
        for (let i = 0; i < 80; i++) {
            const s = document.createElement('div');
            s.className = 'star';
            const size = Math.random() * 2.5 + 0.5;
            s.style.cssText = `
                width:${size}px; height:${size}px;
                top:${Math.random()*100}%; left:${Math.random()*100}%;
                --dur:${(Math.random()*4+2).toFixed(1)}s;
                --delay:${(Math.random()*4).toFixed(1)}s;
            `;
            container.appendChild(s);
        }
    </script>
</body>
</html>
