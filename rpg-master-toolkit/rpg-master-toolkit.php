<?php
/**
 * Plugin Name: RPG Master Toolkit
 * Plugin URI: https://seu-dominio.com/rpg-master-toolkit
 * Description: Ferramenta completa de apoio para mestres e jogadores de RPG (D&D 5e). Gestão de aventuras, personagens, NPCs, mapas e sessões em tempo real.
 * Version: 1.0.0-MVP
 * Author: RPG Master Dev
 * Author URI: https://seu-dominio.com
 * Text Domain: rpg-master-toolkit
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================
// CONSTANTES DO PLUGIN
// ============================================================
define( 'RMT_VERSION', '1.0.0' );
define( 'RMT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RMT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RMT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'RMT_DB_VERSION', '1.0.0' );

// ============================================================
// AUTOLOAD DE CLASSES
// ============================================================
spl_autoload_register( function( $class ) {
    $prefix = 'RMT\\';
    $base_dir = RMT_PLUGIN_DIR . 'includes/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
});

// ============================================================
// ATIVAÇÃO DO PLUGIN
// ============================================================
register_activation_hook( __FILE__, 'rmt_activate_plugin' );

function rmt_activate_plugin() {
    // Criar tabelas no banco
    rmt_create_tables();
    
    // Configurar roles e capabilities
    RMT\Roles::setup_roles();
    
    // Flush rewrite rules para CPTs
    flush_rewrite_rules();
    
    // Salvar versão do DB
    update_option( 'rmt_db_version', RMT_DB_VERSION );
    update_option( 'rmt_version', RMT_VERSION );
}

// ============================================================
// DESATIVAÇÃO DO PLUGIN
// ============================================================
register_deactivation_hook( __FILE__, 'rmt_deactivate_plugin' );

function rmt_deactivate_plugin() {
    flush_rewrite_rules();
}

// ============================================================
// CRIAÇÃO DE TABELAS CUSTOMIZADAS
// ============================================================
function rmt_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // -------------------------------------------
    // TABELA: Personagens dos Jogadores
    // -------------------------------------------
    $table_characters = $wpdb->prefix . 'rmt_characters';
    $sql_characters = "CREATE TABLE $table_characters (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        adventure_id bigint(20) UNSIGNED DEFAULT 0,
        name varchar(255) NOT NULL,
        race varchar(100) NOT NULL DEFAULT '',
        subrace varchar(100) DEFAULT '',
        class varchar(100) NOT NULL DEFAULT '',
        subclass varchar(100) DEFAULT '',
        level int(3) NOT NULL DEFAULT 1,
        experience_points bigint(20) NOT NULL DEFAULT 0,
        background varchar(100) DEFAULT '',
        alignment varchar(50) DEFAULT '',
        
        -- Atributos base (ability scores)
        strength int(3) NOT NULL DEFAULT 10,
        dexterity int(3) NOT NULL DEFAULT 10,
        constitution int(3) NOT NULL DEFAULT 10,
        intelligence int(3) NOT NULL DEFAULT 10,
        wisdom int(3) NOT NULL DEFAULT 10,
        charisma int(3) NOT NULL DEFAULT 10,

        -- Combate
        max_hp int(5) NOT NULL DEFAULT 0,
        current_hp int(5) NOT NULL DEFAULT 0,
        temp_hp int(5) NOT NULL DEFAULT 0,
        armor_class int(3) NOT NULL DEFAULT 10,
        initiative_bonus int(3) NOT NULL DEFAULT 0,
        speed int(3) NOT NULL DEFAULT 30,
        hit_dice varchar(20) DEFAULT '',
        hit_dice_remaining int(3) DEFAULT 0,
        death_saves_success int(1) DEFAULT 0,
        death_saves_failure int(1) DEFAULT 0,

        -- Proficiência
        proficiency_bonus int(2) NOT NULL DEFAULT 2,
        
        -- Saving Throws proficiency (1 = proficiente)
        save_str tinyint(1) DEFAULT 0,
        save_dex tinyint(1) DEFAULT 0,
        save_con tinyint(1) DEFAULT 0,
        save_int tinyint(1) DEFAULT 0,
        save_wis tinyint(1) DEFAULT 0,
        save_cha tinyint(1) DEFAULT 0,

        -- Skills proficiency (0=não, 1=proficiente, 2=expertise)
        skill_acrobatics tinyint(1) DEFAULT 0,
        skill_animal_handling tinyint(1) DEFAULT 0,
        skill_arcana tinyint(1) DEFAULT 0,
        skill_athletics tinyint(1) DEFAULT 0,
        skill_deception tinyint(1) DEFAULT 0,
        skill_history tinyint(1) DEFAULT 0,
        skill_insight tinyint(1) DEFAULT 0,
        skill_intimidation tinyint(1) DEFAULT 0,
        skill_investigation tinyint(1) DEFAULT 0,
        skill_medicine tinyint(1) DEFAULT 0,
        skill_nature tinyint(1) DEFAULT 0,
        skill_perception tinyint(1) DEFAULT 0,
        skill_performance tinyint(1) DEFAULT 0,
        skill_persuasion tinyint(1) DEFAULT 0,
        skill_religion tinyint(1) DEFAULT 0,
        skill_sleight_of_hand tinyint(1) DEFAULT 0,
        skill_stealth tinyint(1) DEFAULT 0,
        skill_survival tinyint(1) DEFAULT 0,

        -- Recursos
        gold decimal(10,2) DEFAULT 0,
        silver decimal(10,2) DEFAULT 0,
        copper decimal(10,2) DEFAULT 0,
        electrum decimal(10,2) DEFAULT 0,
        platinum decimal(10,2) DEFAULT 0,

        -- Aparência e RP
        avatar_url text DEFAULT '',
        age varchar(50) DEFAULT '',
        height varchar(50) DEFAULT '',
        weight varchar(50) DEFAULT '',
        eyes varchar(50) DEFAULT '',
        skin varchar(50) DEFAULT '',
        hair varchar(50) DEFAULT '',
        personality_traits text DEFAULT '',
        ideals text DEFAULT '',
        bonds text DEFAULT '',
        flaws text DEFAULT '',
        backstory text DEFAULT '',
        appearance_notes text DEFAULT '',

        -- Dados extras em JSON
        features_traits longtext DEFAULT '',
        proficiencies_languages longtext DEFAULT '',
        spell_data longtext DEFAULT '',
        extra_data longtext DEFAULT '',

        -- Condições ativas (JSON: [{type, duration, source}])
        conditions longtext DEFAULT '',

        -- Status
        is_active tinyint(1) DEFAULT 1,
        is_dead tinyint(1) DEFAULT 0,
        
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY adventure_id (adventure_id),
        KEY is_active (is_active)
    ) $charset_collate;";

    dbDelta( $sql_characters );

    // -------------------------------------------
    // TABELA: Inventário dos Personagens
    // -------------------------------------------
    $table_inventory = $wpdb->prefix . 'rmt_inventory';
    $sql_inventory = "CREATE TABLE $table_inventory (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        character_id bigint(20) UNSIGNED NOT NULL,
        name varchar(255) NOT NULL,
        type varchar(50) DEFAULT 'item',
        subtype varchar(50) DEFAULT '',
        description text DEFAULT '',
        quantity int(5) DEFAULT 1,
        weight decimal(8,2) DEFAULT 0,
        value_gp decimal(10,2) DEFAULT 0,
        rarity varchar(50) DEFAULT 'common',
        is_equipped tinyint(1) DEFAULT 0,
        is_attuned tinyint(1) DEFAULT 0,
        requires_attunement tinyint(1) DEFAULT 0,
        
        -- Para armas
        damage_dice varchar(20) DEFAULT '',
        damage_type varchar(50) DEFAULT '',
        weapon_properties varchar(255) DEFAULT '',
        weapon_range varchar(50) DEFAULT '',
        
        -- Para armaduras
        armor_ac_base int(3) DEFAULT 0,
        armor_type varchar(50) DEFAULT '',
        stealth_disadvantage tinyint(1) DEFAULT 0,
        str_requirement int(3) DEFAULT 0,
        
        -- Propriedades mágicas (JSON)
        magic_properties longtext DEFAULT '',
        
        -- Notas extras
        notes text DEFAULT '',
        
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (id),
        KEY character_id (character_id),
        KEY type (type),
        KEY is_equipped (is_equipped)
    ) $charset_collate;";

    dbDelta( $sql_inventory );

    // -------------------------------------------
    // TABELA: Conquistas dos Jogadores
    // -------------------------------------------
    $table_achievements = $wpdb->prefix . 'rmt_achievements';
    $sql_achievements = "CREATE TABLE $table_achievements (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        character_id bigint(20) UNSIGNED NOT NULL,
        adventure_id bigint(20) UNSIGNED DEFAULT 0,
        title varchar(255) NOT NULL,
        description text DEFAULT '',
        icon_url text DEFAULT '',
        type varchar(50) DEFAULT 'general',
        xp_reward bigint(20) DEFAULT 0,
        earned_at datetime DEFAULT CURRENT_TIMESTAMP,
        
        PRIMARY KEY (id),
        KEY character_id (character_id),
        KEY adventure_id (adventure_id)
    ) $charset_collate;";

    dbDelta( $sql_achievements );

    // -------------------------------------------
    // TABELA: Sessão Ativa (estado em tempo real)
    // -------------------------------------------
    $table_session = $wpdb->prefix . 'rmt_active_session';
    $sql_session = "CREATE TABLE $table_session (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        adventure_id bigint(20) UNSIGNED NOT NULL,
        dm_user_id bigint(20) UNSIGNED NOT NULL,
        current_scene_id bigint(20) UNSIGNED DEFAULT 0,
        current_map_id bigint(20) UNSIGNED DEFAULT 0,
        
        -- Estado da cena
        scene_type varchar(50) DEFAULT 'exploration',
        scene_title varchar(255) DEFAULT '',
        scene_description text DEFAULT '',
        scene_image_url text DEFAULT '',
        
        -- Dados de combate ativo (JSON)
        combat_data longtext DEFAULT '',
        
        -- NPC em interação
        active_npc_id bigint(20) UNSIGNED DEFAULT 0,
        
        -- Posições dos personagens no mapa (JSON: [{char_id, x, y}])
        character_positions longtext DEFAULT '',
        
        -- Fog of war / visibilidade (JSON)
        visibility_data longtext DEFAULT '',
        
        -- Status da sessão
        status varchar(20) DEFAULT 'paused',
        
        -- Log de ações (JSON)
        action_log longtext DEFAULT '',
        
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (id),
        KEY adventure_id (adventure_id),
        KEY dm_user_id (dm_user_id),
        KEY status (status)
    ) $charset_collate;";

    dbDelta( $sql_session );

    // -------------------------------------------
    // TABELA: Controle do Mestre sobre jogadores
    // -------------------------------------------
    $table_dm_controls = $wpdb->prefix . 'rmt_dm_player_controls';
    $sql_dm_controls = "CREATE TABLE $table_dm_controls (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        session_id bigint(20) UNSIGNED NOT NULL,
        character_id bigint(20) UNSIGNED NOT NULL,
        
        -- Condições aplicadas pelo mestre
        conditions_applied longtext DEFAULT '',
        
        -- Modificadores temporários (JSON)
        temp_modifiers longtext DEFAULT '',
        
        -- Notas do mestre sobre o jogador
        dm_notes text DEFAULT '',
        
        -- XP concedido nessa sessão
        session_xp_granted bigint(20) DEFAULT 0,
        
        -- Inspiração
        has_inspiration tinyint(1) DEFAULT 0,
        
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY character_id (character_id)
    ) $charset_collate;";

    dbDelta( $sql_dm_controls );
}

// ============================================================
// INICIALIZAÇÃO DO PLUGIN
// ============================================================
add_action( 'plugins_loaded', 'rmt_init_plugin' );

function rmt_init_plugin() {
    // Verificar se precisa atualizar DB
    if ( get_option( 'rmt_db_version' ) !== RMT_DB_VERSION ) {
        rmt_create_tables();
        update_option( 'rmt_db_version', RMT_DB_VERSION );
    }

    // Carregar componentes
    require_once RMT_PLUGIN_DIR . 'includes/Roles.php';
    require_once RMT_PLUGIN_DIR . 'includes/PostTypes.php';
    require_once RMT_PLUGIN_DIR . 'includes/DnD5e_Rules.php';
    require_once RMT_PLUGIN_DIR . 'includes/Character_Manager.php';
    require_once RMT_PLUGIN_DIR . 'includes/Adventure_Manager.php';
    require_once RMT_PLUGIN_DIR . 'includes/Session_Manager.php';
    require_once RMT_PLUGIN_DIR . 'includes/REST_API.php';
    require_once RMT_PLUGIN_DIR . 'includes/Admin_Pages.php';

    // Inicializar componentes
    RMT\PostTypes::init();
    RMT\REST_API::init();
    RMT\Admin_Pages::init();
}

// ============================================================
// ENQUEUE SCRIPTS E STYLES (ADMIN)
// ============================================================
add_action( 'admin_enqueue_scripts', 'rmt_admin_enqueue' );

function rmt_admin_enqueue( $hook ) {
    // Somente nas páginas do plugin
    if ( strpos( $hook, 'rmt-' ) === false && get_post_type() !== 'rmt_adventure' ) {
        return;
    }

    wp_enqueue_media();
    
    wp_enqueue_style(
        'rmt-admin-style',
        RMT_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        RMT_VERSION
    );

    wp_enqueue_script(
        'rmt-admin-script',
        RMT_PLUGIN_URL . 'assets/js/admin.js',
        array( 'jquery', 'wp-api-fetch' ),
        RMT_VERSION,
        true
    );

    wp_localize_script( 'rmt-admin-script', 'rmtAdmin', array(
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'rest_url'  => rest_url( 'rmt/v1/' ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'plugin_url'=> RMT_PLUGIN_URL,
    ));
}

// ============================================================
// ENQUEUE SCRIPTS E STYLES (FRONTEND)
// ============================================================
add_action( 'wp_enqueue_scripts', 'rmt_frontend_enqueue' );

function rmt_frontend_enqueue() {
    // Somente nas páginas do plugin/tema RPG
    if ( ! rmt_is_rpg_page() ) {
        return;
    }

    wp_enqueue_style(
        'rmt-frontend-style',
        RMT_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        RMT_VERSION
    );

    wp_enqueue_script(
        'rmt-frontend-script',
        RMT_PLUGIN_URL . 'assets/js/frontend.js',
        array( 'jquery', 'wp-api-fetch' ),
        RMT_VERSION,
        true
    );

    wp_localize_script( 'rmt-frontend-script', 'rmtFront', array(
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'rest_url'  => rest_url( 'rmt/v1/' ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'user_id'   => get_current_user_id(),
        'user_role' => rmt_get_user_rpg_role(),
        'plugin_url'=> RMT_PLUGIN_URL,
    ));
}

// ============================================================
// HELPERS
// ============================================================

/**
 * Verifica se estamos em uma página do RPG
 */
function rmt_is_rpg_page() {
    if ( is_page( array( 'rpg-master', 'rpg-player', 'rpg-display', 'rpg-table' ) ) ) {
        return true;
    }
    // Verificar por template
    if ( is_page_template( array( 
        'template-dm-dashboard.php', 
        'template-player-dashboard.php', 
        'template-display.php' 
    ) ) ) {
        return true;
    }
    return false;
}

/**
 * Retorna o papel RPG do usuário
 */
function rmt_get_user_rpg_role() {
    if ( ! is_user_logged_in() ) {
        return 'visitor';
    }
    
    $user = wp_get_current_user();
    
    if ( in_array( 'administrator', $user->roles ) ) {
        return 'admin';
    }
    if ( array_intersect( array( 'editor', 'author' ), $user->roles ) ) {
        return 'dm';
    }
    if ( array_intersect( array( 'subscriber', 'contributor' ), $user->roles ) ) {
        return 'player';
    }
    
    return 'visitor';
}

/**
 * Verifica se o usuário pode mestrar
 */
function rmt_user_can_dm() {
    $role = rmt_get_user_rpg_role();
    return in_array( $role, array( 'admin', 'dm' ) );
}

/**
 * Verifica se o usuário pode jogar
 */
function rmt_user_can_play() {
    $role = rmt_get_user_rpg_role();
    return in_array( $role, array( 'admin', 'dm', 'player' ) );
}

/**
 * Verifica se é administrador
 */
function rmt_user_is_admin() {
    return rmt_get_user_rpg_role() === 'admin';
}
