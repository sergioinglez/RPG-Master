<?php
/**
 * Template Name: 🎮 RPG - Painel do Jogador
 * 
 * Interface frontend do Jogador - Criação/gestão de personagens, ficha, inventário, conquistas
 */

if ( ! function_exists( 'rmt_check_access' ) || ! rmt_check_access( 'player' ) ) {
    return;
}

$user = wp_get_current_user();
$rpg_role = rmt_get_user_rpg_role();
$can_dm = in_array( $rpg_role, array( 'dm', 'admin' ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Jogador - RPG Master Toolkit</title>
    <?php wp_head(); ?>
</head>
<body class="rmt-page rmt-player-page <?php echo is_admin_bar_showing() ? 'admin-bar' : ''; ?>">

    <!-- HEADER -->
    <header class="rmt-header">
        <h1>⚔️ Painel do Aventureiro</h1>
        <nav class="rmt-header-nav">
            <a href="#characters" class="rmt-nav-tab active" data-tab="characters">🧙 Personagens</a>
            <a href="#sheet" class="rmt-nav-tab" data-tab="sheet">📋 Ficha</a>
            <a href="#inventory" class="rmt-nav-tab" data-tab="inventory">🎒 Inventário</a>
            <a href="#achievements" class="rmt-nav-tab" data-tab="achievements">🏆 Conquistas</a>
            <?php if ( $can_dm ) : ?>
                <a href="<?php echo home_url('/rpg-master/'); ?>" style="background:var(--rmt-secondary);color:#000;">
                    🎲 Modo Mestre
                </a>
            <?php endif; ?>
            <span style="color:#aaa;font-size:13px;">
                <i class="fas fa-user"></i> <?php echo esc_html( $user->display_name ); ?>
            </span>
            <a href="<?php echo wp_logout_url( home_url() ); ?>" style="border-color:#dc3232;color:#dc3232;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </nav>
    </header>

    <!-- MAIN CONTENT -->
    <main class="rmt-container">

        <!-- TAB: PERSONAGENS (lista + criação) -->
        <div id="tab-characters" class="rmt-tab-content active">
            <div class="rmt-card" style="margin-bottom:20px;">
                <div class="rmt-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <span><i class="fas fa-hat-wizard"></i> Meus Personagens</span>
                    <button class="rmt-btn rmt-btn-gold rmt-btn-sm" onclick="showCreateCharacter()">
                        <i class="fas fa-plus"></i> Novo Personagem
                    </button>
                </div>
                <div id="player-characters-list">
                    <div class="rmt-loading"><div class="rmt-spinner"></div></div>
                </div>
            </div>

            <!-- Modal de Criação de Personagem -->
            <div id="create-character-modal" class="rmt-modal" style="display:none;">
                <div class="rmt-modal-content">
                    <div class="rmt-modal-header">
                        <h2>✨ Criar Novo Personagem</h2>
                        <button class="rmt-modal-close" onclick="hideCreateCharacter()">&times;</button>
                    </div>
                    <div class="rmt-modal-body">
                        <form id="create-character-form">
                            <!-- Step 1: Básico -->
                            <div class="rmt-create-step" id="step-1">
                                <h3>📝 Informações Básicas</h3>
                                <div class="rmt-grid rmt-grid-2">
                                    <div class="rmt-form-group">
                                        <label for="char-name">Nome do Personagem</label>
                                        <input type="text" id="char-name" required placeholder="Ex: Thorin Escudo de Carvalho">
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-race">Raça</label>
                                        <select id="char-race" required>
                                            <option value="">— Selecione —</option>
                                        </select>
                                    </div>
                                    <div class="rmt-form-group" id="subrace-group" style="display:none;">
                                        <label for="char-subrace">Sub-raça</label>
                                        <select id="char-subrace">
                                            <option value="">— Nenhuma —</option>
                                        </select>
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-class">Classe</label>
                                        <select id="char-class" required>
                                            <option value="">— Selecione —</option>
                                        </select>
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-background">Antecedente</label>
                                        <select id="char-background">
                                            <option value="">— Selecione —</option>
                                        </select>
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-alignment">Alinhamento</label>
                                        <select id="char-alignment">
                                            <option value="">— Selecione —</option>
                                            <option value="LG">Leal e Bom</option>
                                            <option value="NG">Neutro e Bom</option>
                                            <option value="CG">Caótico e Bom</option>
                                            <option value="LN">Leal e Neutro</option>
                                            <option value="TN">Verdadeiro Neutro</option>
                                            <option value="CN">Caótico e Neutro</option>
                                            <option value="LE">Leal e Mau</option>
                                            <option value="NE">Neutro e Mau</option>
                                            <option value="CE">Caótico e Mau</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="button" class="rmt-btn rmt-btn-gold" onclick="goToStep(2)">
                                    Próximo: Atributos <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>

                            <!-- Step 2: Atributos -->
                            <div class="rmt-create-step" id="step-2" style="display:none;">
                                <h3>💪 Atributos (Ability Scores)</h3>
                                <p style="color:#aaa;font-size:13px;">Distribua os valores. Método padrão: 15, 14, 13, 12, 10, 8 (ou role 4d6 descartando o menor)</p>
                                
                                <div class="rmt-stat-block" id="ability-inputs">
                                    <div class="rmt-stat-item">
                                        <div class="rmt-stat-label">FOR</div>
                                        <input type="number" id="attr-str" min="1" max="20" value="10" class="rmt-attr-input">
                                        <div class="rmt-stat-mod" id="mod-str">+0</div>
                                    </div>
                                    <div class="rmt-stat-item">
                                        <div class="rmt-stat-label">DES</div>
                                        <input type="number" id="attr-dex" min="1" max="20" value="10" class="rmt-attr-input">
                                        <div class="rmt-stat-mod" id="mod-dex">+0</div>
                                    </div>
                                    <div class="rmt-stat-item">
                                        <div class="rmt-stat-label">CON</div>
                                        <input type="number" id="attr-con" min="1" max="20" value="10" class="rmt-attr-input">
                                        <div class="rmt-stat-mod" id="mod-con">+0</div>
                                    </div>
                                    <div class="rmt-stat-item">
                                        <div class="rmt-stat-label">INT</div>
                                        <input type="number" id="attr-int" min="1" max="20" value="10" class="rmt-attr-input">
                                        <div class="rmt-stat-mod" id="mod-int">+0</div>
                                    </div>
                                    <div class="rmt-stat-item">
                                        <div class="rmt-stat-label">SAB</div>
                                        <input type="number" id="attr-wis" min="1" max="20" value="10" class="rmt-attr-input">
                                        <div class="rmt-stat-mod" id="mod-wis">+0</div>
                                    </div>
                                    <div class="rmt-stat-item">
                                        <div class="rmt-stat-label">CAR</div>
                                        <input type="number" id="attr-cha" min="1" max="20" value="10" class="rmt-attr-input">
                                        <div class="rmt-stat-mod" id="mod-cha">+0</div>
                                    </div>
                                </div>
                                
                                <div id="racial-bonuses-info" style="margin:15px 0;padding:10px;background:rgba(219,166,23,0.1);border-radius:8px;display:none;"></div>

                                <div style="display:flex;gap:10px;margin-top:20px;">
                                    <button type="button" class="rmt-btn rmt-btn-primary" onclick="goToStep(1)">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </button>
                                    <button type="button" class="rmt-btn rmt-btn-gold" onclick="goToStep(3)">
                                        Próximo: Personalidade <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Step 3: Personalidade e Backstory -->
                            <div class="rmt-create-step" id="step-3" style="display:none;">
                                <h3>📖 Personalidade e História</h3>
                                <div class="rmt-grid rmt-grid-2">
                                    <div class="rmt-form-group">
                                        <label for="char-personality">Traços de Personalidade</label>
                                        <textarea id="char-personality" rows="3" placeholder="Descreva 2 traços de personalidade..."></textarea>
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-ideals">Ideais</label>
                                        <textarea id="char-ideals" rows="3" placeholder="O que motiva seu personagem..."></textarea>
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-bonds">Vínculos</label>
                                        <textarea id="char-bonds" rows="3" placeholder="Pessoas, lugares ou coisas importantes..."></textarea>
                                    </div>
                                    <div class="rmt-form-group">
                                        <label for="char-flaws">Defeitos</label>
                                        <textarea id="char-flaws" rows="3" placeholder="Fraquezas ou vícios do personagem..."></textarea>
                                    </div>
                                </div>
                                <div class="rmt-form-group">
                                    <label for="char-backstory">História de Fundo</label>
                                    <textarea id="char-backstory" rows="5" placeholder="Conte a história do seu personagem..."></textarea>
                                </div>

                                <div style="display:flex;gap:10px;margin-top:20px;">
                                    <button type="button" class="rmt-btn rmt-btn-primary" onclick="goToStep(2)">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </button>
                                    <button type="submit" class="rmt-btn rmt-btn-gold" id="btn-create-char">
                                        <i class="fas fa-magic"></i> Criar Personagem!
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: FICHA DO PERSONAGEM -->
        <div id="tab-sheet" class="rmt-tab-content" style="display:none;">
            <div id="player-character-sheet">
                <div class="rmt-card" style="text-align:center;padding:40px;">
                    <i class="fas fa-arrow-left" style="font-size:24px;color:var(--rmt-secondary);"></i>
                    <p>Selecione um personagem na aba "Personagens" para ver sua ficha.</p>
                </div>
            </div>
        </div>

        <!-- TAB: INVENTÁRIO -->
        <div id="tab-inventory" class="rmt-tab-content" style="display:none;">
            <div class="rmt-card">
                <div class="rmt-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <span><i class="fas fa-box-open"></i> Inventário</span>
                    <button class="rmt-btn rmt-btn-gold rmt-btn-sm" onclick="showAddItem()" id="btn-add-item" style="display:none;">
                        <i class="fas fa-plus"></i> Adicionar Item
                    </button>
                </div>
                <div id="player-inventory">
                    <p style="color:#aaa;">Selecione um personagem primeiro.</p>
                </div>
            </div>

            <!-- Modal Adicionar Item -->
            <div id="add-item-modal" class="rmt-modal" style="display:none;">
                <div class="rmt-modal-content" style="max-width:500px;">
                    <div class="rmt-modal-header">
                        <h2>📦 Adicionar Item</h2>
                        <button class="rmt-modal-close" onclick="hideAddItem()">&times;</button>
                    </div>
                    <div class="rmt-modal-body">
                        <form id="add-item-form">
                            <div class="rmt-form-group">
                                <label>Nome do Item</label>
                                <input type="text" id="item-name" required>
                            </div>
                            <div class="rmt-grid rmt-grid-2">
                                <div class="rmt-form-group">
                                    <label>Tipo</label>
                                    <select id="item-type">
                                        <option value="item">📦 Item</option>
                                        <option value="weapon">⚔️ Arma</option>
                                        <option value="armor">🛡️ Armadura</option>
                                        <option value="potion">🧪 Poção</option>
                                        <option value="scroll">📜 Pergaminho</option>
                                        <option value="wand">🪄 Varinha</option>
                                        <option value="ring">💍 Anel</option>
                                        <option value="wondrous">✨ Maravilhoso</option>
                                        <option value="tool">🔧 Ferramenta</option>
                                        <option value="ammo">🏹 Munição</option>
                                        <option value="gem">💎 Gema</option>
                                        <option value="food">🍖 Comida</option>
                                    </select>
                                </div>
                                <div class="rmt-form-group">
                                    <label>Raridade</label>
                                    <select id="item-rarity">
                                        <option value="common">Comum</option>
                                        <option value="uncommon">Incomum</option>
                                        <option value="rare">Raro</option>
                                        <option value="very_rare">Muito Raro</option>
                                        <option value="legendary">Lendário</option>
                                        <option value="artifact">Artefato</option>
                                    </select>
                                </div>
                                <div class="rmt-form-group">
                                    <label>Quantidade</label>
                                    <input type="number" id="item-qty" value="1" min="1">
                                </div>
                                <div class="rmt-form-group">
                                    <label>Peso (lb)</label>
                                    <input type="number" id="item-weight" value="0" min="0" step="0.1">
                                </div>
                            </div>
                            <div class="rmt-form-group">
                                <label>Descrição</label>
                                <textarea id="item-desc" rows="3"></textarea>
                            </div>
                            <button type="submit" class="rmt-btn rmt-btn-gold" style="width:100%;">
                                <i class="fas fa-plus"></i> Adicionar ao Inventário
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: CONQUISTAS -->
        <div id="tab-achievements" class="rmt-tab-content" style="display:none;">
            <div class="rmt-card">
                <div class="rmt-card-header"><i class="fas fa-trophy"></i> Conquistas</div>
                <div id="player-achievements">
                    <p style="color:#aaa;">Selecione um personagem para ver suas conquistas.</p>
                </div>
            </div>
        </div>

    </main>

    <?php wp_footer(); ?>
</body>
</html>
