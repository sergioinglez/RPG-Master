<?php
/**
 * RPG Master Toolkit - Adventure Manager
 * 
 * Gerencia aventuras, cenas, e o fluxo narrativo
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class Adventure_Manager {

    /**
     * Retorna dados completos de uma aventura
     */
    public static function get_adventure( $adventure_id ) {
        $adventure = get_post( $adventure_id );
        if ( ! $adventure || $adventure->post_type !== 'rmt_adventure' ) {
            return null;
        }

        return array(
            'id'          => $adventure->ID,
            'title'       => $adventure->post_title,
            'description' => $adventure->post_content,
            'excerpt'     => $adventure->post_excerpt,
            'thumbnail'   => get_the_post_thumbnail_url( $adventure->ID, 'large' ),
            'status'      => $adventure->post_status,
            'scenes'      => self::get_adventure_scenes( $adventure->ID ),
            'npcs'        => self::get_adventure_npcs( $adventure->ID ),
            'monsters'    => self::get_adventure_monsters( $adventure->ID ),
            'maps'        => self::get_adventure_maps( $adventure->ID ),
            'created'     => $adventure->post_date,
        );
    }

    /**
     * Lista todas as aventuras
     */
    public static function get_adventures( $status = 'publish' ) {
        $posts = get_posts( array(
            'post_type'   => 'rmt_adventure',
            'post_status' => $status,
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ));

        $adventures = array();
        foreach ( $posts as $post ) {
            $scene_count = self::count_scenes( $post->ID );
            $adventures[] = array(
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'excerpt'     => $post->post_excerpt,
                'thumbnail'   => get_the_post_thumbnail_url( $post->ID, 'medium' ),
                'scene_count' => $scene_count,
                'status'      => $post->post_status,
            );
        }

        return $adventures;
    }

    /**
     * Retorna cenas de uma aventura ordenadas
     */
    public static function get_adventure_scenes( $adventure_id ) {
        $scenes = get_posts( array(
            'post_type'   => 'rmt_scene',
            'numberposts' => -1,
            'post_status' => 'publish',
            'meta_key'    => '_rmt_adventure_id',
            'meta_value'  => $adventure_id,
            'orderby'     => 'meta_value_num',
            'meta_key'    => '_rmt_scene_order',
            'order'       => 'ASC',
        ));

        $result = array();
        foreach ( $scenes as $scene ) {
            $result[] = self::format_scene( $scene );
        }

        return $result;
    }

    /**
     * Retorna dados formatados de uma cena
     */
    public static function format_scene( $scene ) {
        if ( is_numeric( $scene ) ) {
            $scene = get_post( $scene );
        }
        if ( ! $scene ) return null;

        return array(
            'id'              => $scene->ID,
            'title'           => $scene->post_title,
            'content'         => $scene->post_content,
            'thumbnail'       => get_the_post_thumbnail_url( $scene->ID, 'large' ),
            'adventure_id'    => get_post_meta( $scene->ID, '_rmt_adventure_id', true ),
            'order'           => intval( get_post_meta( $scene->ID, '_rmt_scene_order', true ) ),
            'scene_type'      => get_post_meta( $scene->ID, '_rmt_scene_type', true ),
            'narration_text'  => get_post_meta( $scene->ID, '_rmt_narration_text', true ),
            'dm_notes'        => get_post_meta( $scene->ID, '_rmt_dm_notes', true ),
            'map_id'          => get_post_meta( $scene->ID, '_rmt_map_id', true ),
            'display_image'   => get_post_meta( $scene->ID, '_rmt_display_image', true ),
            'ambient_music'   => get_post_meta( $scene->ID, '_rmt_ambient_music', true ),
            'linked_npcs'     => get_post_meta( $scene->ID, '_rmt_linked_npcs', true ),
            'linked_monsters' => get_post_meta( $scene->ID, '_rmt_linked_monsters', true ),
        );
    }

    /**
     * Retorna NPCs vinculados a uma aventura
     */
    public static function get_adventure_npcs( $adventure_id ) {
        // Buscar cenas da aventura e extrair NPCs
        $scenes = self::get_adventure_scenes( $adventure_id );
        $npc_ids = array();

        foreach ( $scenes as $scene ) {
            if ( ! empty( $scene['linked_npcs'] ) ) {
                $ids = array_map( 'intval', array_filter( explode( ',', $scene['linked_npcs'] ) ) );
                $npc_ids = array_merge( $npc_ids, $ids );
            }
        }

        $npc_ids = array_unique( $npc_ids );
        $npcs = array();

        foreach ( $npc_ids as $npc_id ) {
            $npc = self::format_npc( $npc_id );
            if ( $npc ) {
                $npcs[] = $npc;
            }
        }

        return $npcs;
    }

    /**
     * Formata dados de um NPC
     */
    public static function format_npc( $npc_id ) {
        $post = get_post( $npc_id );
        if ( ! $post || $post->post_type !== 'rmt_npc' ) return null;

        return array(
            'id'          => $post->ID,
            'name'        => $post->post_title,
            'description' => $post->post_content,
            'thumbnail'   => get_the_post_thumbnail_url( $post->ID, 'medium' ),
            'race'        => get_post_meta( $post->ID, '_rmt_npc_race', true ),
            'class'       => get_post_meta( $post->ID, '_rmt_npc_class', true ),
            'alignment'   => get_post_meta( $post->ID, '_rmt_npc_alignment', true ),
            'role'        => get_post_meta( $post->ID, '_rmt_npc_role', true ),
            'location'    => get_post_meta( $post->ID, '_rmt_npc_location', true ),
            'personality' => get_post_meta( $post->ID, '_rmt_npc_personality', true ),
            'dialogue'    => get_post_meta( $post->ID, '_rmt_npc_dialogue', true ),
            'secrets'     => get_post_meta( $post->ID, '_rmt_npc_secrets', true ),
            'voice_notes' => get_post_meta( $post->ID, '_rmt_npc_voice_notes', true ),
            'portrait'    => get_post_meta( $post->ID, '_rmt_npc_portrait', true ),
            'is_hostile'  => get_post_meta( $post->ID, '_rmt_npc_is_hostile', true ),
            'hp'          => get_post_meta( $post->ID, '_rmt_npc_hp', true ),
            'ac'          => get_post_meta( $post->ID, '_rmt_npc_ac', true ),
            'cr'          => get_post_meta( $post->ID, '_rmt_npc_cr', true ),
            'stats'       => json_decode( get_post_meta( $post->ID, '_rmt_npc_stats', true ), true ),
        );
    }

    /**
     * Retorna monstros de uma aventura
     */
    public static function get_adventure_monsters( $adventure_id ) {
        $scenes = self::get_adventure_scenes( $adventure_id );
        $monster_ids = array();

        foreach ( $scenes as $scene ) {
            if ( ! empty( $scene['linked_monsters'] ) ) {
                $ids = array_map( 'intval', array_filter( explode( ',', $scene['linked_monsters'] ) ) );
                $monster_ids = array_merge( $monster_ids, $ids );
            }
        }

        $monster_ids = array_unique( $monster_ids );
        $monsters = array();

        foreach ( $monster_ids as $mon_id ) {
            $monster = self::format_monster( $mon_id );
            if ( $monster ) {
                $monsters[] = $monster;
            }
        }

        return $monsters;
    }

    /**
     * Formata dados de um monstro
     */
    public static function format_monster( $monster_id ) {
        $post = get_post( $monster_id );
        if ( ! $post || $post->post_type !== 'rmt_monster' ) return null;

        $abilities = array( 'str', 'dex', 'con', 'int', 'wis', 'cha' );
        $stats = array();
        foreach ( $abilities as $ab ) {
            $stats[ $ab ] = intval( get_post_meta( $post->ID, '_rmt_mon_' . $ab, true ) );
        }

        return array(
            'id'           => $post->ID,
            'name'         => $post->post_title,
            'description'  => $post->post_content,
            'thumbnail'    => get_the_post_thumbnail_url( $post->ID, 'medium' ),
            'portrait'     => get_post_meta( $post->ID, '_rmt_mon_portrait', true ),
            'size'         => get_post_meta( $post->ID, '_rmt_mon_size', true ),
            'type'         => get_post_meta( $post->ID, '_rmt_mon_type', true ),
            'alignment'    => get_post_meta( $post->ID, '_rmt_mon_alignment', true ),
            'ac'           => get_post_meta( $post->ID, '_rmt_mon_ac', true ),
            'ac_type'      => get_post_meta( $post->ID, '_rmt_mon_ac_type', true ),
            'hp'           => get_post_meta( $post->ID, '_rmt_mon_hp', true ),
            'hp_dice'      => get_post_meta( $post->ID, '_rmt_mon_hp_dice', true ),
            'speed'        => get_post_meta( $post->ID, '_rmt_mon_speed', true ),
            'stats'        => $stats,
            'cr'           => get_post_meta( $post->ID, '_rmt_mon_cr', true ),
            'xp'           => get_post_meta( $post->ID, '_rmt_mon_xp', true ),
            'senses'       => get_post_meta( $post->ID, '_rmt_mon_senses', true ),
            'languages'    => get_post_meta( $post->ID, '_rmt_mon_languages', true ),
            'resistances'  => get_post_meta( $post->ID, '_rmt_mon_resistances', true ),
            'immunities'   => get_post_meta( $post->ID, '_rmt_mon_immunities', true ),
            'abilities'    => get_post_meta( $post->ID, '_rmt_mon_abilities', true ),
            'actions'      => get_post_meta( $post->ID, '_rmt_mon_actions', true ),
            'legendary'    => get_post_meta( $post->ID, '_rmt_mon_legendary', true ),
        );
    }

    /**
     * Retorna mapas de uma aventura
     */
    public static function get_adventure_maps( $adventure_id ) {
        $maps = get_posts( array(
            'post_type'   => 'rmt_map',
            'numberposts' => -1,
            'post_status' => 'publish',
            'meta_query'  => array(
                'relation' => 'OR',
                array(
                    'key'   => '_rmt_map_adventure_id',
                    'value' => $adventure_id,
                ),
                array(
                    'key'   => '_rmt_map_adventure_id',
                    'value' => '',
                ),
                array(
                    'key'     => '_rmt_map_adventure_id',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ));

        $result = array();
        foreach ( $maps as $map ) {
            $result[] = array(
                'id'          => $map->ID,
                'title'       => $map->post_title,
                'type'        => get_post_meta( $map->ID, '_rmt_map_type', true ),
                'image'       => get_post_meta( $map->ID, '_rmt_map_image', true ),
                'dm_image'    => get_post_meta( $map->ID, '_rmt_map_dm_image', true ),
                'grid_size'   => get_post_meta( $map->ID, '_rmt_map_grid_size', true ),
                'description' => get_post_meta( $map->ID, '_rmt_map_description', true ),
                'poi'         => json_decode( get_post_meta( $map->ID, '_rmt_map_poi', true ), true ),
            );
        }

        return $result;
    }

    /**
     * Conta cenas de uma aventura
     */
    public static function count_scenes( $adventure_id ) {
        global $wpdb;
        return intval( $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p 
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             WHERE p.post_type = 'rmt_scene' 
             AND p.post_status = 'publish' 
             AND pm.meta_key = '_rmt_adventure_id' 
             AND pm.meta_value = %s",
            $adventure_id
        )));
    }
}
