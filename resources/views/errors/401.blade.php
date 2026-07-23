<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 — Authentification requise | IBIG FactPro</title>
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
            max-width: 520px;
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
            background: linear-gradient(135deg, #F0C040, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 40px rgba(240,192,64,0.6));
            animation: pulse-glow 3s ease-in-out infinite;
            margin-bottom: 0.5rem;
        }
        @keyframes pulse-glow {
            0%, 100% { filter: drop-shadow(0 0 30px rgba(240,192,64,0.4)); }
            50%       { filter: drop-shadow(0 0 60px rgba(240,192,64,0.9)); }
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            animation: bob 3s ease-in-out infinite;
        }
        @keyframes bob {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%       { transform: translateY(-6px) rotate(-5deg); }
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
            box-shadow: 0 4px 20px rgba(240,192,64,0.4);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px rgba(240,192,64,0.6);
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
            background: linear-gradient(90deg, #F0C040, #0062CC);
            border-radius: 2px;
            margin: 1.5rem auto;
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

    <div class="orb" style="width:350px;height:350px;background:#F0C040;top:-80px;left:-80px;--dur:24s;--tx:50px;--ty:40px;opacity:0.08;"></div>
    <div class="orb" style="width:300px;height:300px;background:#0062CC;bottom:-60px;right:-60px;--dur:16s;--tx:-40px;--ty:-50px;opacity:0.1;"></div>

    <div class="card">
        <div class="brand">IBIG <span>FactPro</span></div>

        <span class="icon">🛡️</span>
        <div class="error-code">401</div>

        <div class="divider"></div>

        <h1>Authentification requise</h1>
        <p class="message">
            Vous devez être connecté pour accéder à cette page.
            Veuillez vous identifier avec votre compte IBIG FactPro.
        </p>

        <div class="btns">
            <a href="/login" class="btn-gold">
                Se connecter →
            </a>
            <a href="/" class="btn-outline">
                ← Retour à l'accueil
            </a>
        </div>
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
