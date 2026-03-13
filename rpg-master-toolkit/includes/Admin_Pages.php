<?php
/**
 * RPG Master Toolkit - Admin Pages
 * 
 * Páginas administrativas no WP-Admin
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin_Pages {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
    }

    public static function register_menus() {
        // Submenus do CPT principal (rmt_adventure)
        
        add_submenu_page(
            'edit.php?post_type=rmt_adventure',
            'Painel do Mestre',
            '🎯 Painel do Mestre',
            'rmt_run_adventures',
            'rmt-dm-panel',
            array( __CLASS__, 'render_dm_panel' )
        );

        add_submenu_page(
            'edit.php?post_type=rmt_adventure',
            'Sessão Ativa',
            '🔴 Sessão Ativa',
            'rmt_manage_sessions',
            'rmt-active-session',
            array( __CLASS__, 'render_active_session' )
        );

        add_submenu_page(
            'edit.php?post_type=rmt_adventure',
            'Jogadores',
            '👥 Jogadores',
            'rmt_view_all_characters',
            'rmt-players',
            array( __CLASS__, 'render_players_page' )
        );

        // Configurações (só admin)
        add_submenu_page(
            'edit.php?post_type=rmt_adventure',
            'Configurações RPG',
            '⚙️ Configurações',
            'rmt_manage_settings',
            'rmt-settings',
            array( __CLASS__, 'render_settings' )
        );
    }

    /**
     * Painel do Mestre - visão geral
     */
    public static function render_dm_panel() {
        $adventures = Adventure_Manager::get_adventures();
        $active_session = Session_Manager::get_active_session();
        ?>
        <div class="wrap rmt-admin-wrap">
            <h1>🎲 Painel do Mestre</h1>

            <?php if ( $active_session && $active_session->status === 'active' ) : ?>
                <div class="notice notice-success">
                    <p><strong>🔴 Sessão ativa!</strong> 
                    <a href="<?php echo admin_url( 'edit.php?post_type=rmt_adventure&page=rmt-active-session' ); ?>" class="button button-primary">
                        Ir para Sessão Ativa
                    </a></p>
                </div>
            <?php endif; ?>

            <div class="rmt-dashboard-grid">
                <!-- Aventuras disponíveis -->
                <div class="rmt-card">
                    <h2>📚 Aventuras</h2>
                    <?php if ( empty( $adventures ) ) : ?>
                        <p>Nenhuma aventura cadastrada. <a href="<?php echo admin_url( 'post-new.php?post_type=rmt_adventure' ); ?>">Criar primeira aventura</a></p>
                    <?php else : ?>
                        <table class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th>Aventura</th>
                                    <th>Cenas</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $adventures as $adv ) : ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html( $adv['title'] ); ?></strong>
                                            <br><small><?php echo esc_html( $adv['excerpt'] ); ?></small>
                                        </td>
                                        <td><?php echo $adv['scene_count']; ?></td>
                                        <td>
                                            <a href="<?php echo get_edit_post_link( $adv['id'] ); ?>" class="button button-small">Editar</a>
                                            <button class="button button-small button-primary rmt-start-session" 
                                                    data-adventure-id="<?php echo $adv['id']; ?>">
                                                ▶️ Iniciar Sessão
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Personagens ativos -->
                <div class="rmt-card">
                    <h2>👥 Personagens dos Jogadores</h2>
                    <div id="rmt-dm-characters-list">
                        <p>Carregando...</p>
                    </div>
                </div>

                <!-- Links rápidos -->
                <div class="rmt-card">
                    <h2>⚡ Ações Rápidas</h2>
                    <ul class="rmt-quick-links">
                        <li><a href="<?php echo admin_url( 'post-new.php?post_type=rmt_adventure' ); ?>">📕 Nova Aventura</a></li>
                        <li><a href="<?php echo admin_url( 'post-new.php?post_type=rmt_scene' ); ?>">🎬 Nova Cena</a></li>
                        <li><a href="<?php echo admin_url( 'post-new.php?post_type=rmt_npc' ); ?>">👤 Novo NPC</a></li>
                        <li><a href="<?php echo admin_url( 'post-new.php?post_type=rmt_monster' ); ?>">🐉 Novo Monstro</a></li>
                        <li><a href="<?php echo admin_url( 'post-new.php?post_type=rmt_map' ); ?>">🗺️ Novo Mapa</a></li>
                        <li><a href="<?php echo admin_url( 'user-new.php' ); ?>">➕ Adicionar Jogador</a></li>
                    </ul>
                </div>

                <!-- URLs das Interfaces -->
                <div class="rmt-card">
                    <h2>🔗 Links das Interfaces</h2>
                    <table class="form-table">
                        <tr>
                            <th>Painel do Mestre:</th>
                            <td><code><?php echo home_url( '/rpg-master/' ); ?></code></td>
                        </tr>
                        <tr>
                            <th>Painel do Jogador:</th>
                            <td><code><?php echo home_url( '/rpg-player/' ); ?></code></td>
                        </tr>
                        <tr>
                            <th>Tela de Exibição:</th>
                            <td><code><?php echo home_url( '/rpg-display/' ); ?></code></td>
                        </tr>
                    </table>
                    <p class="description">⚠️ Certifique-se de criar páginas com esses slugs e atribuir os templates corretos do tema RPG Master.</p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Página de Sessão Ativa
     */
    public static function render_active_session() {
        ?>
        <div class="wrap rmt-admin-wrap">
            <h1>🔴 Sessão Ativa - Controle do Mestre</h1>
            <div id="rmt-session-app">
                <p>Carregando painel de sessão...</p>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            const app = $('#rmt-session-app');
            
            // Verificar sessão ativa
            function checkSession() {
                $.ajax({
                    url: rmtAdmin.rest_url + 'session/status',
                    headers: { 'X-WP-Nonce': rmtAdmin.nonce },
                    success: function(data) {
                        if (data.status === 'active') {
                            renderSessionPanel(data);
                        } else {
                            app.html(`
                                <div class="notice notice-info">
                                    <p>Nenhuma sessão ativa. Inicie uma sessão pelo <a href="${ajaxurl.replace('admin-ajax.php', 'edit.php?post_type=rmt_adventure&page=rmt-dm-panel')}">Painel do Mestre</a>.</p>
                                </div>
                            `);
                        }
                    }
                });
            }

            function renderSessionPanel(session) {
                app.html(`
                    <div class="rmt-session-controls">
                        <div class="rmt-session-header">
                            <h2>Sessão #${session.id} - ${session.scene_title || 'Sem cena'}</h2>
                            <span class="rmt-badge rmt-badge-${session.scene_type}">${session.scene_type}</span>
                            <button class="button" onclick="pauseSession(${session.id})">⏸️ Pausar</button>
                            <button class="button button-link-delete" onclick="endSession(${session.id})">⏹️ Encerrar</button>
                        </div>
                        <div class="rmt-session-body">
                            <div class="rmt-session-narration">
                                <h3>📖 Narração</h3>
                                <div class="rmt-narration-text">${session.scene_description || 'Nenhum texto de narração.'}</div>
                            </div>
                        </div>
                    </div>
                `);
            }

            checkSession();
        });
        </script>
        <?php
    }

    /**
     * Página de Jogadores
     */
    public static function render_players_page() {
        ?>
        <div class="wrap rmt-admin-wrap">
            <h1>👥 Jogadores e Personagens</h1>
            <div id="rmt-players-list">
                <p>Carregando...</p>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $.ajax({
                url: rmtAdmin.rest_url + 'dm/characters',
                headers: { 'X-WP-Nonce': rmtAdmin.nonce },
                success: function(characters) {
                    let html = '<table class="wp-list-table widefat striped">';
                    html += '<thead><tr><th>Jogador</th><th>Personagem</th><th>Classe</th><th>Nível</th><th>HP</th><th>Condições</th><th>Ações</th></tr></thead><tbody>';
                    
                    if (characters.length === 0) {
                        html += '<tr><td colspan="7">Nenhum personagem cadastrado.</td></tr>';
                    }

                    characters.forEach(c => {
                        const conditions = JSON.parse(c.conditions || '[]');
                        const condText = conditions.map(co => co.type).join(', ') || '—';
                        const hpPercent = c.max_hp > 0 ? Math.round((c.current_hp / c.max_hp) * 100) : 0;
                        const hpColor = hpPercent > 50 ? '#46b450' : hpPercent > 25 ? '#ffb900' : '#dc3232';

                        html += `<tr>
                            <td>${c.player_name || 'N/A'}</td>
                            <td><strong>${c.name}</strong></td>
                            <td>${c.class} (${c.race})</td>
                            <td>${c.level}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:60px;height:8px;background:#ddd;border-radius:4px;">
                                        <div style="width:${hpPercent}%;height:100%;background:${hpColor};border-radius:4px;"></div>
                                    </div>
                                    ${c.current_hp}/${c.max_hp}
                                </div>
                            </td>
                            <td>${condText}</td>
                            <td>
                                <button class="button button-small" onclick="modifyHP(${c.id}, 'heal')">💚 Curar</button>
                                <button class="button button-small" onclick="modifyHP(${c.id}, 'damage')">💔 Dano</button>
                                <button class="button button-small" onclick="grantXP(${c.id})">⭐ XP</button>
                            </td>
                        </tr>`;
                    });

                    html += '</tbody></table>';
                    $('#rmt-players-list').html(html);
                }
            });
        });

        function modifyHP(charId, type) {
            const amount = prompt(type === 'heal' ? 'Quantidade de cura:' : 'Quantidade de dano:');
            if (!amount) return;

            jQuery.ajax({
                url: rmtAdmin.rest_url + 'dm/player/' + charId + '/hp',
                method: 'POST',
                headers: { 'X-WP-Nonce': rmtAdmin.nonce, 'Content-Type': 'application/json' },
                data: JSON.stringify({ amount: parseInt(amount), type: type }),
                success: function() { location.reload(); }
            });
        }

        function grantXP(charId) {
            const xp = prompt('Quantidade de XP:');
            if (!xp) return;

            jQuery.ajax({
                url: rmtAdmin.rest_url + 'dm/player/' + charId + '/xp',
                method: 'POST',
                headers: { 'X-WP-Nonce': rmtAdmin.nonce, 'Content-Type': 'application/json' },
                data: JSON.stringify({ xp: parseInt(xp) }),
                success: function(result) {
                    if (result.leveled_up) {
                        alert('🎉 Level Up! Novo nível: ' + result.new_level);
                    }
                    location.reload();
                }
            });
        }
        </script>
        <?php
    }

    /**
     * Página de Configurações
     */
    public static function render_settings() {
        if ( isset( $_POST['rmt_save_settings'] ) && check_admin_referer( 'rmt_settings' ) ) {
            update_option( 'rmt_display_poll_interval', intval( $_POST['rmt_poll_interval'] ?? 3000 ) );
            update_option( 'rmt_display_theme', sanitize_text_field( $_POST['rmt_display_theme'] ?? 'dark' ) );
            echo '<div class="notice notice-success"><p>Configurações salvas!</p></div>';
        }

        $poll_interval = get_option( 'rmt_display_poll_interval', 3000 );
        $display_theme = get_option( 'rmt_display_theme', 'dark' );
        ?>
        <div class="wrap rmt-admin-wrap">
            <h1>⚙️ Configurações - RPG Master Toolkit</h1>
            
            <form method="post">
                <?php wp_nonce_field( 'rmt_settings' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="rmt_poll_interval">Intervalo de Atualização (Display)</label></th>
                        <td>
                            <input type="number" name="rmt_poll_interval" value="<?php echo esc_attr( $poll_interval ); ?>" min="1000" max="30000" step="500"> ms
                            <p class="description">Tempo entre atualizações da Tela de Exibição (em milissegundos). Padrão: 3000 (3 segundos)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="rmt_display_theme">Tema da Tela de Exibição</label></th>
                        <td>
                            <select name="rmt_display_theme">
                                <option value="dark" <?php selected( $display_theme, 'dark' ); ?>>🌙 Escuro (recomendado)</option>
                                <option value="light" <?php selected( $display_theme, 'light' ); ?>>☀️ Claro</option>
                                <option value="parchment" <?php selected( $display_theme, 'parchment' ); ?>>📜 Pergaminho</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>📋 Informações do Sistema</h2>
                <table class="form-table">
                    <tr>
                        <th>Versão do Plugin</th>
                        <td><?php echo RMT_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th>Versão do Banco</th>
                        <td><?php echo get_option( 'rmt_db_version', 'N/A' ); ?></td>
                    </tr>
                    <tr>
                        <th>REST API Base</th>
                        <td><code><?php echo rest_url( 'rmt/v1/' ); ?></code></td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="rmt_save_settings" class="button button-primary" value="Salvar Configurações">
                </p>
            </form>
        </div>
        <?php
    }
}
