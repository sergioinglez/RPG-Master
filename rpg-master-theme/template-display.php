<?php
/**
 * Template Name: 📺 RPG - Tela de Exibição
 * 
 * Interface de exibição pública para TV/projetor na mesa de RPG.
 * Mostra: mapa atual, tokens dos personagens, NPC em interação, 
 * monstros em combate, título da cena.
 * Atualiza via polling da REST API.
 */

// Display não requer login - é público para a TV
$poll_interval = get_option( 'rmt_display_poll_interval', 3000 );
$display_theme = get_option( 'rmt_display_theme', 'dark' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPG Display - Mesa Virtual</title>
    <?php wp_head(); ?>
    <style>
        /* Full-screen display styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0a15;
            color: #e0e0e0;
            font-family: 'Nunito', sans-serif;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            cursor: none;
        }

        /* === SCENE CONTAINER === */
        .display-container {
            width: 100vw;
            height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* === TOP BAR === */
        .display-topbar {
            background: linear-gradient(90deg, rgba(123,45,142,0.9), rgba(15,52,96,0.9));
            padding: 10px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
            box-shadow: 0 2px 15px rgba(0,0,0,0.5);
        }

        .display-scene-title {
            font-family: 'Cinzel', serif;
            font-size: 1.8em;
            color: #ffd700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .display-scene-type {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .type-exploration { background: #0073aa; }
        .type-combat { background: #dc3232; animation: pulse-red 2s infinite; }
        .type-social { background: #46b450; }
        .type-puzzle { background: #826eb4; }
        .type-rest { background: #00a0d2; }
        .type-shop { background: #dba617; color: #000; }
        .type-travel { background: #ca4a1f; }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 5px #dc3232; }
            50% { box-shadow: 0 0 20px #dc3232, 0 0 40px #dc3232; }
        }

        /* === MAIN AREA === */
        .display-main {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        /* === MAP === */
        .display-map {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .display-map img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* === CHARACTER TOKENS === */
        .display-token {
            position: absolute;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 3px solid #ffd700;
            background-size: cover;
            background-position: center;
            background-color: #333;
            box-shadow: 0 0 15px rgba(255,215,0,0.5);
            transition: left 0.5s ease, top 0.5s ease;
            z-index: 5;
        }

        .display-token-name {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            font-weight: 700;
            color: #ffd700;
            white-space: nowrap;
            text-shadow: 1px 1px 2px #000;
            background: rgba(0,0,0,0.7);
            padding: 1px 6px;
            border-radius: 4px;
        }

        /* === SCENE IMAGE (no map) === */
        .display-scene-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center;
        }

        /* === NPC INTERACTION === */
        .display-npc {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, rgba(15,52,96,0.5) 0%, rgba(10,10,21,0.9) 100%);
        }

        .display-npc-portrait {
            max-height: 70vh;
            max-width: 50vw;
            border-radius: 16px;
            border: 3px solid #ffd700;
            box-shadow: 0 0 40px rgba(255,215,0,0.3);
        }

        .display-npc-info {
            padding: 30px;
            text-align: center;
        }

        .display-npc-name {
            font-family: 'Cinzel', serif;
            font-size: 2.5em;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .display-npc-role {
            font-size: 1.2em;
            color: #aaa;
        }

        /* === COMBAT VIEW === */
        .display-combat {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 30px;
            width: 100%;
            height: 100%;
            padding: 30px;
            background: radial-gradient(ellipse at center, rgba(220,50,50,0.1) 0%, rgba(10,10,21,0.95) 100%);
        }

        .display-monster-card {
            background: rgba(30,30,50,0.9);
            border: 2px solid #dc3232;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            min-width: 250px;
            max-width: 350px;
            box-shadow: 0 0 30px rgba(220,50,50,0.3);
        }

        .display-monster-portrait {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .display-monster-name {
            font-family: 'Cinzel', serif;
            font-size: 1.5em;
            color: #ff6666;
            margin-bottom: 10px;
        }

        .display-monster-hp {
            width: 100%;
            height: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin-top: 10px;
        }

        .display-monster-hp-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
            background: linear-gradient(90deg, #dc3232, #46b450);
        }

        .display-monster-hp-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
            font-size: 12px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }

        .display-combat-round {
            position: absolute;
            top: 80px;
            right: 30px;
            font-family: 'Cinzel', serif;
            font-size: 1.5em;
            color: #dc3232;
            text-shadow: 0 0 10px rgba(220,50,50,0.5);
        }

        /* === IDLE / WAITING === */
        .display-idle {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, rgba(15,52,96,0.3) 0%, rgba(10,10,21,1) 100%);
        }

        .display-idle-logo {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        .display-idle h2 {
            font-family: 'Cinzel', serif;
            font-size: 2.5em;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .display-idle p {
            color: #666;
            font-size: 1.2em;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* === TRANSITION ANIMATION === */
        .display-transition {
            position: fixed;
            inset: 0;
            background: #000;
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease;
        }

        .display-transition.active {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Transition overlay -->
    <div class="display-transition" id="display-transition"></div>

    <div class="display-container">
        <!-- Top Bar -->
        <div class="display-topbar" id="display-topbar" style="display:none;">
            <div class="display-scene-title" id="display-scene-title"></div>
            <div class="display-scene-type" id="display-scene-type"></div>
        </div>

        <!-- Main Display Area -->
        <div class="display-main" id="display-main">
            <!-- Idle state -->
            <div class="display-idle" id="display-idle">
                <div class="display-idle-logo">🎲</div>
                <h2>RPG Master Toolkit</h2>
                <p>Aguardando o Mestre iniciar a sessão...</p>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const POLL_INTERVAL = <?php echo intval( $poll_interval ); ?>;
        const API_URL = '<?php echo esc_url( rest_url( 'rmt/v1/display' ) ); ?>';
        
        let currentState = null;
        let pollTimer = null;

        // Type labels
        const typeLabels = {
            exploration: '🗺️ Exploração',
            combat: '⚔️ Combate',
            social: '💬 Interação',
            puzzle: '🧩 Enigma',
            rest: '🏕️ Descanso',
            travel: '🚶 Viagem',
            cutscene: '🎥 Cutscene',
            shop: '🏪 Comércio'
        };

        async function pollDisplay() {
            try {
                const response = await fetch(API_URL);
                const data = await response.json();
                
                if (JSON.stringify(data) !== JSON.stringify(currentState)) {
                    await updateDisplay(data);
                    currentState = data;
                }
            } catch (err) {
                console.error('Polling error:', err);
            }
        }

        async function updateDisplay(data) {
            const topbar = document.getElementById('display-topbar');
            const main = document.getElementById('display-main');
            const idle = document.getElementById('display-idle');

            if (data.status !== 'active') {
                topbar.style.display = 'none';
                main.innerHTML = `
                    <div class="display-idle">
                        <div class="display-idle-logo">🎲</div>
                        <h2>RPG Master Toolkit</h2>
                        <p>Aguardando o Mestre iniciar a sessão...</p>
                    </div>`;
                return;
            }

            // Transition effect
            const transition = document.getElementById('display-transition');
            if (currentState && currentState.scene_title !== data.scene_title) {
                transition.classList.add('active');
                await new Promise(r => setTimeout(r, 500));
            }

            // Update topbar
            topbar.style.display = 'flex';
            document.getElementById('display-scene-title').textContent = data.scene_title || 'Sem título';
            
            const typeEl = document.getElementById('display-scene-type');
            typeEl.textContent = typeLabels[data.scene_type] || data.scene_type;
            typeEl.className = 'display-scene-type type-' + data.scene_type;

            // Render based on scene type
            let html = '';

            // COMBAT VIEW
            if (data.scene_type === 'combat' && data.combat && data.combat.monsters) {
                html = '<div class="display-combat">';
                if (data.combat.round) {
                    html += `<div class="display-combat-round">Round ${data.combat.round}</div>`;
                }
                data.combat.monsters.forEach(m => {
                    const hpPercent = m.max_hp > 0 ? Math.round((m.current_hp / m.max_hp) * 100) : 0;
                    const hpColor = hpPercent > 50 ? '#46b450' : hpPercent > 25 ? '#ffb900' : '#dc3232';
                    
                    html += `<div class="display-monster-card">`;
                    if (m.portrait) {
                        html += `<img src="${m.portrait}" class="display-monster-portrait" alt="${m.name}">`;
                    } else {
                        html += `<div class="display-monster-portrait" style="display:flex;align-items:center;justify-content:center;font-size:60px;background:#1a1a2e;">🐉</div>`;
                    }
                    html += `<div class="display-monster-name">${m.name}</div>`;
                    html += `<div class="display-monster-hp">
                        <div class="display-monster-hp-fill" style="width:${hpPercent}%;background:${hpColor}"></div>
                        <span class="display-monster-hp-text">${m.current_hp}/${m.max_hp}</span>
                    </div>`;
                    
                    // Conditions
                    if (m.conditions && m.conditions.length > 0) {
                        html += '<div style="margin-top:8px;">';
                        m.conditions.forEach(c => {
                            html += `<span class="rmt-condition-badge rmt-condition-${c}">${c}</span> `;
                        });
                        html += '</div>';
                    }
                    html += '</div>';
                });
                html += '</div>';
            }
            // NPC INTERACTION
            else if (data.scene_type === 'social' && data.active_npc) {
                html = '<div class="display-npc">';
                if (data.active_npc.portrait) {
                    html += `<img src="${data.active_npc.portrait}" class="display-npc-portrait" alt="${data.active_npc.name}">`;
                }
                html += `<div class="display-npc-info">
                    <div class="display-npc-name">${data.active_npc.name}</div>
                    <div class="display-npc-role">${data.active_npc.race || ''} — ${data.active_npc.role || ''}</div>
                </div>`;
                html += '</div>';
            }
            // MAP VIEW
            else if (data.map && data.map.image) {
                html = '<div class="display-map">';
                html += `<img src="${data.map.image}" alt="${data.map.title || 'Mapa'}" id="display-map-img">`;
                
                // Character tokens
                if (data.character_positions && data.character_positions.length > 0) {
                    data.character_positions.forEach(pos => {
                        const bgImage = pos.avatar ? `background-image:url(${pos.avatar})` : '';
                        html += `<div class="display-token" style="left:${pos.x}px;top:${pos.y}px;${bgImage}">
                            <div class="display-token-name">${pos.name}</div>
                        </div>`;
                    });
                }
                html += '</div>';
            }
            // SCENE IMAGE
            else if (data.scene_image_url) {
                html = `<div class="display-scene-image" style="background-image:url(${data.scene_image_url})"></div>`;
            }
            // FALLBACK
            else {
                html = `<div class="display-idle">
                    <div class="display-idle-logo">📖</div>
                    <h2>${data.scene_title || 'Aventura em andamento'}</h2>
                    <p>O Mestre está narrando...</p>
                </div>`;
            }

            main.innerHTML = html;

            // Remove transition
            setTimeout(() => {
                transition.classList.remove('active');
            }, 100);
        }

        // Start polling
        pollDisplay();
        pollTimer = setInterval(pollDisplay, POLL_INTERVAL);

        // Fullscreen on click
        document.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            }
        });

        // Show cursor on mouse move, hide after 3s
        let cursorTimeout;
        document.addEventListener('mousemove', () => {
            document.body.style.cursor = 'default';
            clearTimeout(cursorTimeout);
            cursorTimeout = setTimeout(() => {
                document.body.style.cursor = 'none';
            }, 3000);
        });
    })();
    </script>

    <?php wp_footer(); ?>
</body>
</html>
