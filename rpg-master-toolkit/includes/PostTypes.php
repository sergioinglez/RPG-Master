<?php
/**
 * RPG Master Toolkit - Custom Post Types
 * 
 * CPTs para: Aventuras, NPCs, Monstros, Mapas, Cenas
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class PostTypes {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ) );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
    }

    /**
     * Registra todos os Custom Post Types
     */
    public static function register_post_types() {

        // =============================================
        // CPT: AVENTURAS
        // =============================================
        register_post_type( 'rmt_adventure', array(
            'labels' => array(
                'name'               => 'Aventuras',
                'singular_name'      => 'Aventura',
                'add_new'            => 'Nova Aventura',
                'add_new_item'       => 'Adicionar Nova Aventura',
                'edit_item'          => 'Editar Aventura',
                'view_item'          => 'Ver Aventura',
                'all_items'          => 'Todas as Aventuras',
                'search_items'       => 'Buscar Aventuras',
                'not_found'          => 'Nenhuma aventura encontrada',
                'menu_name'          => '🎲 RPG Toolkit',
            ),
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-shield',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'show_in_rest'       => true,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ));

        // =============================================
        // CPT: CENAS (vinculadas a aventuras)
        // =============================================
        register_post_type( 'rmt_scene', array(
            'labels' => array(
                'name'               => 'Cenas',
                'singular_name'      => 'Cena',
                'add_new'            => 'Nova Cena',
                'add_new_item'       => 'Adicionar Nova Cena',
                'edit_item'          => 'Editar Cena',
                'all_items'          => 'Cenas',
                'menu_name'          => 'Cenas',
            ),
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=rmt_adventure',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'show_in_rest'       => true,
        ));

        // Meta boxes para cenas
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_scene_meta_boxes' ) );
        add_action( 'save_post_rmt_scene', array( __CLASS__, 'save_scene_meta' ) );

        // =============================================
        // CPT: NPCs
        // =============================================
        register_post_type( 'rmt_npc', array(
            'labels' => array(
                'name'               => 'NPCs',
                'singular_name'      => 'NPC',
                'add_new'            => 'Novo NPC',
                'add_new_item'       => 'Adicionar Novo NPC',
                'edit_item'          => 'Editar NPC',
                'all_items'          => 'NPCs',
                'menu_name'          => 'NPCs',
            ),
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=rmt_adventure',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'show_in_rest'       => true,
        ));

        add_action( 'add_meta_boxes', array( __CLASS__, 'add_npc_meta_boxes' ) );
        add_action( 'save_post_rmt_npc', array( __CLASS__, 'save_npc_meta' ) );

        // =============================================
        // CPT: MONSTROS / INIMIGOS
        // =============================================
        register_post_type( 'rmt_monster', array(
            'labels' => array(
                'name'               => 'Monstros',
                'singular_name'      => 'Monstro',
                'add_new'            => 'Novo Monstro',
                'add_new_item'       => 'Adicionar Novo Monstro',
                'edit_item'          => 'Editar Monstro',
                'all_items'          => 'Monstros',
                'menu_name'          => 'Monstros',
            ),
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=rmt_adventure',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'show_in_rest'       => true,
        ));

        add_action( 'add_meta_boxes', array( __CLASS__, 'add_monster_meta_boxes' ) );
        add_action( 'save_post_rmt_monster', array( __CLASS__, 'save_monster_meta' ) );

        // =============================================
        // CPT: MAPAS
        // =============================================
        register_post_type( 'rmt_map', array(
            'labels' => array(
                'name'               => 'Mapas',
                'singular_name'      => 'Mapa',
                'add_new'            => 'Novo Mapa',
                'add_new_item'       => 'Adicionar Novo Mapa',
                'edit_item'          => 'Editar Mapa',
                'all_items'          => 'Mapas',
                'menu_name'          => 'Mapas',
            ),
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=rmt_adventure',
            'supports'           => array( 'title', 'thumbnail' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'show_in_rest'       => true,
        ));

        add_action( 'add_meta_boxes', array( __CLASS__, 'add_map_meta_boxes' ) );
        add_action( 'save_post_rmt_map', array( __CLASS__, 'save_map_meta' ) );
    }

    /**
     * Registra Taxonomias
     */
    public static function register_taxonomies() {

        // Tipo de Cena
        register_taxonomy( 'rmt_scene_type', 'rmt_scene', array(
            'labels' => array(
                'name'          => 'Tipos de Cena',
                'singular_name' => 'Tipo de Cena',
            ),
            'hierarchical'  => true,
            'show_ui'       => true,
            'show_in_rest'  => true,
            'rewrite'       => false,
        ));

        // Tipo de Monstro (CR, tipo de criatura)
        register_taxonomy( 'rmt_creature_type', 'rmt_monster', array(
            'labels' => array(
                'name'          => 'Tipos de Criatura',
                'singular_name' => 'Tipo de Criatura',
            ),
            'hierarchical'  => true,
            'show_ui'       => true,
            'show_in_rest'  => true,
            'rewrite'       => false,
        ));

        // Localização (para mapas e cenas)
        register_taxonomy( 'rmt_location', array( 'rmt_scene', 'rmt_map', 'rmt_npc' ), array(
            'labels' => array(
                'name'          => 'Localizações',
                'singular_name' => 'Localização',
            ),
            'hierarchical'  => true,
            'show_ui'       => true,
            'show_in_rest'  => true,
            'rewrite'       => false,
        ));
    }

    // =================================================================
    // META BOXES - CENAS
    // =================================================================

    public static function add_scene_meta_boxes() {
        add_meta_box(
            'rmt_scene_details',
            '🎬 Detalhes da Cena',
            array( __CLASS__, 'render_scene_meta_box' ),
            'rmt_scene',
            'normal',
            'high'
        );
    }

    public static function render_scene_meta_box( $post ) {
        wp_nonce_field( 'rmt_scene_meta', 'rmt_scene_meta_nonce' );

        $adventure_id   = get_post_meta( $post->ID, '_rmt_adventure_id', true );
        $scene_order    = get_post_meta( $post->ID, '_rmt_scene_order', true );
        $scene_type     = get_post_meta( $post->ID, '_rmt_scene_type', true );
        $narration_text = get_post_meta( $post->ID, '_rmt_narration_text', true );
        $dm_notes       = get_post_meta( $post->ID, '_rmt_dm_notes', true );
        $map_id         = get_post_meta( $post->ID, '_rmt_map_id', true );
        $linked_npcs    = get_post_meta( $post->ID, '_rmt_linked_npcs', true );
        $linked_monsters= get_post_meta( $post->ID, '_rmt_linked_monsters', true );
        $display_image  = get_post_meta( $post->ID, '_rmt_display_image', true );
        $ambient_music  = get_post_meta( $post->ID, '_rmt_ambient_music', true );

        // Buscar aventuras para dropdown
        $adventures = get_posts( array(
            'post_type'   => 'rmt_adventure',
            'numberposts' => -1,
            'post_status' => 'publish',
        ));

        // Buscar mapas para dropdown
        $maps = get_posts( array(
            'post_type'   => 'rmt_map',
            'numberposts' => -1,
            'post_status' => 'publish',
        ));
        ?>
        <div class="rmt-meta-box">
            <table class="form-table">
                <tr>
                    <th><label for="rmt_adventure_id">Aventura</label></th>
                    <td>
                        <select name="rmt_adventure_id" id="rmt_adventure_id" class="regular-text">
                            <option value="">— Selecione —</option>
                            <?php foreach ( $adventures as $adv ) : ?>
                                <option value="<?php echo $adv->ID; ?>" <?php selected( $adventure_id, $adv->ID ); ?>>
                                    <?php echo esc_html( $adv->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_scene_order">Ordem da Cena</label></th>
                    <td>
                        <input type="number" name="rmt_scene_order" id="rmt_scene_order" 
                               value="<?php echo esc_attr( $scene_order ); ?>" min="1" class="small-text">
                        <p class="description">Número sequencial da cena na aventura</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_scene_type">Tipo de Cena</label></th>
                    <td>
                        <select name="rmt_scene_type" id="rmt_scene_type">
                            <option value="exploration" <?php selected( $scene_type, 'exploration' ); ?>>🗺️ Exploração</option>
                            <option value="combat" <?php selected( $scene_type, 'combat' ); ?>>⚔️ Combate</option>
                            <option value="social" <?php selected( $scene_type, 'social' ); ?>>💬 Interação Social</option>
                            <option value="puzzle" <?php selected( $scene_type, 'puzzle' ); ?>>🧩 Puzzle/Enigma</option>
                            <option value="rest" <?php selected( $scene_type, 'rest' ); ?>>🏕️ Descanso</option>
                            <option value="travel" <?php selected( $scene_type, 'travel' ); ?>>🚶 Viagem</option>
                            <option value="cutscene" <?php selected( $scene_type, 'cutscene' ); ?>>🎥 Cutscene</option>
                            <option value="shop" <?php selected( $scene_type, 'shop' ); ?>>🏪 Loja/Comércio</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_narration_text">Texto de Narração</label></th>
                    <td>
                        <textarea name="rmt_narration_text" id="rmt_narration_text" rows="8" class="large-text"><?php echo esc_textarea( $narration_text ); ?></textarea>
                        <p class="description">Texto que o Mestre irá ler/narrar para os jogadores. Suporta marcações: <code>[pause]</code> para pausas dramáticas, <code>[whisper]</code> para sussurros.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_dm_notes">Notas do Mestre</label></th>
                    <td>
                        <textarea name="rmt_dm_notes" id="rmt_dm_notes" rows="5" class="large-text"><?php echo esc_textarea( $dm_notes ); ?></textarea>
                        <p class="description">Notas privadas para o Mestre. Dicas, triggers, DCs, consequências...</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_map_id">Mapa da Cena</label></th>
                    <td>
                        <select name="rmt_map_id" id="rmt_map_id" class="regular-text">
                            <option value="">— Sem mapa —</option>
                            <?php foreach ( $maps as $map ) : ?>
                                <option value="<?php echo $map->ID; ?>" <?php selected( $map_id, $map->ID ); ?>>
                                    <?php echo esc_html( $map->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_display_image">Imagem de Exibição</label></th>
                    <td>
                        <input type="text" name="rmt_display_image" id="rmt_display_image" 
                               value="<?php echo esc_url( $display_image ); ?>" class="large-text">
                        <button type="button" class="button rmt-upload-image" data-target="rmt_display_image">
                            📷 Selecionar Imagem
                        </button>
                        <p class="description">Imagem que aparecerá na Tela de Exibição quando esta cena estiver ativa</p>
                        <?php if ( $display_image ) : ?>
                            <img src="<?php echo esc_url( $display_image ); ?>" style="max-width:300px;margin-top:10px;border-radius:8px;">
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_ambient_music">Música Ambiente (URL)</label></th>
                    <td>
                        <input type="url" name="rmt_ambient_music" id="rmt_ambient_music" 
                               value="<?php echo esc_url( $ambient_music ); ?>" class="large-text">
                        <p class="description">URL de áudio para tocar durante esta cena (opcional)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_linked_npcs">NPCs Presentes</label></th>
                    <td>
                        <input type="text" name="rmt_linked_npcs" id="rmt_linked_npcs" 
                               value="<?php echo esc_attr( $linked_npcs ); ?>" class="large-text">
                        <p class="description">IDs dos NPCs separados por vírgula (ex: 12,45,67)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_linked_monsters">Monstros na Cena</label></th>
                    <td>
                        <input type="text" name="rmt_linked_monsters" id="rmt_linked_monsters" 
                               value="<?php echo esc_attr( $linked_monsters ); ?>" class="large-text">
                        <p class="description">IDs dos monstros separados por vírgula (ex: 8,23)</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public static function save_scene_meta( $post_id ) {
        if ( ! isset( $_POST['rmt_scene_meta_nonce'] ) ||
             ! wp_verify_nonce( $_POST['rmt_scene_meta_nonce'], 'rmt_scene_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = array(
            'rmt_adventure_id'    => '_rmt_adventure_id',
            'rmt_scene_order'     => '_rmt_scene_order',
            'rmt_scene_type'      => '_rmt_scene_type',
            'rmt_narration_text'  => '_rmt_narration_text',
            'rmt_dm_notes'        => '_rmt_dm_notes',
            'rmt_map_id'          => '_rmt_map_id',
            'rmt_display_image'   => '_rmt_display_image',
            'rmt_ambient_music'   => '_rmt_ambient_music',
            'rmt_linked_npcs'     => '_rmt_linked_npcs',
            'rmt_linked_monsters' => '_rmt_linked_monsters',
        );

        foreach ( $fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
            }
        }

        // Sanitizar narration e dm_notes com sanitize_textarea_field
        if ( isset( $_POST['rmt_narration_text'] ) ) {
            update_post_meta( $post_id, '_rmt_narration_text', sanitize_textarea_field( $_POST['rmt_narration_text'] ) );
        }
        if ( isset( $_POST['rmt_dm_notes'] ) ) {
            update_post_meta( $post_id, '_rmt_dm_notes', sanitize_textarea_field( $_POST['rmt_dm_notes'] ) );
        }
    }

    // =================================================================
    // META BOXES - NPCs
    // =================================================================

    public static function add_npc_meta_boxes() {
        add_meta_box(
            'rmt_npc_details',
            '👤 Detalhes do NPC',
            array( __CLASS__, 'render_npc_meta_box' ),
            'rmt_npc',
            'normal',
            'high'
        );
    }

    public static function render_npc_meta_box( $post ) {
        wp_nonce_field( 'rmt_npc_meta', 'rmt_npc_meta_nonce' );

        $fields = array(
            'race'         => get_post_meta( $post->ID, '_rmt_npc_race', true ),
            'class'        => get_post_meta( $post->ID, '_rmt_npc_class', true ),
            'alignment'    => get_post_meta( $post->ID, '_rmt_npc_alignment', true ),
            'location'     => get_post_meta( $post->ID, '_rmt_npc_location', true ),
            'role'         => get_post_meta( $post->ID, '_rmt_npc_role', true ),
            'personality'  => get_post_meta( $post->ID, '_rmt_npc_personality', true ),
            'dialogue'     => get_post_meta( $post->ID, '_rmt_npc_dialogue', true ),
            'secrets'      => get_post_meta( $post->ID, '_rmt_npc_secrets', true ),
            'stats'        => get_post_meta( $post->ID, '_rmt_npc_stats', true ),
            'hp'           => get_post_meta( $post->ID, '_rmt_npc_hp', true ),
            'ac'           => get_post_meta( $post->ID, '_rmt_npc_ac', true ),
            'cr'           => get_post_meta( $post->ID, '_rmt_npc_cr', true ),
            'is_hostile'   => get_post_meta( $post->ID, '_rmt_npc_is_hostile', true ),
            'portrait_url' => get_post_meta( $post->ID, '_rmt_npc_portrait', true ),
            'voice_notes'  => get_post_meta( $post->ID, '_rmt_npc_voice_notes', true ),
        );
        ?>
        <div class="rmt-meta-box">
            <table class="form-table">
                <tr>
                    <th><label for="rmt_npc_race">Raça</label></th>
                    <td><input type="text" name="rmt_npc_race" value="<?php echo esc_attr( $fields['race'] ); ?>" class="regular-text" placeholder="Humano, Elfo, Anão..."></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_class">Classe/Ocupação</label></th>
                    <td><input type="text" name="rmt_npc_class" value="<?php echo esc_attr( $fields['class'] ); ?>" class="regular-text" placeholder="Taberneiro, Guarda, Mago..."></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_alignment">Alinhamento</label></th>
                    <td>
                        <select name="rmt_npc_alignment">
                            <option value="">— Selecione —</option>
                            <option value="LG" <?php selected( $fields['alignment'], 'LG' ); ?>>Leal e Bom</option>
                            <option value="NG" <?php selected( $fields['alignment'], 'NG' ); ?>>Neutro e Bom</option>
                            <option value="CG" <?php selected( $fields['alignment'], 'CG' ); ?>>Caótico e Bom</option>
                            <option value="LN" <?php selected( $fields['alignment'], 'LN' ); ?>>Leal e Neutro</option>
                            <option value="TN" <?php selected( $fields['alignment'], 'TN' ); ?>>Verdadeiro Neutro</option>
                            <option value="CN" <?php selected( $fields['alignment'], 'CN' ); ?>>Caótico e Neutro</option>
                            <option value="LE" <?php selected( $fields['alignment'], 'LE' ); ?>>Leal e Mau</option>
                            <option value="NE" <?php selected( $fields['alignment'], 'NE' ); ?>>Neutro e Mau</option>
                            <option value="CE" <?php selected( $fields['alignment'], 'CE' ); ?>>Caótico e Mau</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_role">Papel na História</label></th>
                    <td>
                        <select name="rmt_npc_role">
                            <option value="ally" <?php selected( $fields['role'], 'ally' ); ?>>🤝 Aliado</option>
                            <option value="neutral" <?php selected( $fields['role'], 'neutral' ); ?>>😐 Neutro</option>
                            <option value="enemy" <?php selected( $fields['role'], 'enemy' ); ?>>👿 Inimigo</option>
                            <option value="merchant" <?php selected( $fields['role'], 'merchant' ); ?>>🏪 Mercador</option>
                            <option value="quest_giver" <?php selected( $fields['role'], 'quest_giver' ); ?>>📜 Dá Missões</option>
                            <option value="boss" <?php selected( $fields['role'], 'boss' ); ?>>💀 Boss</option>
                            <option value="commoner" <?php selected( $fields['role'], 'commoner' ); ?>>👨 Plebeu</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>É Hostil?</label></th>
                    <td>
                        <label><input type="checkbox" name="rmt_npc_is_hostile" value="1" <?php checked( $fields['is_hostile'], '1' ); ?>> Sim, pode entrar em combate</label>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_hp">HP</label></th>
                    <td><input type="number" name="rmt_npc_hp" value="<?php echo esc_attr( $fields['hp'] ); ?>" class="small-text" min="0"></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_ac">CA (Armor Class)</label></th>
                    <td><input type="number" name="rmt_npc_ac" value="<?php echo esc_attr( $fields['ac'] ); ?>" class="small-text" min="0"></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_cr">CR (Challenge Rating)</label></th>
                    <td><input type="text" name="rmt_npc_cr" value="<?php echo esc_attr( $fields['cr'] ); ?>" class="small-text" placeholder="1/4, 1, 5..."></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_location">Localização</label></th>
                    <td><input type="text" name="rmt_npc_location" value="<?php echo esc_attr( $fields['location'] ); ?>" class="regular-text" placeholder="Taverna do Pônei Saltitante"></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_personality">Personalidade</label></th>
                    <td><textarea name="rmt_npc_personality" rows="3" class="large-text"><?php echo esc_textarea( $fields['personality'] ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_voice_notes">Notas de Voz/Sotaque</label></th>
                    <td>
                        <input type="text" name="rmt_npc_voice_notes" value="<?php echo esc_attr( $fields['voice_notes'] ); ?>" class="large-text" placeholder="Voz grave, sotaque anão, fala devagar...">
                        <p class="description">Dica para o Mestre interpretar o NPC</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_dialogue">Diálogos Preparados</label></th>
                    <td>
                        <textarea name="rmt_npc_dialogue" rows="6" class="large-text"><?php echo esc_textarea( $fields['dialogue'] ); ?></textarea>
                        <p class="description">Falas prontas para o Mestre. Use <code>[greeting]</code>, <code>[quest]</code>, <code>[farewell]</code> como marcadores.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_secrets">Segredos (só o Mestre vê)</label></th>
                    <td>
                        <textarea name="rmt_npc_secrets" rows="3" class="large-text"><?php echo esc_textarea( $fields['secrets'] ); ?></textarea>
                        <p class="description">Informações secretas que só o Mestre conhece</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_portrait">Retrato do NPC</label></th>
                    <td>
                        <input type="text" name="rmt_npc_portrait" id="rmt_npc_portrait" 
                               value="<?php echo esc_url( $fields['portrait_url'] ); ?>" class="large-text">
                        <button type="button" class="button rmt-upload-image" data-target="rmt_npc_portrait">
                            📷 Selecionar Imagem
                        </button>
                        <?php if ( $fields['portrait_url'] ) : ?>
                            <br><img src="<?php echo esc_url( $fields['portrait_url'] ); ?>" style="max-width:200px;margin-top:10px;border-radius:8px;">
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmt_npc_stats">Stats Completos (JSON)</label></th>
                    <td>
                        <textarea name="rmt_npc_stats" rows="5" class="large-text" placeholder='{"str":10,"dex":12,"con":10,"int":14,"wis":11,"cha":16}'><?php echo esc_textarea( $fields['stats'] ); ?></textarea>
                        <p class="description">Stats detalhados em formato JSON (opcional, para combate)</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public static function save_npc_meta( $post_id ) {
        if ( ! isset( $_POST['rmt_npc_meta_nonce'] ) ||
             ! wp_verify_nonce( $_POST['rmt_npc_meta_nonce'], 'rmt_npc_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $text_fields = array(
            'rmt_npc_race'       => '_rmt_npc_race',
            'rmt_npc_class'      => '_rmt_npc_class',
            'rmt_npc_alignment'  => '_rmt_npc_alignment',
            'rmt_npc_role'       => '_rmt_npc_role',
            'rmt_npc_location'   => '_rmt_npc_location',
            'rmt_npc_hp'         => '_rmt_npc_hp',
            'rmt_npc_ac'         => '_rmt_npc_ac',
            'rmt_npc_cr'         => '_rmt_npc_cr',
            'rmt_npc_portrait'   => '_rmt_npc_portrait',
            'rmt_npc_voice_notes'=> '_rmt_npc_voice_notes',
        );

        foreach ( $text_fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
            }
        }

        $textarea_fields = array(
            'rmt_npc_personality' => '_rmt_npc_personality',
            'rmt_npc_dialogue'   => '_rmt_npc_dialogue',
            'rmt_npc_secrets'    => '_rmt_npc_secrets',
            'rmt_npc_stats'      => '_rmt_npc_stats',
        );

        foreach ( $textarea_fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $_POST[ $field ] ) );
            }
        }

        // Checkbox
        update_post_meta( $post_id, '_rmt_npc_is_hostile', isset( $_POST['rmt_npc_is_hostile'] ) ? '1' : '0' );
    }

    // =================================================================
    // META BOXES - MONSTROS
    // =================================================================

    public static function add_monster_meta_boxes() {
        add_meta_box(
            'rmt_monster_details',
            '🐉 Stats do Monstro (D&D 5e)',
            array( __CLASS__, 'render_monster_meta_box' ),
            'rmt_monster',
            'normal',
            'high'
        );
    }

    public static function render_monster_meta_box( $post ) {
        wp_nonce_field( 'rmt_monster_meta', 'rmt_monster_meta_nonce' );

        $m = array(
            'size'        => get_post_meta( $post->ID, '_rmt_mon_size', true ) ?: 'medium',
            'type'        => get_post_meta( $post->ID, '_rmt_mon_type', true ),
            'alignment'   => get_post_meta( $post->ID, '_rmt_mon_alignment', true ),
            'ac'          => get_post_meta( $post->ID, '_rmt_mon_ac', true ),
            'ac_type'     => get_post_meta( $post->ID, '_rmt_mon_ac_type', true ),
            'hp'          => get_post_meta( $post->ID, '_rmt_mon_hp', true ),
            'hp_dice'     => get_post_meta( $post->ID, '_rmt_mon_hp_dice', true ),
            'speed'       => get_post_meta( $post->ID, '_rmt_mon_speed', true ),
            'str'         => get_post_meta( $post->ID, '_rmt_mon_str', true ) ?: 10,
            'dex'         => get_post_meta( $post->ID, '_rmt_mon_dex', true ) ?: 10,
            'con'         => get_post_meta( $post->ID, '_rmt_mon_con', true ) ?: 10,
            'int'         => get_post_meta( $post->ID, '_rmt_mon_int', true ) ?: 10,
            'wis'         => get_post_meta( $post->ID, '_rmt_mon_wis', true ) ?: 10,
            'cha'         => get_post_meta( $post->ID, '_rmt_mon_cha', true ) ?: 10,
            'cr'          => get_post_meta( $post->ID, '_rmt_mon_cr', true ),
            'xp'          => get_post_meta( $post->ID, '_rmt_mon_xp', true ),
            'actions'     => get_post_meta( $post->ID, '_rmt_mon_actions', true ),
            'abilities'   => get_post_meta( $post->ID, '_rmt_mon_abilities', true ),
            'legendary'   => get_post_meta( $post->ID, '_rmt_mon_legendary', true ),
            'resistances' => get_post_meta( $post->ID, '_rmt_mon_resistances', true ),
            'immunities'  => get_post_meta( $post->ID, '_rmt_mon_immunities', true ),
            'senses'      => get_post_meta( $post->ID, '_rmt_mon_senses', true ),
            'languages'   => get_post_meta( $post->ID, '_rmt_mon_languages', true ),
            'portrait'    => get_post_meta( $post->ID, '_rmt_mon_portrait', true ),
        );
        ?>
        <div class="rmt-meta-box">
            <table class="form-table">
                <tr>
                    <th>Tamanho</th>
                    <td>
                        <select name="rmt_mon_size">
                            <?php foreach ( array('tiny'=>'Miúdo','small'=>'Pequeno','medium'=>'Médio','large'=>'Grande','huge'=>'Enorme','gargantuan'=>'Colossal') as $k => $v ) : ?>
                                <option value="<?php echo $k; ?>" <?php selected( $m['size'], $k ); ?>><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Tipo de Criatura</th>
                    <td><input type="text" name="rmt_mon_type" value="<?php echo esc_attr( $m['type'] ); ?>" placeholder="Aberração, Besta, Dragão, Morto-vivo..." class="regular-text"></td>
                </tr>
                <tr>
                    <th>Alinhamento</th>
                    <td><input type="text" name="rmt_mon_alignment" value="<?php echo esc_attr( $m['alignment'] ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>CA (AC)</th>
                    <td>
                        <input type="number" name="rmt_mon_ac" value="<?php echo esc_attr( $m['ac'] ); ?>" class="small-text" min="0">
                        <input type="text" name="rmt_mon_ac_type" value="<?php echo esc_attr( $m['ac_type'] ); ?>" placeholder="armadura natural, cota de malha..." class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th>Pontos de Vida</th>
                    <td>
                        <input type="number" name="rmt_mon_hp" value="<?php echo esc_attr( $m['hp'] ); ?>" class="small-text" min="0">
                        <input type="text" name="rmt_mon_hp_dice" value="<?php echo esc_attr( $m['hp_dice'] ); ?>" placeholder="5d8+10" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th>Velocidade</th>
                    <td><input type="text" name="rmt_mon_speed" value="<?php echo esc_attr( $m['speed'] ); ?>" placeholder="30 ft, voar 60 ft" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Atributos</th>
                    <td>
                        <label>FOR: <input type="number" name="rmt_mon_str" value="<?php echo esc_attr( $m['str'] ); ?>" class="small-text" min="1" max="30"></label>
                        <label>DES: <input type="number" name="rmt_mon_dex" value="<?php echo esc_attr( $m['dex'] ); ?>" class="small-text" min="1" max="30"></label>
                        <label>CON: <input type="number" name="rmt_mon_con" value="<?php echo esc_attr( $m['con'] ); ?>" class="small-text" min="1" max="30"></label><br>
                        <label>INT: <input type="number" name="rmt_mon_int" value="<?php echo esc_attr( $m['int'] ); ?>" class="small-text" min="1" max="30"></label>
                        <label>SAB: <input type="number" name="rmt_mon_wis" value="<?php echo esc_attr( $m['wis'] ); ?>" class="small-text" min="1" max="30"></label>
                        <label>CAR: <input type="number" name="rmt_mon_cha" value="<?php echo esc_attr( $m['cha'] ); ?>" class="small-text" min="1" max="30"></label>
                    </td>
                </tr>
                <tr>
                    <th>CR / XP</th>
                    <td>
                        <label>CR: <input type="text" name="rmt_mon_cr" value="<?php echo esc_attr( $m['cr'] ); ?>" class="small-text"></label>
                        <label>XP: <input type="number" name="rmt_mon_xp" value="<?php echo esc_attr( $m['xp'] ); ?>" class="small-text" min="0"></label>
                    </td>
                </tr>
                <tr>
                    <th>Sentidos</th>
                    <td><input type="text" name="rmt_mon_senses" value="<?php echo esc_attr( $m['senses'] ); ?>" class="large-text" placeholder="Visão no escuro 60ft, Percepção passiva 14"></td>
                </tr>
                <tr>
                    <th>Idiomas</th>
                    <td><input type="text" name="rmt_mon_languages" value="<?php echo esc_attr( $m['languages'] ); ?>" class="large-text" placeholder="Comum, Dracônico"></td>
                </tr>
                <tr>
                    <th>Resistências</th>
                    <td><input type="text" name="rmt_mon_resistances" value="<?php echo esc_attr( $m['resistances'] ); ?>" class="large-text" placeholder="Fogo, Ácido"></td>
                </tr>
                <tr>
                    <th>Imunidades</th>
                    <td><input type="text" name="rmt_mon_immunities" value="<?php echo esc_attr( $m['immunities'] ); ?>" class="large-text" placeholder="Veneno, encantamento"></td>
                </tr>
                <tr>
                    <th>Habilidades Especiais</th>
                    <td>
                        <textarea name="rmt_mon_abilities" rows="4" class="large-text"><?php echo esc_textarea( $m['abilities'] ); ?></textarea>
                        <p class="description">Uma habilidade por linha. Formato: <code>Nome: Descrição</code></p>
                    </td>
                </tr>
                <tr>
                    <th>Ações</th>
                    <td>
                        <textarea name="rmt_mon_actions" rows="4" class="large-text"><?php echo esc_textarea( $m['actions'] ); ?></textarea>
                        <p class="description">Uma ação por linha. Formato: <code>Nome: +X para acertar, alcance Xft, dano XdX+X tipo</code></p>
                    </td>
                </tr>
                <tr>
                    <th>Ações Lendárias</th>
                    <td>
                        <textarea name="rmt_mon_legendary" rows="3" class="large-text"><?php echo esc_textarea( $m['legendary'] ); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>Imagem do Monstro</th>
                    <td>
                        <input type="text" name="rmt_mon_portrait" id="rmt_mon_portrait" 
                               value="<?php echo esc_url( $m['portrait'] ); ?>" class="large-text">
                        <button type="button" class="button rmt-upload-image" data-target="rmt_mon_portrait">📷 Selecionar</button>
                        <?php if ( $m['portrait'] ) : ?>
                            <br><img src="<?php echo esc_url( $m['portrait'] ); ?>" style="max-width:200px;margin-top:10px;border-radius:8px;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public static function save_monster_meta( $post_id ) {
        if ( ! isset( $_POST['rmt_monster_meta_nonce'] ) ||
             ! wp_verify_nonce( $_POST['rmt_monster_meta_nonce'], 'rmt_monster_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $text_fields = array(
            'rmt_mon_size', 'rmt_mon_type', 'rmt_mon_alignment',
            'rmt_mon_ac', 'rmt_mon_ac_type', 'rmt_mon_hp', 'rmt_mon_hp_dice',
            'rmt_mon_speed', 'rmt_mon_str', 'rmt_mon_dex', 'rmt_mon_con',
            'rmt_mon_int', 'rmt_mon_wis', 'rmt_mon_cha',
            'rmt_mon_cr', 'rmt_mon_xp', 'rmt_mon_senses', 'rmt_mon_languages',
            'rmt_mon_resistances', 'rmt_mon_immunities', 'rmt_mon_portrait',
        );

        foreach ( $text_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
            }
        }

        $textarea_fields = array( 'rmt_mon_actions', 'rmt_mon_abilities', 'rmt_mon_legendary' );
        foreach ( $textarea_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( $_POST[ $field ] ) );
            }
        }
    }

    // =================================================================
    // META BOXES - MAPAS
    // =================================================================

    public static function add_map_meta_boxes() {
        add_meta_box(
            'rmt_map_details',
            '🗺️ Detalhes do Mapa',
            array( __CLASS__, 'render_map_meta_box' ),
            'rmt_map',
            'normal',
            'high'
        );
    }

    public static function render_map_meta_box( $post ) {
        wp_nonce_field( 'rmt_map_meta', 'rmt_map_meta_nonce' );

        $adventure_id = get_post_meta( $post->ID, '_rmt_map_adventure_id', true );
        $map_type     = get_post_meta( $post->ID, '_rmt_map_type', true );
        $map_image    = get_post_meta( $post->ID, '_rmt_map_image', true );
        $grid_size    = get_post_meta( $post->ID, '_rmt_map_grid_size', true ) ?: 50;
        $dm_map_image = get_post_meta( $post->ID, '_rmt_map_dm_image', true );
        $points_of_interest = get_post_meta( $post->ID, '_rmt_map_poi', true );
        $description  = get_post_meta( $post->ID, '_rmt_map_description', true );

        $adventures = get_posts( array(
            'post_type'   => 'rmt_adventure',
            'numberposts' => -1,
            'post_status' => 'publish',
        ));
        ?>
        <div class="rmt-meta-box">
            <table class="form-table">
                <tr>
                    <th><label>Aventura</label></th>
                    <td>
                        <select name="rmt_map_adventure_id">
                            <option value="">— Geral (todas as aventuras) —</option>
                            <?php foreach ( $adventures as $adv ) : ?>
                                <option value="<?php echo $adv->ID; ?>" <?php selected( $adventure_id, $adv->ID ); ?>>
                                    <?php echo esc_html( $adv->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>Tipo de Mapa</label></th>
                    <td>
                        <select name="rmt_map_type">
                            <option value="world" <?php selected( $map_type, 'world' ); ?>>🌍 Mapa Mundo</option>
                            <option value="region" <?php selected( $map_type, 'region' ); ?>>🏔️ Região</option>
                            <option value="city" <?php selected( $map_type, 'city' ); ?>>🏘️ Cidade</option>
                            <option value="dungeon" <?php selected( $map_type, 'dungeon' ); ?>>🏰 Dungeon</option>
                            <option value="cave" <?php selected( $map_type, 'cave' ); ?>>🕳️ Caverna</option>
                            <option value="building" <?php selected( $map_type, 'building' ); ?>>🏠 Edifício</option>
                            <option value="battle" <?php selected( $map_type, 'battle' ); ?>>⚔️ Mapa de Batalha</option>
                            <option value="wilderness" <?php selected( $map_type, 'wilderness' ); ?>>🌲 Área Selvagem</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>Imagem do Mapa (Jogadores)</label></th>
                    <td>
                        <input type="text" name="rmt_map_image" id="rmt_map_image" 
                               value="<?php echo esc_url( $map_image ); ?>" class="large-text">
                        <button type="button" class="button rmt-upload-image" data-target="rmt_map_image">📷 Selecionar</button>
                        <p class="description">Mapa visível para os jogadores (sem segredos/salas escondidas)</p>
                        <?php if ( $map_image ) : ?>
                            <br><img src="<?php echo esc_url( $map_image ); ?>" style="max-width:400px;margin-top:10px;border-radius:8px;">
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label>Mapa Completo (Mestre)</label></th>
                    <td>
                        <input type="text" name="rmt_map_dm_image" id="rmt_map_dm_image" 
                               value="<?php echo esc_url( $dm_map_image ); ?>" class="large-text">
                        <button type="button" class="button rmt-upload-image" data-target="rmt_map_dm_image">📷 Selecionar</button>
                        <p class="description">Mapa completo com todas as salas/segredos (só o Mestre vê)</p>
                        <?php if ( $dm_map_image ) : ?>
                            <br><img src="<?php echo esc_url( $dm_map_image ); ?>" style="max-width:400px;margin-top:10px;border-radius:8px;">
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label>Tamanho do Grid (px)</label></th>
                    <td>
                        <input type="number" name="rmt_map_grid_size" value="<?php echo esc_attr( $grid_size ); ?>" class="small-text" min="10" max="200">
                        <p class="description">Tamanho de cada quadrado do grid em pixels (para posicionamento de tokens)</p>
                    </td>
                </tr>
                <tr>
                    <th><label>Descrição do Local</label></th>
                    <td><textarea name="rmt_map_description" rows="4" class="large-text"><?php echo esc_textarea( $description ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label>Pontos de Interesse (JSON)</label></th>
                    <td>
                        <textarea name="rmt_map_poi" rows="5" class="large-text" placeholder='[{"name":"Taverna","x":120,"y":340,"description":"Taverna do Pônei Saltitante"}]'><?php echo esc_textarea( $points_of_interest ); ?></textarea>
                        <p class="description">Array JSON com pontos de interesse no mapa</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public static function save_map_meta( $post_id ) {
        if ( ! isset( $_POST['rmt_map_meta_nonce'] ) ||
             ! wp_verify_nonce( $_POST['rmt_map_meta_nonce'], 'rmt_map_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $text_fields = array(
            'rmt_map_adventure_id' => '_rmt_map_adventure_id',
            'rmt_map_type'         => '_rmt_map_type',
            'rmt_map_image'        => '_rmt_map_image',
            'rmt_map_dm_image'     => '_rmt_map_dm_image',
            'rmt_map_grid_size'    => '_rmt_map_grid_size',
        );

        foreach ( $text_fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
            }
        }

        if ( isset( $_POST['rmt_map_description'] ) ) {
            update_post_meta( $post_id, '_rmt_map_description', sanitize_textarea_field( $_POST['rmt_map_description'] ) );
        }
        if ( isset( $_POST['rmt_map_poi'] ) ) {
            update_post_meta( $post_id, '_rmt_map_poi', sanitize_textarea_field( $_POST['rmt_map_poi'] ) );
        }
    }
}
