<?php
/**
 * RPG Master Toolkit - REST API
 * 
 * Endpoints REST para comunicação entre frontend e backend
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class REST_API {

    const NAMESPACE = 'rmt/v1';

    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    public static function register_routes() {

        // =============================================
        // PERSONAGENS
        // =============================================

        // Listar personagens do usuário
        register_rest_route( self::NAMESPACE, '/characters', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_characters' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // Criar personagem
        register_rest_route( self::NAMESPACE, '/characters', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'create_character' ),
            'permission_callback' => function() { return current_user_can( 'rmt_create_characters' ); },
        ));

        // Ver personagem específico
        register_rest_route( self::NAMESPACE, '/characters/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_character' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // Atualizar personagem
        register_rest_route( self::NAMESPACE, '/characters/(?P<id>\d+)', array(
            'methods'             => 'PUT,PATCH',
            'callback'            => array( __CLASS__, 'update_character' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // Deletar personagem
        register_rest_route( self::NAMESPACE, '/characters/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( __CLASS__, 'delete_character' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // Ficha completa do personagem
        register_rest_route( self::NAMESPACE, '/characters/(?P<id>\d+)/sheet', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_character_sheet' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // =============================================
        // INVENTÁRIO
        // =============================================

        register_rest_route( self::NAMESPACE, '/characters/(?P<id>\d+)/inventory', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_inventory' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        register_rest_route( self::NAMESPACE, '/characters/(?P<id>\d+)/inventory', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'add_inventory_item' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        register_rest_route( self::NAMESPACE, '/inventory/(?P<item_id>\d+)', array(
            'methods'             => 'PUT,PATCH',
            'callback'            => array( __CLASS__, 'update_inventory_item' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        register_rest_route( self::NAMESPACE, '/inventory/(?P<item_id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( __CLASS__, 'delete_inventory_item' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // =============================================
        // AVENTURAS
        // =============================================

        register_rest_route( self::NAMESPACE, '/adventures', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_adventures' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        register_rest_route( self::NAMESPACE, '/adventures/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_adventure' ),
            'permission_callback' => function() { return is_user_logged_in() && rmt_user_can_dm(); },
        ));

        // =============================================
        // SESSÃO ATIVA (DM)
        // =============================================

        // Iniciar sessão
        register_rest_route( self::NAMESPACE, '/session/start', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'start_session' ),
            'permission_callback' => function() { return current_user_can( 'rmt_manage_sessions' ); },
        ));

        // Status da sessão
        register_rest_route( self::NAMESPACE, '/session/status', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_session_status' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ));

        // Mudar cena
        register_rest_route( self::NAMESPACE, '/session/scene', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'change_scene' ),
            'permission_callback' => function() { return current_user_can( 'rmt_manage_sessions' ); },
        ));

        // Mostrar NPC
        register_rest_route( self::NAMESPACE, '/session/npc', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'show_npc' ),
            'permission_callback' => function() { return current_user_can( 'rmt_manage_sessions' ); },
        ));

        // Mover personagem no mapa
        register_rest_route( self::NAMESPACE, '/session/move', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'move_character' ),
            'permission_callback' => function() { return current_user_can( 'rmt_manage_sessions' ); },
        ));

        // Atualizar combate
        register_rest_route( self::NAMESPACE, '/session/combat', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'update_combat' ),
            'permission_callback' => function() { return current_user_can( 'rmt_manage_sessions' ); },
        ));

        // Pausar/Encerrar sessão
        register_rest_route( self::NAMESPACE, '/session/(?P<action>pause|end)', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'session_control' ),
            'permission_callback' => function() { return current_user_can( 'rmt_manage_sessions' ); },
        ));

        // =============================================
        // DISPLAY (tela de exibição - polling)
        // =============================================

        register_rest_route( self::NAMESPACE, '/display', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_display' ),
            'permission_callback' => '__return_true', // Público para a TV/tela
        ));

        // =============================================
        // DM CONTROLS (controle de jogadores)
        // =============================================

        register_rest_route( self::NAMESPACE, '/dm/player/(?P<char_id>\d+)/condition', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'apply_condition' ),
            'permission_callback' => function() { return current_user_can( 'rmt_control_players' ); },
        ));

        register_rest_route( self::NAMESPACE, '/dm/player/(?P<char_id>\d+)/condition', array(
            'methods'             => 'DELETE',
            'callback'            => array( __CLASS__, 'remove_condition' ),
            'permission_callback' => function() { return current_user_can( 'rmt_control_players' ); },
        ));

        register_rest_route( self::NAMESPACE, '/dm/player/(?P<char_id>\d+)/xp', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'grant_xp' ),
            'permission_callback' => function() { return current_user_can( 'rmt_control_players' ); },
        ));

        register_rest_route( self::NAMESPACE, '/dm/player/(?P<char_id>\d+)/hp', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'modify_hp' ),
            'permission_callback' => function() { return current_user_can( 'rmt_control_players' ); },
        ));

        // Todos os personagens (DM view)
        register_rest_route( self::NAMESPACE, '/dm/characters', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'dm_get_all_characters' ),
            'permission_callback' => function() { return current_user_can( 'rmt_view_all_characters' ); },
        ));

        // =============================================
        // DADOS D&D 5e (referência)
        // =============================================

        register_rest_route( self::NAMESPACE, '/rules/races', array(
            'methods'  => 'GET',
            'callback' => function() { return rest_ensure_response( DnD5e_Rules::get_races() ); },
            'permission_callback' => '__return_true',
        ));

        register_rest_route( self::NAMESPACE, '/rules/classes', array(
            'methods'  => 'GET',
            'callback' => function() { return rest_ensure_response( DnD5e_Rules::get_classes() ); },
            'permission_callback' => '__return_true',
        ));

        register_rest_route( self::NAMESPACE, '/rules/backgrounds', array(
            'methods'  => 'GET',
            'callback' => function() { return rest_ensure_response( DnD5e_Rules::get_backgrounds() ); },
            'permission_callback' => '__return_true',
        ));

        register_rest_route( self::NAMESPACE, '/rules/conditions', array(
            'methods'  => 'GET',
            'callback' => function() { return rest_ensure_response( DnD5e_Rules::get_conditions() ); },
            'permission_callback' => '__return_true',
        ));

        register_rest_route( self::NAMESPACE, '/rules/skills', array(
            'methods'  => 'GET',
            'callback' => function() { return rest_ensure_response( DnD5e_Rules::get_skills() ); },
            'permission_callback' => '__return_true',
        ));
    }

    // =================================================================
    // CALLBACKS - PERSONAGENS
    // =================================================================

    public static function get_characters( $request ) {
        $user_id = get_current_user_id();
        $characters = Character_Manager::get_user_characters( $user_id );
        return rest_ensure_response( $characters );
    }

    public static function create_character( $request ) {
        $user_id = get_current_user_id();
        $data = $request->get_json_params();
        $result = Character_Manager::create_character( $user_id, $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( $result );
    }

    public static function get_character( $request ) {
        $character = Character_Manager::get_character( $request['id'] );

        if ( ! $character ) {
            return new \WP_Error( 'not_found', 'Personagem não encontrado.', array( 'status' => 404 ) );
        }

        // Verificar acesso
        $user_id = get_current_user_id();
        if ( $character->user_id != $user_id && ! current_user_can( 'rmt_view_all_characters' ) ) {
            return new \WP_Error( 'forbidden', 'Acesso negado.', array( 'status' => 403 ) );
        }

        return rest_ensure_response( $character );
    }

    public static function update_character( $request ) {
        $data = $request->get_json_params();
        $result = Character_Manager::update_character( $request['id'], $data, get_current_user_id() );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( $result );
    }

    public static function delete_character( $request ) {
        $result = Character_Manager::delete_character( $request['id'], get_current_user_id() );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( array( 'deleted' => true ) );
    }

    public static function get_character_sheet( $request ) {
        $character = Character_Manager::get_character( $request['id'] );
        if ( ! $character ) {
            return new \WP_Error( 'not_found', 'Personagem não encontrado.', array( 'status' => 404 ) );
        }

        $user_id = get_current_user_id();
        if ( $character->user_id != $user_id && ! current_user_can( 'rmt_view_all_characters' ) ) {
            return new \WP_Error( 'forbidden', 'Acesso negado.', array( 'status' => 403 ) );
        }

        $sheet = DnD5e_Rules::build_character_sheet_data( $character );
        return rest_ensure_response( $sheet );
    }

    // =================================================================
    // CALLBACKS - INVENTÁRIO
    // =================================================================

    public static function get_inventory( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_inventory';
        
        $items = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE character_id = %d ORDER BY type ASC, name ASC",
            $request['id']
        ));

        return rest_ensure_response( $items );
    }

    public static function add_inventory_item( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_inventory';
        $data = $request->get_json_params();

        $insert = array(
            'character_id'       => intval( $request['id'] ),
            'name'               => sanitize_text_field( $data['name'] ?? '' ),
            'type'               => sanitize_text_field( $data['type'] ?? 'item' ),
            'subtype'            => sanitize_text_field( $data['subtype'] ?? '' ),
            'description'        => sanitize_textarea_field( $data['description'] ?? '' ),
            'quantity'           => intval( $data['quantity'] ?? 1 ),
            'weight'             => floatval( $data['weight'] ?? 0 ),
            'value_gp'           => floatval( $data['value_gp'] ?? 0 ),
            'rarity'             => sanitize_text_field( $data['rarity'] ?? 'common' ),
            'is_equipped'        => intval( $data['is_equipped'] ?? 0 ),
            'damage_dice'        => sanitize_text_field( $data['damage_dice'] ?? '' ),
            'damage_type'        => sanitize_text_field( $data['damage_type'] ?? '' ),
            'weapon_properties'  => sanitize_text_field( $data['weapon_properties'] ?? '' ),
            'armor_ac_base'      => intval( $data['armor_ac_base'] ?? 0 ),
            'armor_type'         => sanitize_text_field( $data['armor_type'] ?? '' ),
            'magic_properties'   => wp_json_encode( $data['magic_properties'] ?? array() ),
            'notes'              => sanitize_textarea_field( $data['notes'] ?? '' ),
        );

        $wpdb->insert( $table, $insert );
        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $wpdb->insert_id ) );

        return rest_ensure_response( $item );
    }

    public static function update_inventory_item( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_inventory';
        $data = $request->get_json_params();
        
        $allowed = array(
            'name', 'type', 'subtype', 'description', 'quantity', 'weight', 'value_gp',
            'rarity', 'is_equipped', 'is_attuned', 'damage_dice', 'damage_type',
            'weapon_properties', 'armor_ac_base', 'armor_type', 'notes',
        );

        $update = array();
        foreach ( $data as $key => $value ) {
            if ( in_array( $key, $allowed ) ) {
                $update[ $key ] = $value;
            }
        }

        if ( ! empty( $update ) ) {
            $wpdb->update( $table, $update, array( 'id' => $request['item_id'] ) );
        }

        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $request['item_id'] ) );
        return rest_ensure_response( $item );
    }

    public static function delete_inventory_item( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_inventory';
        $wpdb->delete( $table, array( 'id' => $request['item_id'] ) );
        return rest_ensure_response( array( 'deleted' => true ) );
    }

    // =================================================================
    // CALLBACKS - AVENTURAS
    // =================================================================

    public static function get_adventures( $request ) {
        $adventures = Adventure_Manager::get_adventures();
        return rest_ensure_response( $adventures );
    }

    public static function get_adventure( $request ) {
        $adventure = Adventure_Manager::get_adventure( $request['id'] );
        if ( ! $adventure ) {
            return new \WP_Error( 'not_found', 'Aventura não encontrada.', array( 'status' => 404 ) );
        }
        return rest_ensure_response( $adventure );
    }

    // =================================================================
    // CALLBACKS - SESSÃO
    // =================================================================

    public static function start_session( $request ) {
        $data = $request->get_json_params();
        $result = Session_Manager::start_session(
            intval( $data['adventure_id'] ),
            get_current_user_id()
        );

        if ( is_wp_error( $result ) ) return $result;
        return rest_ensure_response( $result );
    }

    public static function get_session_status( $request ) {
        $adventure_id = $request->get_param( 'adventure_id' );
        $session = Session_Manager::get_active_session( $adventure_id ? intval( $adventure_id ) : 0 );
        
        if ( ! $session ) {
            return rest_ensure_response( array( 'status' => 'inactive' ) );
        }
        return rest_ensure_response( $session );
    }

    public static function change_scene( $request ) {
        $data = $request->get_json_params();
        $result = Session_Manager::change_scene(
            intval( $data['session_id'] ),
            intval( $data['scene_id'] )
        );

        if ( is_wp_error( $result ) ) return $result;
        return rest_ensure_response( $result );
    }

    public static function show_npc( $request ) {
        $data = $request->get_json_params();
        $result = Session_Manager::show_npc(
            intval( $data['session_id'] ),
            intval( $data['npc_id'] )
        );

        if ( is_wp_error( $result ) ) return $result;
        return rest_ensure_response( $result );
    }

    public static function move_character( $request ) {
        $data = $request->get_json_params();
        $result = Session_Manager::update_character_position(
            intval( $data['session_id'] ),
            intval( $data['character_id'] ),
            intval( $data['x'] ),
            intval( $data['y'] )
        );
        return rest_ensure_response( array( 'success' => $result ) );
    }

    public static function update_combat( $request ) {
        $data = $request->get_json_params();
        $result = Session_Manager::update_combat(
            intval( $data['session_id'] ),
            $data['combat_data']
        );

        if ( is_wp_error( $result ) ) return $result;
        return rest_ensure_response( $result );
    }

    public static function session_control( $request ) {
        $data = $request->get_json_params();
        $session_id = intval( $data['session_id'] );

        if ( $request['action'] === 'pause' ) {
            $result = Session_Manager::pause_session( $session_id );
        } else {
            $result = Session_Manager::end_session( $session_id );
        }

        if ( is_wp_error( $result ) ) return $result;
        return rest_ensure_response( $result );
    }

    // =================================================================
    // CALLBACKS - DISPLAY (tela pública)
    // =================================================================

    public static function get_display( $request ) {
        $session_id = $request->get_param( 'session_id' );
        $data = Session_Manager::get_display_data( $session_id ? intval( $session_id ) : 0 );
        return rest_ensure_response( $data );
    }

    // =================================================================
    // CALLBACKS - DM CONTROLS
    // =================================================================

    public static function apply_condition( $request ) {
        $data = $request->get_json_params();
        $result = Character_Manager::apply_condition(
            intval( $request['char_id'] ),
            sanitize_text_field( $data['condition'] ),
            sanitize_text_field( $data['duration'] ?? '' ),
            sanitize_text_field( $data['source'] ?? '' )
        );

        return rest_ensure_response( array( 'success' => ! is_wp_error( $result ) ) );
    }

    public static function remove_condition( $request ) {
        $data = $request->get_json_params();
        $result = Character_Manager::remove_condition(
            intval( $request['char_id'] ),
            sanitize_text_field( $data['condition'] )
        );

        return rest_ensure_response( array( 'success' => $result ) );
    }

    public static function grant_xp( $request ) {
        $data = $request->get_json_params();
        $result = Character_Manager::grant_xp(
            intval( $request['char_id'] ),
            intval( $data['xp'] )
        );

        return rest_ensure_response( $result );
    }

    public static function modify_hp( $request ) {
        $data = $request->get_json_params();
        $char = Character_Manager::get_character( $request['char_id'] );

        if ( ! $char ) {
            return new \WP_Error( 'not_found', 'Personagem não encontrado.', array( 'status' => 404 ) );
        }

        $amount = intval( $data['amount'] ); // positivo = cura, negativo = dano
        $type = sanitize_text_field( $data['type'] ?? 'damage' ); // damage, heal, temp

        if ( $type === 'temp' ) {
            $new_hp = max( $char->temp_hp, abs( $amount ) );
            $update = array( 'temp_hp' => $new_hp );
        } elseif ( $type === 'heal' ) {
            $new_hp = min( $char->max_hp, $char->current_hp + abs( $amount ) );
            $update = array( 'current_hp' => $new_hp );
        } else {
            // Dano: primeiro remove temp HP, depois current HP
            $damage = abs( $amount );
            $temp = $char->temp_hp;
            
            if ( $temp > 0 ) {
                if ( $damage <= $temp ) {
                    $update = array( 'temp_hp' => $temp - $damage );
                } else {
                    $remaining = $damage - $temp;
                    $update = array(
                        'temp_hp'    => 0,
                        'current_hp' => max( 0, $char->current_hp - $remaining ),
                    );
                }
            } else {
                $update = array( 'current_hp' => max( 0, $char->current_hp - $damage ) );
            }
        }

        $result = Character_Manager::update_character( $char->id, $update );
        return rest_ensure_response( $result );
    }

    public static function dm_get_all_characters( $request ) {
        $adventure_id = $request->get_param( 'adventure_id' );
        $characters = Character_Manager::get_all_characters( $adventure_id ? intval( $adventure_id ) : 0 );
        return rest_ensure_response( $characters );
    }
}
