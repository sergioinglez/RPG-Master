<?php
/**
 * Template Name: 🎲 RPG - Painel do Mestre
 * 
 * Interface frontend do Dungeon Master
 */

if ( ! function_exists( 'rmt_check_access' ) || ! rmt_check_access( 'dm' ) ) {
    return;
}

$user = wp_get_current_user();
$rpg_role = rmt_get_user_rpg_role();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Mestre - RPG Master Toolkit</title>
    <?php wp_head(); ?>
</head>
<body class="rmt-page rmt-dm-page <?php echo is_admin_bar_showing() ? 'admin-bar' : ''; ?>">

    <!-- HEADER -->
    <header class="rmt-header">
        <h1>🎲 Painel do Mestre</h1>
        <nav class="rmt-header-nav">
            <a href="#overview" class="rmt-nav-tab active" data-tab="overview">📋 Visão Geral</a>
            <a href="#session" class="rmt-nav-tab" data-tab="session">🔴 Sessão</a>
            <a href="#players" class="rmt-nav-tab" data-tab="players">👥 Jogadores</a>
            <a href="#npcs" class="rmt-nav-tab" data-tab="npcs">👤 NPCs</a>
            <a href="#bestiary" class="rmt-nav-tab" data-tab="bestiary">🐉 Bestiário</a>
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

        <!-- TAB: VISÃO GERAL -->
        <div id="tab-overview" class="rmt-tab-content active">
            <div class="rmt-grid rmt-grid-3" style="margin-bottom:20px;">
                <!-- Card: Sessão Ativa -->
                <div class="rmt-card" id="dm-session-status-card">
                    <div class="rmt-card-header"><i class="fas fa-broadcast-tower"></i> Sessão Ativa</div>
                    <div id="dm-session-status">
                        <div class="rmt-loading"><div class="rmt-spinner"></div></div>
                    </div>
                </div>

                <!-- Card: Aventuras -->
                <div class="rmt-card">
                    <div class="rmt-card-header"><i class="fas fa-book-open"></i> Aventuras</div>
                    <div id="dm-adventures-list">
                        <div class="rmt-loading"><div class="rmt-spinner"></div></div>
                    </div>
                </div>

                <!-- Card: Ações Rápidas -->
                <div class="rmt-card">
                    <div class="rmt-card-header"><i class="fas fa-bolt"></i> Ações Rápidas</div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <a href="<?php echo admin_url('post-new.php?post_type=rmt_adventure'); ?>" class="rmt-btn rmt-btn-primary" target="_blank">
                            <i class="fas fa-plus"></i> Nova Aventura
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=rmt_scene'); ?>" class="rmt-btn rmt-btn-primary" target="_blank">
                            <i class="fas fa-film"></i> Nova Cena
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=rmt_npc'); ?>" class="rmt-btn rmt-btn-primary" target="_blank">
                            <i class="fas fa-user-plus"></i> Novo NPC
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=rmt_monster'); ?>" class="rmt-btn rmt-btn-primary" target="_blank">
                            <i class="fas fa-dragon"></i> Novo Monstro
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=rmt_map'); ?>" class="rmt-btn rmt-btn-primary" target="_blank">
                            <i class="fas fa-map"></i> Novo Mapa
                        </a>
                        <hr style="border-color:rgba(255,255,255,0.1);">
                        <a href="<?php echo home_url('/rpg-display/'); ?>" class="rmt-btn rmt-btn-gold" target="_blank">
                            <i class="fas fa-tv"></i> Abrir Tela de Exibição
                        </a>
                    </div>
                </div>
            </div>

            <!-- Personagens dos Jogadores (visão geral) -->
            <div class="rmt-card">
                <div class="rmt-card-header"><i class="fas fa-users"></i> Personagens dos Jogadores</div>
                <div id="dm-characters-overview">
                    <div class="rmt-loading"><div class="rmt-spinner"></div></div>
                </div>
            </div>
        </div>

        <!-- TAB: SESSÃO ATIVA -->
        <div id="tab-session" class="rmt-tab-content" style="display:none;">
            <div id="dm-session-panel">
                <div class="rmt-loading"><div class="rmt-spinner"></div></div>
            </div>
        </div>

        <!-- TAB: JOGADORES -->
        <div id="tab-players" class="rmt-tab-content" style="display:none;">
            <div class="rmt-card">
                <div class="rmt-card-header"><i class="fas fa-users-cog"></i> Controle de Jogadores</div>
                <div id="dm-players-control">
                    <div class="rmt-loading"><div class="rmt-spinner"></div></div>
                </div>
            </div>
        </div>

        <!-- TAB: NPCs -->
        <div id="tab-npcs" class="rmt-tab-content" style="display:none;">
            <div class="rmt-card">
                <div class="rmt-card-header"><i class="fas fa-theater-masks"></i> NPCs da Aventura</div>
                <div id="dm-npcs-list">
                    <p style="color:#aaa;">Selecione uma aventura ativa para ver os NPCs.</p>
                </div>
            </div>
        </div>

        <!-- TAB: BESTIÁRIO -->
        <div id="tab-bestiary" class="rmt-tab-content" style="display:none;">
            <div class="rmt-card">
                <div class="rmt-card-header"><i class="fas fa-skull-crossbones"></i> Bestiário</div>
                <div id="dm-bestiary">
                    <p style="color:#aaa;">Selecione uma aventura ativa para ver os monstros.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- DICE ROLLER FLOATING -->
    <div id="rmt-dice-roller" class="rmt-dice-roller">
        <button class="rmt-dice-toggle" onclick="toggleDiceRoller()">🎲</button>
        <div class="rmt-dice-panel" style="display:none;">
            <h3>🎲 Rolagem de Dados</h3>
            <div class="rmt-dice-buttons">
                <button onclick="rollDice('1d4')">d4</button>
                <button onclick="rollDice('1d6')">d6</button>
                <button onclick="rollDice('1d8')">d8</button>
                <button onclick="rollDice('1d10')">d10</button>
                <button onclick="rollDice('1d12')">d12</button>
                <button onclick="rollDice('1d20')">d20</button>
                <button onclick="rollDice('1d100')">d100</button>
            </div>
            <div class="rmt-dice-custom">
                <input type="text" id="dice-custom" placeholder="2d6+3" style="width:100px;">
                <button onclick="rollDice(document.getElementById('dice-custom').value)">Rolar</button>
            </div>
            <div id="dice-result" class="rmt-dice-result"></div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
