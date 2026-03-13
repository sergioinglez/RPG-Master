/**
 * RPG Master Theme - DM Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // =============================================
    // TAB NAVIGATION
    // =============================================
    document.querySelectorAll('.rmt-nav-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.tab;
            
            // Update active tab
            document.querySelectorAll('.rmt-nav-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show target content
            document.querySelectorAll('.rmt-tab-content').forEach(c => c.style.display = 'none');
            const tabContent = document.getElementById('tab-' + target);
            if (tabContent) {
                tabContent.style.display = 'block';
            }

            // Load content on tab switch
            if (target === 'players') loadPlayersControl();
            if (target === 'session') loadSessionPanel();
        });
    });

    // =============================================
    // LOAD INITIAL DATA
    // =============================================
    loadAdventures();
    loadSessionStatus();
    loadCharactersOverview();

    // =============================================
    // ADVENTURES
    // =============================================
    async function loadAdventures() {
        try {
            const adventures = await RMT.api.get('adventures');
            const container = document.getElementById('dm-adventures-list');
            
            if (!adventures || adventures.length === 0) {
                container.innerHTML = '<p style="color:#aaa;">Nenhuma aventura cadastrada.</p>';
                return;
            }

            let html = '';
            adventures.forEach(adv => {
                html += `
                    <div style="padding:12px;border-bottom:1px solid var(--rmt-border);display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <strong style="color:var(--rmt-gold);font-family:var(--rmt-font);">${adv.title}</strong>
                            <br><small style="color:#888;">${adv.scene_count} cenas</small>
                        </div>
                        <button class="rmt-btn rmt-btn-primary rmt-btn-sm" onclick="startSession(${adv.id})">
                            ▶️ Iniciar
                        </button>
                    </div>
                `;
            });
            container.innerHTML = html;
        } catch (err) {
            document.getElementById('dm-adventures-list').innerHTML = '<p style="color:#dc3232;">Erro ao carregar.</p>';
        }
    }

    // =============================================
    // SESSION STATUS
    // =============================================
    async function loadSessionStatus() {
        try {
            const session = await RMT.api.get('session/status');
            const container = document.getElementById('dm-session-status');

            if (session.status === 'active') {
                container.innerHTML = `
                    <div style="text-align:center;">
                        <div style="font-size:24px;color:var(--rmt-success);margin-bottom:10px;">🔴 ATIVA</div>
                        <p><strong>${session.scene_title || 'Sessão em andamento'}</strong></p>
                        <p style="font-size:13px;color:#888;">Tipo: ${session.scene_type}</p>
                        <div style="display:flex;gap:8px;justify-content:center;margin-top:15px;">
                            <button class="rmt-btn rmt-btn-gold rmt-btn-sm" onclick="document.querySelector('[data-tab=session]').click()">
                                🎮 Gerenciar
                            </button>
                            <button class="rmt-btn rmt-btn-danger rmt-btn-sm" onclick="endCurrentSession(${session.id})">
                                ⏹️ Encerrar
                            </button>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = '<p style="color:#888;text-align:center;">Nenhuma sessão ativa.<br>Inicie uma aventura para começar.</p>';
            }
        } catch (err) {
            document.getElementById('dm-session-status').innerHTML = '<p style="color:#888;">Erro ao verificar sessão.</p>';
        }
    }

    // =============================================
    // CHARACTERS OVERVIEW
    // =============================================
    async function loadCharactersOverview() {
        try {
            const chars = await RMT.api.get('dm/characters');
            const container = document.getElementById('dm-characters-overview');

            if (!chars || chars.length === 0) {
                container.innerHTML = '<p style="color:#aaa;">Nenhum personagem criado ainda.</p>';
                return;
            }

            let html = '<div class="rmt-grid rmt-grid-3">';
            chars.forEach(c => {
                const conditions = JSON.parse(c.conditions || '[]');
                const hpPercent = c.max_hp > 0 ? Math.round((c.current_hp / c.max_hp) * 100) : 0;

                html += `
                    <div class="rmt-player-control-card">
                        <div class="rmt-player-control-header">
                            ${c.avatar_url ? `<img src="${c.avatar_url}" class="rmt-player-avatar">` : 
                              `<div class="rmt-player-avatar" style="display:flex;align-items:center;justify-content:center;font-size:20px;">⚔️</div>`}
                            <div>
                                <strong style="color:var(--rmt-gold);">${c.name}</strong>
                                <br><small style="color:#888;">${c.player_name || 'N/A'} — ${c.race} ${c.class} Lv.${c.level}</small>
                            </div>
                        </div>
                        ${RMT.renderHPBar(c.current_hp, c.max_hp, c.temp_hp)}
                        ${conditions.length > 0 ? '<div style="margin-top:8px;">' + RMT.renderConditions(conditions) + '</div>' : ''}
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        } catch (err) {
            document.getElementById('dm-characters-overview').innerHTML = '<p style="color:#dc3232;">Erro ao carregar personagens.</p>';
        }
    }

    // =============================================
    // SESSION PANEL (full)
    // =============================================
    window.loadSessionPanel = async function() {
        const container = document.getElementById('dm-session-panel');
        RMT.showLoading(container);

        try {
            const session = await RMT.api.get('session/status');
            
            if (session.status !== 'active') {
                container.innerHTML = `
                    <div class="rmt-card" style="text-align:center;padding:40px;">
                        <p style="font-size:18px;color:#888;">Nenhuma sessão ativa.</p>
                        <p>Inicie uma aventura na aba "Visão Geral".</p>
                    </div>`;
                return;
            }

            // Buscar dados da aventura
            const adventure = await RMT.api.get('adventures/' + session.adventure_id);

            let html = '<div class="rmt-session-panel">';
            
            // Main area
            html += '<div class="rmt-session-main">';
            
            // Scene flow
            html += '<div class="rmt-card"><div class="rmt-card-header">🎬 Fluxo de Cenas</div>';
            html += '<div class="rmt-scene-flow">';
            if (adventure && adventure.scenes) {
                adventure.scenes.forEach(scene => {
                    const isActive = scene.id == session.current_scene_id;
                    html += `
                        <div class="rmt-scene-card ${isActive ? 'active' : ''}" onclick="changeScene(${session.id}, ${scene.id})">
                            <div class="scene-order">#${scene.order}</div>
                            <div class="scene-name">${scene.title}</div>
                            <div class="scene-type">${scene.scene_type}</div>
                        </div>`;
                });
            }
            html += '</div></div>';

            // Narration
            html += '<div class="rmt-card"><div class="rmt-card-header">📖 Narração</div>';
            html += `<div class="rmt-narration-box">${session.scene_description || 'Nenhum texto de narração para esta cena.'}</div>`;
            html += '</div>';

            // DM Notes
            const currentScene = adventure?.scenes?.find(s => s.id == session.current_scene_id);
            if (currentScene && currentScene.dm_notes) {
                html += `<div class="rmt-dm-notes"><h4>🔒 Notas do Mestre</h4><p>${currentScene.dm_notes}</p></div>`;
            }

            html += '</div>'; // end main

            // Sidebar
            html += '<div class="rmt-session-sidebar">';
            
            // Session controls
            html += `<div class="rmt-card">
                <div class="rmt-card-header">⚡ Controles</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button class="rmt-btn rmt-btn-primary" onclick="pauseSession(${session.id})">⏸️ Pausar Sessão</button>
                    <button class="rmt-btn rmt-btn-danger" onclick="endCurrentSession(${session.id})">⏹️ Encerrar Sessão</button>
                </div>
            </div>`;

            // NPCs in current scene
            if (currentScene && currentScene.linked_npcs) {
                html += '<div class="rmt-card"><div class="rmt-card-header">👤 NPCs na Cena</div>';
                const npcIds = currentScene.linked_npcs.split(',').map(id => id.trim());
                if (adventure.npcs) {
                    adventure.npcs.forEach(npc => {
                        if (npcIds.includes(String(npc.id))) {
                            html += `
                                <div class="rmt-npc-card" onclick="showNPC(${session.id}, ${npc.id})" style="cursor:pointer;">
                                    ${npc.portrait ? `<img src="${npc.portrait}" class="rmt-npc-portrait">` : 
                                      `<div class="rmt-npc-portrait" style="display:flex;align-items:center;justify-content:center;font-size:24px;background:#1a1a2e;">👤</div>`}
                                    <div class="rmt-npc-info">
                                        <h4>${npc.name}</h4>
                                        <p>${npc.race || ''} — ${npc.class || ''}</p>
                                    </div>
                                </div>`;
                        }
                    });
                }
                html += '</div>';
            }

            html += '</div>'; // end sidebar
            html += '</div>'; // end panel

            container.innerHTML = html;
        } catch (err) {
            container.innerHTML = '<div class="rmt-card"><p style="color:#dc3232;">Erro ao carregar sessão: ' + err.message + '</p></div>';
        }
    };

    // =============================================
    // PLAYERS CONTROL
    // =============================================
    async function loadPlayersControl() {
        const container = document.getElementById('dm-players-control');
        RMT.showLoading(container);

        try {
            const chars = await RMT.api.get('dm/characters');
            const conditions = await RMT.api.get('rules/conditions');

            if (!chars || chars.length === 0) {
                container.innerHTML = '<p style="color:#aaa;">Nenhum personagem encontrado.</p>';
                return;
            }

            let html = '';
            chars.forEach(c => {
                const charConditions = JSON.parse(c.conditions || '[]');
                
                html += `
                    <div class="rmt-player-control-card">
                        <div class="rmt-player-control-header">
                            ${c.avatar_url ? `<img src="${c.avatar_url}" class="rmt-player-avatar">` : 
                              `<div class="rmt-player-avatar" style="display:flex;align-items:center;justify-content:center;font-size:20px;">⚔️</div>`}
                            <div style="flex:1;">
                                <strong style="color:var(--rmt-gold);font-size:16px;">${c.name}</strong>
                                <br><small style="color:#888;">${c.player_name || ''} — ${c.race} ${c.class} Lv.${c.level}</small>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:11px;color:#888;">CA ${c.armor_class} | Iniciativa ${RMT.dnd.formatMod(RMT.dnd.abilityMod(c.dexterity))}</div>
                            </div>
                        </div>
                        
                        ${RMT.renderHPBar(c.current_hp, c.max_hp, c.temp_hp)}
                        
                        <div style="margin:8px 0;">${RMT.renderXPBar(parseInt(c.experience_points), parseInt(c.level))}</div>
                        
                        ${charConditions.length > 0 ? '<div style="margin:8px 0;">' + RMT.renderConditions(charConditions) + '</div>' : ''}
                        
                        <div class="rmt-player-control-actions">
                            <button class="rmt-btn rmt-btn-success rmt-btn-sm" onclick="dmHealPlayer(${c.id})">💚 Curar</button>
                            <button class="rmt-btn rmt-btn-danger rmt-btn-sm" onclick="dmDamagePlayer(${c.id})">💔 Dano</button>
                            <button class="rmt-btn rmt-btn-sm" style="background:var(--rmt-info);color:white;" onclick="dmGrantXP(${c.id})">⭐ XP</button>
                            <button class="rmt-btn rmt-btn-sm" style="background:#9c27b0;color:white;" onclick="dmAddCondition(${c.id})">🔮 Condição</button>
                            <button class="rmt-btn rmt-btn-sm" style="background:#607d8b;color:white;" onclick="dmRemoveCondition(${c.id})">❌ Remover</button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        } catch (err) {
            container.innerHTML = '<p style="color:#dc3232;">Erro: ' + err.message + '</p>';
        }
    }
});

// =============================================
// GLOBAL FUNCTIONS
// =============================================

async function startSession(adventureId) {
    if (!confirm('Iniciar sessão para esta aventura?')) return;
    
    try {
        const session = await RMT.api.post('session/start', { adventure_id: adventureId });
        RMT.toast('Sessão iniciada!', 'success');
        setTimeout(() => location.reload(), 1000);
    } catch (err) {
        RMT.toast('Erro ao iniciar sessão', 'error');
    }
}

async function changeScene(sessionId, sceneId) {
    try {
        await RMT.api.post('session/scene', { session_id: sessionId, scene_id: sceneId });
        RMT.toast('Cena alterada!', 'success');
        loadSessionPanel();
    } catch (err) {
        RMT.toast('Erro ao mudar cena', 'error');
    }
}

async function showNPC(sessionId, npcId) {
    try {
        await RMT.api.post('session/npc', { session_id: sessionId, npc_id: npcId });
        RMT.toast('NPC exibido na tela!', 'success');
    } catch (err) {
        RMT.toast('Erro ao exibir NPC', 'error');
    }
}

async function pauseSession(sessionId) {
    try {
        await RMT.api.post('session/pause', { session_id: sessionId });
        RMT.toast('Sessão pausada', 'info');
        location.reload();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

async function endCurrentSession(sessionId) {
    if (!confirm('Tem certeza que deseja encerrar a sessão?')) return;
    try {
        await RMT.api.post('session/end', { session_id: sessionId });
        RMT.toast('Sessão encerrada', 'info');
        location.reload();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

async function dmHealPlayer(charId) {
    const amount = prompt('Quantidade de cura:');
    if (!amount || isNaN(amount)) return;
    try {
        await RMT.api.post('dm/player/' + charId + '/hp', { amount: parseInt(amount), type: 'heal' });
        RMT.toast(`+${amount} HP!`, 'success');
        document.querySelector('[data-tab="players"]').click();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

async function dmDamagePlayer(charId) {
    const amount = prompt('Quantidade de dano:');
    if (!amount || isNaN(amount)) return;
    try {
        await RMT.api.post('dm/player/' + charId + '/hp', { amount: parseInt(amount), type: 'damage' });
        RMT.toast(`-${amount} HP!`, 'error');
        document.querySelector('[data-tab="players"]').click();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

async function dmGrantXP(charId) {
    const xp = prompt('Quantidade de XP:');
    if (!xp || isNaN(xp)) return;
    try {
        const result = await RMT.api.post('dm/player/' + charId + '/xp', { xp: parseInt(xp) });
        if (result.leveled_up) {
            RMT.toast(`🎉 LEVEL UP! Nível ${result.new_level}!`, 'success');
        } else {
            RMT.toast(`+${xp} XP concedido!`, 'success');
        }
        document.querySelector('[data-tab="players"]').click();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

async function dmAddCondition(charId) {
    const condition = prompt('Condição (ex: poisoned, blinded, stunned, prone, charmed, frightened):');
    if (!condition) return;
    try {
        await RMT.api.post('dm/player/' + charId + '/condition', { condition: condition.trim() });
        RMT.toast('Condição aplicada!', 'info');
        document.querySelector('[data-tab="players"]').click();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

async function dmRemoveCondition(charId) {
    const condition = prompt('Qual condição remover?');
    if (!condition) return;
    try {
        await RMT.api.delete('dm/player/' + charId + '/condition', { condition: condition.trim() });
        RMT.toast('Condição removida!', 'success');
        document.querySelector('[data-tab="players"]').click();
    } catch (err) {
        RMT.toast('Erro', 'error');
    }
}

// Dice Roller
function toggleDiceRoller() {
    const panel = document.querySelector('.rmt-dice-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function rollDice(notation) {
    if (!notation) return;
    const result = RMT.dnd.rollDice(notation);
    if (!result) {
        document.getElementById('dice-result').innerHTML = '<span style="color:#dc3232;">Formato inválido</span>';
        return;
    }
    
    document.getElementById('dice-result').innerHTML = `
        ${result.total}
        <small>Rolagem: [${result.rolls.join(', ')}] ${result.bonus ? (result.bonus > 0 ? '+' : '') + result.bonus : ''}</small>
    `;
}
