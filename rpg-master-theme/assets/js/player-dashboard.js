/**
 * RPG Master Theme - Player Dashboard JavaScript
 */

let selectedCharacterId = null;
let racesData = {};
let classesData = {};
let backgroundsData = {};

document.addEventListener('DOMContentLoaded', function() {

    // =============================================
    // TAB NAVIGATION
    // =============================================
    document.querySelectorAll('.rmt-nav-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.tab;
            
            document.querySelectorAll('.rmt-nav-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.rmt-tab-content').forEach(c => c.style.display = 'none');
            document.getElementById('tab-' + target).style.display = 'block';
        });
    });

    // Load initial data
    loadRulesData();
    loadMyCharacters();

    // Attribute input listeners
    document.querySelectorAll('.rmt-attr-input').forEach(input => {
        input.addEventListener('input', updateModifiers);
    });

    // Create character form
    document.getElementById('create-character-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        await createCharacter();
    });

    // Add item form
    document.getElementById('add-item-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        await addItem();
    });
});

// =============================================
// LOAD D&D RULES DATA
// =============================================
async function loadRulesData() {
    try {
        [racesData, classesData, backgroundsData] = await Promise.all([
            RMT.api.get('rules/races'),
            RMT.api.get('rules/classes'),
            RMT.api.get('rules/backgrounds'),
        ]);

        // Populate race select
        const raceSelect = document.getElementById('char-race');
        Object.entries(racesData).forEach(([key, race]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = race.name;
            raceSelect.appendChild(option);
        });

        // Populate class select
        const classSelect = document.getElementById('char-class');
        Object.entries(classesData).forEach(([key, cls]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = cls.name;
            classSelect.appendChild(option);
        });

        // Populate background select
        const bgSelect = document.getElementById('char-background');
        Object.entries(backgroundsData).forEach(([key, bg]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = bg.name;
            bgSelect.appendChild(option);
        });

        // Race change -> update subraces
        raceSelect.addEventListener('change', function() {
            const race = racesData[this.value];
            const subraceGroup = document.getElementById('subrace-group');
            const subraceSelect = document.getElementById('char-subrace');
            const bonusInfo = document.getElementById('racial-bonuses-info');

            subraceSelect.innerHTML = '<option value="">— Nenhuma —</option>';

            if (race && race.subraces && Object.keys(race.subraces).length > 0) {
                Object.entries(race.subraces).forEach(([key, sub]) => {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = sub.name;
                    subraceSelect.appendChild(opt);
                });
                subraceGroup.style.display = 'block';
            } else {
                subraceGroup.style.display = 'none';
            }

            // Show racial bonuses
            if (race) {
                const bonuses = Object.entries(race.bonuses).map(([ab, val]) => 
                    `${RMT.dnd.abilityNamesShort[ab]} +${val}`
                ).join(', ');
                
                bonusInfo.innerHTML = `<strong style="color:var(--rmt-secondary);">Bônus Raciais (${race.name}):</strong> ${bonuses}
                    <br><small style="color:#888;">Velocidade: ${race.speed}ft | Tamanho: ${race.size}</small>
                    <br><small style="color:#888;">Traços: ${race.traits.join(', ')}</small>`;
                bonusInfo.style.display = 'block';
            } else {
                bonusInfo.style.display = 'none';
            }
        });

    } catch (err) {
        console.error('Error loading rules data:', err);
    }
}

// =============================================
// MY CHARACTERS
// =============================================
async function loadMyCharacters() {
    const container = document.getElementById('player-characters-list');
    RMT.showLoading(container);

    try {
        const chars = await RMT.api.get('characters');

        if (!chars || chars.length === 0) {
            container.innerHTML = `
                <div style="text-align:center;padding:40px;">
                    <div style="font-size:48px;margin-bottom:15px;">🧙</div>
                    <p style="font-size:18px;color:#aaa;">Você ainda não tem nenhum personagem.</p>
                    <p>Crie seu primeiro aventureiro!</p>
                    <button class="rmt-btn rmt-btn-gold" onclick="showCreateCharacter()" style="margin-top:15px;">
                        <i class="fas fa-magic"></i> Criar Personagem
                    </button>
                </div>`;
            return;
        }

        let html = `<div class="rmt-char-list">`;
        chars.forEach(c => {
            const conditions = JSON.parse(c.conditions || '[]');
            const raceName = racesData[c.race]?.name || c.race;
            const className = classesData[c.class]?.name || c.class;

            html += `
                <div class="rmt-char-card ${c.id == selectedCharacterId ? 'selected' : ''}" onclick="selectCharacter(${c.id})">
                    <div class="rmt-char-level-badge">${c.level}</div>
                    <div class="rmt-char-card-top">
                        ${c.avatar_url ? `<img src="${c.avatar_url}" class="rmt-char-avatar">` : 
                          `<div class="rmt-char-avatar">⚔️</div>`}
                        <div>
                            <div class="rmt-char-name">${c.name}</div>
                            <div class="rmt-char-subtitle">${raceName} ${className}</div>
                        </div>
                    </div>
                    ${RMT.renderHPBar(c.current_hp, c.max_hp, c.temp_hp)}
                    ${RMT.renderXPBar(parseInt(c.experience_points), parseInt(c.level))}
                    ${conditions.length > 0 ? '<div style="margin-top:8px;">' + RMT.renderConditions(conditions) + '</div>' : ''}
                </div>`;
        });

        html += `</div>`;
        html += `<p style="color:#555;font-size:12px;margin-top:10px;">${chars.length}/5 personagens criados</p>`;
        container.innerHTML = html;

    } catch (err) {
        container.innerHTML = '<p style="color:#dc3232;">Erro ao carregar personagens.</p>';
    }
}

// =============================================
// SELECT CHARACTER -> Load Sheet, Inventory, Achievements
// =============================================
async function selectCharacter(charId) {
    selectedCharacterId = charId;
    loadMyCharacters(); // Refresh selection visual
    loadCharacterSheet(charId);
    loadInventory(charId);
    loadAchievements(charId);
    document.getElementById('btn-add-item').style.display = 'inline-flex';
}

// =============================================
// CHARACTER SHEET
// =============================================
async function loadCharacterSheet(charId) {
    const container = document.getElementById('player-character-sheet');
    RMT.showLoading(container);

    try {
        const sheet = await RMT.api.get('characters/' + charId + '/sheet');
        const c = sheet.character;
        const mods = sheet.modifiers;

        const raceName = racesData[c.race]?.name || c.race;
        const className = classesData[c.class]?.name || c.class;

        let html = '<div class="rmt-sheet">';

        // Header card
        html += `<div class="rmt-card rmt-sheet-full">
            <div style="display:flex;align-items:center;gap:20px;">
                ${c.avatar_url ? `<img src="${c.avatar_url}" style="width:80px;height:80px;border-radius:50%;border:3px solid var(--rmt-gold);">` : 
                  `<div style="width:80px;height:80px;border-radius:50%;background:var(--rmt-bg-dark);border:3px solid var(--rmt-gold);display:flex;align-items:center;justify-content:center;font-size:36px;">⚔️</div>`}
                <div>
                    <h2 style="font-family:var(--rmt-font);color:var(--rmt-gold);margin:0;">${c.name}</h2>
                    <p style="color:#aaa;margin:4px 0;">${raceName} ${className} — Nível ${c.level}</p>
                    <p style="color:#666;margin:0;font-size:13px;">${c.background || ''} ${c.alignment ? '| ' + c.alignment : ''}</p>
                </div>
            </div>
            
            <div class="rmt-combat-stats">
                <div class="rmt-combat-stat">
                    <div class="rmt-combat-stat-value">${c.armor_class}</div>
                    <div class="rmt-combat-stat-label">CA</div>
                </div>
                <div class="rmt-combat-stat">
                    <div class="rmt-combat-stat-value">${sheet.initiative >= 0 ? '+' : ''}${sheet.initiative}</div>
                    <div class="rmt-combat-stat-label">Iniciativa</div>
                </div>
                <div class="rmt-combat-stat">
                    <div class="rmt-combat-stat-value">${c.speed}ft</div>
                    <div class="rmt-combat-stat-label">Velocidade</div>
                </div>
                <div class="rmt-combat-stat">
                    <div class="rmt-combat-stat-value">+${sheet.proficiency_bonus}</div>
                    <div class="rmt-combat-stat-label">Proficiência</div>
                </div>
                <div class="rmt-combat-stat">
                    <div class="rmt-combat-stat-value">${sheet.passive_perception}</div>
                    <div class="rmt-combat-stat-label">Percepção Passiva</div>
                </div>
            </div>

            ${RMT.renderHPBar(c.current_hp, c.max_hp, c.temp_hp)}
            ${RMT.renderXPBar(parseInt(c.experience_points), parseInt(c.level))}
        </div>`;

        // Attributes
        html += `<div class="rmt-card">
            <div class="rmt-card-header">💪 Atributos</div>
            ${RMT.renderStatBlock({
                str: c.strength, dex: c.dexterity, con: c.constitution,
                int: c.intelligence, wis: c.wisdom, cha: c.charisma
            })}
        </div>`;

        // Saving Throws
        html += `<div class="rmt-card">
            <div class="rmt-card-header">🛡️ Testes de Resistência</div>
            <ul class="rmt-save-list">`;
        Object.entries(sheet.saving_throws).forEach(([ab, save]) => {
            html += `<li>
                <span class="rmt-prof-dot ${save.proficient ? 'proficient' : ''}"></span>
                <span>${RMT.dnd.abilityNames[ab]}</span>
                <span class="rmt-skill-mod">${RMT.dnd.formatMod(save.modifier)}</span>
            </li>`;
        });
        html += '</ul></div>';

        // Skills
        html += `<div class="rmt-card rmt-sheet-full">
            <div class="rmt-card-header">🎯 Perícias</div>
            <div style="columns:2;column-gap:20px;">
            <ul class="rmt-skill-list">`;
        Object.entries(sheet.skills).forEach(([key, skill]) => {
            const profClass = skill.proficiency === 2 ? 'expertise' : skill.proficiency === 1 ? 'proficient' : '';
            html += `<li>
                <span class="rmt-prof-dot ${profClass}"></span>
                <span>${skill.name} <small style="color:#555">(${RMT.dnd.abilityNamesShort[skill.ability]})</small></span>
                <span class="rmt-skill-mod">${RMT.dnd.formatMod(skill.modifier)}</span>
            </li>`;
        });
        html += '</ul></div></div>';

        // Money
        html += `<div class="rmt-card">
            <div class="rmt-card-header">💰 Dinheiro</div>
            <div class="rmt-money-row">
                <div class="rmt-money-item"><span class="coin coin-gold">G</span> ${parseFloat(c.gold)}</div>
                <div class="rmt-money-item"><span class="coin coin-silver">S</span> ${parseFloat(c.silver)}</div>
                <div class="rmt-money-item"><span class="coin coin-copper">C</span> ${parseFloat(c.copper)}</div>
                <div class="rmt-money-item"><span class="coin coin-electrum">E</span> ${parseFloat(c.electrum)}</div>
                <div class="rmt-money-item"><span class="coin coin-platinum">P</span> ${parseFloat(c.platinum)}</div>
            </div>
        </div>`;

        // Personality
        html += `<div class="rmt-card">
            <div class="rmt-card-header">📖 Personalidade</div>
            ${c.personality_traits ? `<p><strong style="color:var(--rmt-secondary);">Traços:</strong> ${c.personality_traits}</p>` : ''}
            ${c.ideals ? `<p><strong style="color:var(--rmt-secondary);">Ideais:</strong> ${c.ideals}</p>` : ''}
            ${c.bonds ? `<p><strong style="color:var(--rmt-secondary);">Vínculos:</strong> ${c.bonds}</p>` : ''}
            ${c.flaws ? `<p><strong style="color:var(--rmt-secondary);">Defeitos:</strong> ${c.flaws}</p>` : ''}
            ${c.backstory ? `<hr style="border-color:var(--rmt-border);"><p><strong style="color:var(--rmt-secondary);">História:</strong> ${c.backstory}</p>` : ''}
        </div>`;

        html += '</div>';
        container.innerHTML = html;

        // Auto switch to sheet tab
        document.querySelector('[data-tab="sheet"]').click();
    } catch (err) {
        container.innerHTML = '<div class="rmt-card"><p style="color:#dc3232;">Erro ao carregar ficha.</p></div>';
    }
}

// =============================================
// INVENTORY
// =============================================
async function loadInventory(charId) {
    const container = document.getElementById('player-inventory');
    RMT.showLoading(container);

    try {
        const items = await RMT.api.get('characters/' + charId + '/inventory');

        if (!items || items.length === 0) {
            container.innerHTML = '<p style="color:#aaa;text-align:center;padding:20px;">Inventário vazio. Adicione itens!</p>';
            return;
        }

        let totalWeight = 0;
        let html = '<div class="rmt-inventory-grid">';
        
        items.forEach(item => {
            totalWeight += parseFloat(item.weight || 0) * parseInt(item.quantity || 1);
            html += `
                <div class="rmt-inventory-item rmt-rarity-${item.rarity}">
                    <div class="rmt-inventory-icon">${RMT.itemIcon(item.type)}</div>
                    <div style="flex:1;">
                        <strong>${item.name}</strong>
                        ${item.is_equipped ? '<span style="color:var(--rmt-success);font-size:11px;"> ✓ Equipado</span>' : ''}
                        <br><small style="color:#888;">${item.description || item.type} ${item.quantity > 1 ? '(x' + item.quantity + ')' : ''}</small>
                        ${item.damage_dice ? `<br><small style="color:#dc3232;">⚔️ ${item.damage_dice} ${item.damage_type}</small>` : ''}
                        ${item.armor_ac_base ? `<br><small style="color:#0073aa;">🛡️ CA ${item.armor_ac_base}</small>` : ''}
                    </div>
                    <div class="rmt-inventory-item-actions">
                        <button class="rmt-btn rmt-btn-sm" style="background:rgba(255,255,255,0.1);" onclick="toggleEquip(${item.id}, ${item.is_equipped ? 0 : 1})">
                            ${item.is_equipped ? '🔓' : '⚔️'}
                        </button>
                        <button class="rmt-btn rmt-btn-danger rmt-btn-sm" onclick="deleteItem(${item.id})">🗑️</button>
                    </div>
                </div>`;
        });
        html += '</div>';

        // Weight tracker
        const weightHtml = `<div class="rmt-weight-tracker">
            <i class="fas fa-weight-hanging"></i>
            <span>Peso total: <strong>${totalWeight.toFixed(1)} lb</strong></span>
        </div>`;

        container.innerHTML = weightHtml + html;
    } catch (err) {
        container.innerHTML = '<p style="color:#dc3232;">Erro ao carregar inventário.</p>';
    }
}

async function toggleEquip(itemId, equip) {
    try {
        await RMT.api.put('inventory/' + itemId, { is_equipped: equip });
        loadInventory(selectedCharacterId);
    } catch (err) { RMT.toast('Erro', 'error'); }
}

async function deleteItem(itemId) {
    if (!confirm('Remover este item do inventário?')) return;
    try {
        await RMT.api.delete('inventory/' + itemId);
        RMT.toast('Item removido!', 'info');
        loadInventory(selectedCharacterId);
    } catch (err) { RMT.toast('Erro', 'error'); }
}

// =============================================
// ACHIEVEMENTS
// =============================================
async function loadAchievements(charId) {
    const container = document.getElementById('player-achievements');
    // TODO: Implement achievements endpoint
    container.innerHTML = `
        <div style="text-align:center;padding:30px;">
            <div style="font-size:48px;margin-bottom:10px;">🏆</div>
            <p style="color:#aaa;">O sistema de conquistas será ativado pelo Mestre durante as sessões.</p>
            <p style="font-size:13px;color:#555;">Conquistas serão concedidas conforme você avança na aventura.</p>
        </div>`;
}

// =============================================
// CREATE CHARACTER
// =============================================
function showCreateCharacter() {
    document.getElementById('create-character-modal').style.display = 'flex';
    goToStep(1);
}

function hideCreateCharacter() {
    document.getElementById('create-character-modal').style.display = 'none';
}

function goToStep(step) {
    document.querySelectorAll('.rmt-create-step').forEach(s => s.style.display = 'none');
    document.getElementById('step-' + step).style.display = 'block';
}

function updateModifiers() {
    ['str', 'dex', 'con', 'int', 'wis', 'cha'].forEach(ab => {
        const val = parseInt(document.getElementById('attr-' + ab).value) || 10;
        const mod = RMT.dnd.abilityMod(val);
        document.getElementById('mod-' + ab).textContent = RMT.dnd.formatMod(mod);
    });
}

async function createCharacter() {
    const btn = document.getElementById('btn-create-char');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';

    const data = {
        name: document.getElementById('char-name').value,
        race: document.getElementById('char-race').value,
        subrace: document.getElementById('char-subrace').value,
        class: document.getElementById('char-class').value,
        background: document.getElementById('char-background').value,
        alignment: document.getElementById('char-alignment').value,
        strength: parseInt(document.getElementById('attr-str').value) || 10,
        dexterity: parseInt(document.getElementById('attr-dex').value) || 10,
        constitution: parseInt(document.getElementById('attr-con').value) || 10,
        intelligence: parseInt(document.getElementById('attr-int').value) || 10,
        wisdom: parseInt(document.getElementById('attr-wis').value) || 10,
        charisma: parseInt(document.getElementById('attr-cha').value) || 10,
        personality_traits: document.getElementById('char-personality').value,
        ideals: document.getElementById('char-ideals').value,
        bonds: document.getElementById('char-bonds').value,
        flaws: document.getElementById('char-flaws').value,
        backstory: document.getElementById('char-backstory').value,
    };

    try {
        const character = await RMT.api.post('characters', data);
        RMT.toast('🎉 Personagem criado com sucesso!', 'success');
        hideCreateCharacter();
        document.getElementById('create-character-form').reset();
        loadMyCharacters();
        selectCharacter(character.id);
    } catch (err) {
        RMT.toast('Erro: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> Criar Personagem!';
    }
}

// =============================================
// ADD ITEM
// =============================================
function showAddItem() {
    if (!selectedCharacterId) {
        RMT.toast('Selecione um personagem primeiro!', 'error');
        return;
    }
    document.getElementById('add-item-modal').style.display = 'flex';
}

function hideAddItem() {
    document.getElementById('add-item-modal').style.display = 'none';
}

async function addItem() {
    const data = {
        name: document.getElementById('item-name').value,
        type: document.getElementById('item-type').value,
        rarity: document.getElementById('item-rarity').value,
        quantity: parseInt(document.getElementById('item-qty').value) || 1,
        weight: parseFloat(document.getElementById('item-weight').value) || 0,
        description: document.getElementById('item-desc').value,
    };

    try {
        await RMT.api.post('characters/' + selectedCharacterId + '/inventory', data);
        RMT.toast('Item adicionado!', 'success');
        hideAddItem();
        document.getElementById('add-item-form').reset();
        loadInventory(selectedCharacterId);
    } catch (err) {
        RMT.toast('Erro: ' + err.message, 'error');
    }
}
