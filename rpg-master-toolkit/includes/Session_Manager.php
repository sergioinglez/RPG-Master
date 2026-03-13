<?php
/**
 * RPG Master Toolkit - Session Manager
 * 
 * Gerencia a sessão ativa de jogo (tempo real entre mestre e display)
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class Session_Manager {

    /**
     * Inicia ou retoma uma sessão
     */
    public static function start_session( $adventure_id, $dm_user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        // Verificar se já existe sessão ativa para esta aventura
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE adventure_id = %d AND status != 'ended'",
            $adventure_id
        ));

        if ( $existing ) {
            // Retomar sessão
            $wpdb->update( $table, array( 'status' => 'active' ), array( 'id' => $existing->id ) );
            return self::get_session( $existing->id );
        }

        // Criar nova sessão
        $adventure = Adventure_Manager::get_adventure( $adventure_id );
        if ( ! $adventure ) {
            return new \WP_Error( 'not_found', 'Aventura não encontrada.' );
        }

        // Pegar primeira cena
        $first_scene = ! empty( $adventure['scenes'] ) ? $adventure['scenes'][0] : null;

        $wpdb->insert( $table, array(
            'adventure_id'       => $adventure_id,
            'dm_user_id'         => $dm_user_id,
            'current_scene_id'   => $first_scene ? $first_scene['id'] : 0,
            'current_map_id'     => $first_scene && $first_scene['map_id'] ? $first_scene['map_id'] : 0,
            'scene_type'         => $first_scene ? $first_scene['scene_type'] : 'exploration',
            'scene_title'        => $first_scene ? $first_scene['title'] : '',
            'scene_description'  => $first_scene ? $first_scene['narration_text'] : '',
            'scene_image_url'    => $first_scene ? $first_scene['display_image'] : '',
            'combat_data'        => wp_json_encode( array() ),
            'character_positions'=> wp_json_encode( array() ),
            'visibility_data'    => wp_json_encode( array() ),
            'action_log'         => wp_json_encode( array() ),
            'status'             => 'active',
        ));

        return self::get_session( $wpdb->insert_id );
    }

    /**
     * Busca sessão por ID
     */
    public static function get_session( $session_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $session_id
        ));
    }

    /**
     * Busca sessão ativa de uma aventura
     */
    public static function get_active_session( $adventure_id = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        if ( $adventure_id > 0 ) {
            return $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $table WHERE adventure_id = %d AND status = 'active' ORDER BY updated_at DESC LIMIT 1",
                $adventure_id
            ));
        }

        // Retorna qualquer sessão ativa
        return $wpdb->get_row(
            "SELECT * FROM $table WHERE status = 'active' ORDER BY updated_at DESC LIMIT 1"
        );
    }

    /**
     * Muda a cena atual (ação do mestre)
     */
    public static function change_scene( $session_id, $scene_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $scene = Adventure_Manager::format_scene( $scene_id );
        if ( ! $scene ) {
            return new \WP_Error( 'not_found', 'Cena não encontrada.' );
        }

        $update = array(
            'current_scene_id'  => $scene_id,
            'current_map_id'    => $scene['map_id'] ?: 0,
            'scene_type'        => $scene['scene_type'],
            'scene_title'       => $scene['title'],
            'scene_description' => $scene['narration_text'],
            'scene_image_url'   => $scene['display_image'],
            'active_npc_id'     => 0,
            'combat_data'       => wp_json_encode( array() ),
        );

        // Se é combate, preparar dados
        if ( $scene['scene_type'] === 'combat' && ! empty( $scene['linked_monsters'] ) ) {
            $monster_ids = array_map( 'intval', array_filter( explode( ',', $scene['linked_monsters'] ) ) );
            $combat = array(
                'round'       => 0,
                'turn'        => 0,
                'initiative'  => array(),
                'monsters'    => array(),
                'is_active'   => false,
            );

            foreach ( $monster_ids as $mid ) {
                $monster = Adventure_Manager::format_monster( $mid );
                if ( $monster ) {
                    $combat['monsters'][] = array(
                        'id'         => $monster['id'],
                        'name'       => $monster['name'],
                        'max_hp'     => intval( $monster['hp'] ),
                        'current_hp' => intval( $monster['hp'] ),
                        'ac'         => intval( $monster['ac'] ),
                        'initiative' => 0,
                        'conditions' => array(),
                        'portrait'   => $monster['portrait'],
                    );
                }
            }

            $update['combat_data'] = wp_json_encode( $combat );
        }

        // Log da ação
        self::add_to_log( $session_id, 'scene_change', 'Cena mudou para: ' . $scene['title'] );

        $wpdb->update( $table, $update, array( 'id' => $session_id ) );
        return self::get_session( $session_id );
    }

    /**
     * Ativa NPC na tela de exibição
     */
    public static function show_npc( $session_id, $npc_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $npc = Adventure_Manager::format_npc( $npc_id );
        if ( ! $npc ) {
            return new \WP_Error( 'not_found', 'NPC não encontrado.' );
        }

        $wpdb->update( $table, array(
            'active_npc_id'    => $npc_id,
            'scene_type'       => 'social',
            'scene_image_url'  => $npc['portrait'] ?: '',
        ), array( 'id' => $session_id ) );

        self::add_to_log( $session_id, 'npc_interact', 'Interação com NPC: ' . $npc['name'] );

        return self::get_session( $session_id );
    }

    /**
     * Atualiza posição de um personagem no mapa
     */
    public static function update_character_position( $session_id, $character_id, $x, $y ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $session = self::get_session( $session_id );
        if ( ! $session ) return false;

        $positions = json_decode( $session->character_positions, true ) ?: array();

        // Atualizar ou adicionar posição
        $found = false;
        foreach ( $positions as &$pos ) {
            if ( $pos['character_id'] == $character_id ) {
                $pos['x'] = intval( $x );
                $pos['y'] = intval( $y );
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            $character = Character_Manager::get_character( $character_id );
            $positions[] = array(
                'character_id' => intval( $character_id ),
                'name'         => $character ? $character->name : 'Unknown',
                'avatar'       => $character ? $character->avatar_url : '',
                'x'            => intval( $x ),
                'y'            => intval( $y ),
            );
        }

        $wpdb->update( $table, array(
            'character_positions' => wp_json_encode( $positions ),
        ), array( 'id' => $session_id ) );

        return true;
    }

    /**
     * Atualiza dados de combate
     */
    public static function update_combat( $session_id, $combat_data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $wpdb->update( $table, array(
            'combat_data' => wp_json_encode( $combat_data ),
            'scene_type'  => 'combat',
        ), array( 'id' => $session_id ) );

        return self::get_session( $session_id );
    }

    /**
     * Pausa a sessão
     */
    public static function pause_session( $session_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $wpdb->update( $table, array( 'status' => 'paused' ), array( 'id' => $session_id ) );
        self::add_to_log( $session_id, 'session', 'Sessão pausada' );
        return self::get_session( $session_id );
    }

    /**
     * Encerra a sessão
     */
    public static function end_session( $session_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $wpdb->update( $table, array( 'status' => 'ended' ), array( 'id' => $session_id ) );
        self::add_to_log( $session_id, 'session', 'Sessão encerrada' );
        return self::get_session( $session_id );
    }

    /**
     * Adiciona entrada ao log da sessão
     */
    public static function add_to_log( $session_id, $type, $message ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_active_session';

        $session = self::get_session( $session_id );
        if ( ! $session ) return;

        $log = json_decode( $session->action_log, true ) ?: array();
        $log[] = array(
            'type'    => $type,
            'message' => $message,
            'time'    => current_time( 'mysql' ),
            'user_id' => get_current_user_id(),
        );

        // Manter apenas os últimos 100 logs
        if ( count( $log ) > 100 ) {
            $log = array_slice( $log, -100 );
        }

        $wpdb->update( $table, array( 'action_log' => wp_json_encode( $log ) ), array( 'id' => $session_id ) );
    }

    /**
     * Retorna dados da sessão para a tela de exibição (sem dados privados do DM)
     */
    public static function get_display_data( $session_id = 0 ) {
        $session = $session_id ? self::get_session( $session_id ) : self::get_active_session();
        
        if ( ! $session || $session->status !== 'active' ) {
            return array(
                'status'  => 'inactive',
                'message' => 'Nenhuma sessão ativa no momento.',
            );
        }

        $data = array(
            'status'              => 'active',
            'session_id'          => $session->id,
            'adventure_id'        => $session->adventure_id,
            'scene_type'          => $session->scene_type,
            'scene_title'         => $session->scene_title,
            'scene_image_url'     => $session->scene_image_url,
            'character_positions' => json_decode( $session->character_positions, true ) ?: array(),
            'updated_at'          => $session->updated_at,
        );

        // Mapa da cena
        if ( $session->current_map_id ) {
            $map_image = get_post_meta( $session->current_map_id, '_rmt_map_image', true );
            $grid_size = get_post_meta( $session->current_map_id, '_rmt_map_grid_size', true );
            $data['map'] = array(
                'id'        => $session->current_map_id,
                'image'     => $map_image,
                'grid_size' => intval( $grid_size ),
                'title'     => get_the_title( $session->current_map_id ),
            );
        }

        // NPC ativo
        if ( $session->active_npc_id ) {
            $npc = Adventure_Manager::format_npc( $session->active_npc_id );
            if ( $npc ) {
                $data['active_npc'] = array(
                    'name'     => $npc['name'],
                    'portrait' => $npc['portrait'],
                    'race'     => $npc['race'],
                    'role'     => $npc['role'],
                );
            }
        }

        // Combate ativo
        if ( $session->scene_type === 'combat' ) {
            $combat = json_decode( $session->combat_data, true );
            if ( $combat && ! empty( $combat['is_active'] ) ) {
                $display_combat = array(
                    'round'    => $combat['round'],
                    'monsters' => array(),
                );
                foreach ( $combat['monsters'] as $m ) {
                    $display_combat['monsters'][] = array(
                        'name'       => $m['name'],
                        'current_hp' => $m['current_hp'],
                        'max_hp'     => $m['max_hp'],
                        'portrait'   => $m['portrait'] ?? '',
                        'conditions' => $m['conditions'] ?? array(),
                    );
                }
                $data['combat'] = $display_combat;
            }
        }

        return $data;
    }
}
