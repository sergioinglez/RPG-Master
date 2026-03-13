<?php
/**
 * RPG Master Theme - Functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================
// SETUP DO TEMA
// =============================================
add_action( 'after_setup_theme', 'rmt_theme_setup' );

function rmt_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    add_theme_support( 'custom-logo' );
}

// =============================================
// ENQUEUE STYLES E SCRIPTS DO TEMA
// =============================================
add_action( 'wp_enqueue_scripts', 'rmt_theme_enqueue' );

function rmt_theme_enqueue() {
    // Google Fonts
    wp_enqueue_style( 'rmt-google-fonts', 
        'https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Nunito:wght@300;400;600;700&display=swap', 
        array(), null );

    // Font Awesome
    wp_enqueue_style( 'font-awesome', 
        'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css', 
        array(), '6.4.0' );

    // Tema base
    wp_enqueue_style( 'rmt-theme-style', get_stylesheet_uri(), array(), '1.0.0' );

    // Scripts específicos por template
    if ( is_page_template( 'template-dm-dashboard.php' ) ) {
        wp_enqueue_style( 'rmt-dm-style', get_template_directory_uri() . '/assets/css/dm-dashboard.css', array(), '1.0.0' );
        wp_enqueue_script( 'rmt-dm-script', get_template_directory_uri() . '/assets/js/dm-dashboard.js', array( 'jquery' ), '1.0.0', true );
    }

    if ( is_page_template( 'template-player-dashboard.php' ) ) {
        wp_enqueue_style( 'rmt-player-style', get_template_directory_uri() . '/assets/css/player-dashboard.css', array(), '1.0.0' );
        wp_enqueue_script( 'rmt-player-script', get_template_directory_uri() . '/assets/js/player-dashboard.js', array( 'jquery' ), '1.0.0', true );
    }

    if ( is_page_template( 'template-display.php' ) ) {
        wp_enqueue_style( 'rmt-display-style', get_template_directory_uri() . '/assets/css/display.css', array(), '1.0.0' );
        wp_enqueue_script( 'rmt-display-script', get_template_directory_uri() . '/assets/js/display.js', array( 'jquery' ), '1.0.0', true );
    }
}

// =============================================
// REGISTRAR PAGE TEMPLATES
// =============================================
add_filter( 'theme_page_templates', 'rmt_register_templates' );

function rmt_register_templates( $templates ) {
    $templates['template-dm-dashboard.php']     = '🎲 RPG - Painel do Mestre';
    $templates['template-player-dashboard.php']  = '🎮 RPG - Painel do Jogador';
    $templates['template-display.php']           = '📺 RPG - Tela de Exibição';
    return $templates;
}

// =============================================
// REDIRECIONAR TEMPLATES
// =============================================
add_filter( 'template_include', 'rmt_load_templates' );

function rmt_load_templates( $template ) {
    if ( is_page() ) {
        $page_template = get_page_template_slug();
        
        $custom_templates = array(
            'template-dm-dashboard.php',
            'template-player-dashboard.php',
            'template-display.php',
        );

        if ( in_array( $page_template, $custom_templates ) ) {
            $file = get_template_directory() . '/' . $page_template;
            if ( file_exists( $file ) ) {
                return $file;
            }
        }
    }
    return $template;
}

// =============================================
// CRIAR PÁGINAS AUTOMATICAMENTE NA ATIVAÇÃO
// =============================================
add_action( 'after_switch_theme', 'rmt_create_default_pages' );

function rmt_create_default_pages() {
    $pages = array(
        array(
            'title'    => 'RPG Master',
            'slug'     => 'rpg-master',
            'template' => 'template-dm-dashboard.php',
        ),
        array(
            'title'    => 'RPG Player',
            'slug'     => 'rpg-player',
            'template' => 'template-player-dashboard.php',
        ),
        array(
            'title'    => 'RPG Display',
            'slug'     => 'rpg-display',
            'template' => 'template-display.php',
        ),
    );

    foreach ( $pages as $page_data ) {
        $existing = get_page_by_path( $page_data['slug'] );
        if ( ! $existing ) {
            $page_id = wp_insert_post( array(
                'post_title'   => $page_data['title'],
                'post_name'    => $page_data['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ));
            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
            }
        }
    }
}

// =============================================
// HELPER: Verificar acesso e redirecionar
// =============================================
function rmt_check_access( $required_role = 'player' ) {
    if ( ! is_user_logged_in() ) {
        rmt_render_login_required();
        return false;
    }

    $rpg_role = rmt_get_user_rpg_role();

    $access = array(
        'player' => array( 'player', 'dm', 'admin' ),
        'dm'     => array( 'dm', 'admin' ),
        'admin'  => array( 'admin' ),
    );

    if ( ! isset( $access[ $required_role ] ) || ! in_array( $rpg_role, $access[ $required_role ] ) ) {
        rmt_render_access_denied();
        return false;
    }

    return true;
}

function rmt_render_login_required() {
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php wp_head(); ?>
    </head>
    <body class="rmt-page">
        <div class="rmt-login-required">
            <div class="rmt-login-box">
                <div style="font-size:48px;margin-bottom:15px;">🎲</div>
                <h2>RPG Master Toolkit</h2>
                <p>Você precisa estar logado para acessar esta área.</p>
                <a href="<?php echo wp_login_url( get_permalink() ); ?>" class="rmt-btn rmt-btn-gold">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </a>
            </div>
        </div>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}

function rmt_render_access_denied() {
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php wp_head(); ?>
    </head>
    <body class="rmt-page">
        <div class="rmt-login-required">
            <div class="rmt-login-box">
                <div style="font-size:48px;margin-bottom:15px;">🚫</div>
                <h2>Acesso Negado</h2>
                <p>Você não tem permissão para acessar esta área.</p>
                <a href="<?php echo home_url(); ?>" class="rmt-btn rmt-btn-primary">
                    <i class="fas fa-home"></i> Voltar ao Início
                </a>
            </div>
        </div>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}

// =============================================
// DESABILITAR ADMIN BAR PARA JOGADORES
// =============================================
add_action( 'after_setup_theme', 'rmt_hide_admin_bar' );

function rmt_hide_admin_bar() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        show_admin_bar( false );
    }
}

// =============================================
// REDIRECIONAR JOGADORES DO WP-ADMIN
// =============================================
add_action( 'admin_init', 'rmt_redirect_non_admins' );

function rmt_redirect_non_admins() {
    if ( wp_doing_ajax() ) return;
    
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_redirect( home_url( '/rpg-player/' ) );
        exit;
    }
}
