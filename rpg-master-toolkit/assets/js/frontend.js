/**
 * RPG Master Toolkit - Frontend JavaScript
 * API client e utilitários compartilhados
 */

const RMT = {

    // =============================================
    // API CLIENT
    // =============================================

    api: {
        baseUrl: typeof rmtFront !== 'undefined' ? rmtFront.rest_url : '/wp-json/rmt/v1/',
        nonce: typeof rmtFront !== 'undefined' ? rmtFront.nonce : '',

        async request(endpoint, options = {}) {
            const url = this.baseUrl + endpoint;
            const defaults = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce,
                },
            };

            const config = { ...defaults, ...options };
            if (options.headers) {
                config.headers = { ...defaults.headers, ...options.headers };
            }

            try {
                const response = await fetch(url, config);
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Erro na requisição');
                }
                if (response.status === 204) return null;
                return await response.json();
            } catch (err) {
                console.error('RMT API Error:', err);
                RMT.toast(err.message, 'error');
                throw err;
            }
        },

        get(endpoint) {
            return this.request(endpoint);
        },

        post(endpoint, data) {
            return this.request(endpoint, {
                method: 'POST',
                body: JSON.stringify(data),
            });
        },

        put(endpoint, data) {
            return this.request(endpoint, {
                method: 'PUT',
                body: JSON.stringify(data),
            });
        },

        patch(endpoint, data) {
            return this.request(endpoint, {
                method: 'PATCH',
                body: JSON.stringify(data),
            });
        },

        delete(endpoint, data = null) {
            const opts = { method: 'DELETE' };
            if (data) opts.body = JSON.stringify(data);
            return this.request(endpoint, opts);
        },
    },

    // =============================================
    // D&D 5e HELPERS
    // =============================================

    dnd: {
        abilityMod(score) {
            return Math.floor((score - 10) / 2);
        },

        formatMod(mod) {
            return mod >= 0 ? `+${mod}` : `${mod}`;
        },

        profBonus(level) {
            if (level >= 17) return 6;
            if (level >= 13) return 5;
            if (level >= 9) return 4;
            if (level >= 5) return 3;
            return 2;
        },

        xpForLevel(level) {
            const table = {
                1:0,2:300,3:900,4:2700,5:6500,6:14000,7:23000,8:34000,
                9:48000,10:64000,11:85000,12:100000,13:120000,14:140000,
                15:165000,16:195000,17:225000,18:265000,19:305000,20:355000
            };
            return table[level] || 0;
        },

        rollDice(notation) {
            // Parse "2d6+3" format
            const match = notation.match(/^(\d+)d(\d+)([+-]\d+)?$/);
            if (!match) return 0;

            const count = parseInt(match[1]);
            const sides = parseInt(match[2]);
            const bonus = parseInt(match[3] || 0);

            let total = bonus;
            const rolls = [];
            for (let i = 0; i < count; i++) {
                const roll = Math.floor(Math.random() * sides) + 1;
                rolls.push(roll);
                total += roll;
            }

            return { total, rolls, notation, bonus };
        },

        // Nomes de habilidades em PT-BR
        abilityNames: {
            str: 'Força', dex: 'Destreza', con: 'Constituição',
            int: 'Inteligência', wis: 'Sabedoria', cha: 'Carisma'
        },

        abilityNamesShort: {
            str: 'FOR', dex: 'DES', con: 'CON',
            int: 'INT', wis: 'SAB', cha: 'CAR'
        },

        skillNames: {
            acrobatics: 'Acrobacia', animal_handling: 'Adestrar Animais',
            arcana: 'Arcanismo', athletics: 'Atletismo', deception: 'Enganação',
            history: 'História', insight: 'Intuição', intimidation: 'Intimidação',
            investigation: 'Investigação', medicine: 'Medicina', nature: 'Natureza',
            perception: 'Percepção', performance: 'Atuação', persuasion: 'Persuasão',
            religion: 'Religião', sleight_of_hand: 'Prestidigitação',
            stealth: 'Furtividade', survival: 'Sobrevivência'
        },
    },

    // =============================================
    // UI HELPERS
    // =============================================

    toast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `rmt-toast rmt-toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'rmt-slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    showLoading(container) {
        container.innerHTML = `
            <div class="rmt-loading">
                <div class="rmt-spinner"></div>
                <p>Carregando...</p>
            </div>
        `;
    },

    renderHPBar(current, max, temp = 0) {
        const percent = max > 0 ? Math.round((current / max) * 100) : 0;
        let color = 'linear-gradient(90deg, #dc3232, #46b450)';
        if (percent <= 25) color = '#dc3232';
        else if (percent <= 50) color = '#ffb900';
        else color = '#46b450';

        return `
            <div class="rmt-hp-container">
                <div class="rmt-hp-bar">
                    <div class="rmt-hp-fill" style="width:${percent}%;background:${color}"></div>
                    <span class="rmt-hp-text">${current}/${max}${temp > 0 ? ` (+${temp})` : ''}</span>
                </div>
            </div>
        `;
    },

    renderStatBlock(stats) {
        const abilities = ['str', 'dex', 'con', 'int', 'wis', 'cha'];
        let html = '<div class="rmt-stat-block">';

        abilities.forEach(ab => {
            const score = stats[ab] || 10;
            const mod = this.dnd.abilityMod(score);
            html += `
                <div class="rmt-stat-item">
                    <div class="rmt-stat-label">${this.dnd.abilityNamesShort[ab]}</div>
                    <div class="rmt-stat-value">${score}</div>
                    <div class="rmt-stat-mod">${this.dnd.formatMod(mod)}</div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    },

    renderConditions(conditionsJson) {
        const conditions = typeof conditionsJson === 'string' ? JSON.parse(conditionsJson || '[]') : conditionsJson;
        if (!conditions.length) return '';

        return conditions.map(c => 
            `<span class="rmt-condition-badge rmt-condition-${c.type}">${c.type}</span>`
        ).join('');
    },

    renderXPBar(xp, level) {
        const currentLevelXP = this.dnd.xpForLevel(level);
        const nextLevelXP = this.dnd.xpForLevel(Math.min(level + 1, 20));
        const progress = nextLevelXP > currentLevelXP 
            ? Math.round(((xp - currentLevelXP) / (nextLevelXP - currentLevelXP)) * 100) 
            : 100;

        return `
            <div class="rmt-xp-bar">
                <div class="rmt-xp-fill" style="width:${progress}%"></div>
            </div>
            <small style="color:#00a0d2;">${xp.toLocaleString()} / ${nextLevelXP.toLocaleString()} XP (${progress}%)</small>
        `;
    },

    // Ícones de tipo de item
    itemIcon(type) {
        const icons = {
            weapon: '⚔️', armor: '🛡️', potion: '🧪', scroll: '📜',
            wand: '🪄', ring: '💍', wondrous: '✨', tool: '🔧',
            ammo: '🏹', gem: '💎', item: '📦', food: '🍖',
            key: '🔑', container: '🎒', clothing: '👘',
        };
        return icons[type] || '📦';
    },
};

// Disponibilizar globalmente
window.RMT = RMT;
