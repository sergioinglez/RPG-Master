<?php
/**
 * RPG Master Toolkit - Character Manager
 * 
 * CRUD de personagens, validações, limite de 5 por jogador
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class Character_Manager {

    const MAX_CHARACTERS_PER_USER = 5;

    /**
     * Cria um novo personagem
     */
    public static function create_character( $user_id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_characters';

        // Verificar limite
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND is_active = 1",
            $user_id
        ));

        if ( $count >= self::MAX_CHARACTERS_PER_USER ) {
            return new \WP_Error( 'max_characters', 'Limite de ' . self::MAX_CHARACTERS_PER_USER . ' personagens atingido.', array( 'status' => 400 ) );
        }

        // Validar dados obrigatórios
        if ( empty( $data['name'] ) || empty( $data['race'] ) || empty( $data['class'] ) ) {
            return new \WP_Error( 'missing_data', 'Nome, raça e classe são obrigatórios.', array( 'status' => 400 ) );
        }

        // Validar raça e classe
        $races = DnD5e_Rules::get_races();
        $classes = DnD5e_Rules::get_classes();

        if ( ! isset( $races[ $data['race'] ] ) ) {
            return new \WP_Error( 'invalid_race', 'Raça inválida.', array( 'status' => 400 ) );
        }
        if ( ! isset( $classes[ $data['class'] ] ) ) {
            return new \WP_Error( 'invalid_class', 'Classe inválida.', array( 'status' => 400 ) );
        }

        $class_data = $classes[ $data['class'] ];
        $level = isset( $data['level'] ) ? max( 1, min( 20, intval( $data['level'] ) ) ) : 1;
        $con = isset( $data['constitution'] ) ? intval( $data['constitution'] ) : 10;

        // Calcular HP e proficiência
        $max_hp = DnD5e_Rules::calculate_max_hp( $data['class'], $level, $con );
        $prof_bonus = DnD5e_Rules::proficiency_bonus( $level );

        // Configurar saving throws da classe
        $save_data = array();
        foreach ( array( 'str', 'dex', 'con', 'int', 'wis', 'cha' ) as $ability ) {
            $save_data[ 'save_' . $ability ] = in_array( $ability, $class_data['saving_throws'] ) ? 1 : 0;
        }

        // Montar dados para inserção
        $insert_data = array(
            'user_id'            => $user_id,
            'adventure_id'       => isset( $data['adventure_id'] ) ? intval( $data['adventure_id'] ) : 0,
            'name'               => sanitize_text_field( $data['name'] ),
            'race'               => sanitize_text_field( $data['race'] ),
            'subrace'            => isset( $data['subrace'] ) ? sanitize_text_field( $data['subrace'] ) : '',
            'class'              => sanitize_text_field( $data['class'] ),
            'subclass'           => isset( $data['subclass'] ) ? sanitize_text_field( $data['subclass'] ) : '',
            'level'              => $level,
            'experience_points'  => DnD5e_Rules::xp_for_level( $level ),
            'background'         => isset( $data['background'] ) ? sanitize_text_field( $data['background'] ) : '',
            'alignment'          => isset( $data['alignment'] ) ? sanitize_text_field( $data['alignment'] ) : '',
            
            // Atributos
            'strength'           => isset( $data['strength'] ) ? max( 1, min( 30, intval( $data['strength'] ) ) ) : 10,
            'dexterity'          => isset( $data['dexterity'] ) ? max( 1, min( 30, intval( $data['dexterity'] ) ) ) : 10,
            'constitution'       => $con,
            'intelligence'       => isset( $data['intelligence'] ) ? max( 1, min( 30, intval( $data['intelligence'] ) ) ) : 10,
            'wisdom'             => isset( $data['wisdom'] ) ? max( 1, min( 30, intval( $data['wisdom'] ) ) ) : 10,
            'charisma'           => isset( $data['charisma'] ) ? max( 1, min( 30, intval( $data['charisma'] ) ) ) : 10,

            // Combate
            'max_hp'             => $max_hp,
            'current_hp'         => $max_hp,
            'temp_hp'            => 0,
            'armor_class'        => 10 + DnD5e_Rules::ability_modifier( isset( $data['dexterity'] ) ? intval( $data['dexterity'] ) : 10 ),
            'initiative_bonus'   => 0,
            'speed'              => $races[ $data['race'] ]['speed'],
            'hit_dice'           => $level . 'd' . $class_data['hit_die'],
            'hit_dice_remaining' => $level,
            'proficiency_bonus'  => $prof_bonus,

            // Saving throws
            'save_str' => $save_data['save_str'],
            'save_dex' => $save_data['save_dex'],
            'save_con' => $save_data['save_con'],
            'save_int' => $save_data['save_int'],
            'save_wis' => $save_data['save_wis'],
            'save_cha' => $save_data['save_cha'],

            // Dinheiro inicial
            'gold'    => isset( $data['gold'] ) ? floatval( $data['gold'] ) : 0,
            'silver'  => 0,
            'copper'  => 0,

            // Aparência
            'avatar_url'          => isset( $data['avatar_url'] ) ? esc_url_raw( $data['avatar_url'] ) : '',
            'personality_traits'  => isset( $data['personality_traits'] ) ? sanitize_textarea_field( $data['personality_traits'] ) : '',
            'ideals'              => isset( $data['ideals'] ) ? sanitize_textarea_field( $data['ideals'] ) : '',
            'bonds'               => isset( $data['bonds'] ) ? sanitize_textarea_field( $data['bonds'] ) : '',
            'flaws'               => isset( $data['flaws'] ) ? sanitize_textarea_field( $data['flaws'] ) : '',
            'backstory'           => isset( $data['backstory'] ) ? sanitize_textarea_field( $data['backstory'] ) : '',

            // JSON
            'features_traits'          => wp_json_encode( array() ),
            'proficiencies_languages'  => wp_json_encode( array(
                'languages' => $races[ $data['race'] ]['languages'],
                'armor'     => $class_data['armor_prof'],
                'weapons'   => $class_data['weapon_prof'],
            )),
            'conditions'  => wp_json_encode( array() ),
            'extra_data'  => wp_json_encode( array() ),
            'spell_data'  => wp_json_encode( array() ),

            'is_active' => 1,
            'is_dead'   => 0,
        );

        // Skills da classe (se fornecidos)
        $all_skills = DnD5e_Rules::get_skills();
        foreach ( array_keys( $all_skills ) as $skill_key ) {
            $field = 'skill_' . $skill_key;
            $insert_data[ $field ] = isset( $data[ $field ] ) ? min( 2, max( 0, intval( $data[ $field ] ) ) ) : 0;
        }

        $result = $wpdb->insert( $table, $insert_data );

        if ( $result === false ) {
            return new \WP_Error( 'db_error', 'Erro ao criar personagem.', array( 'status' => 500 ) );
        }

        $character_id = $wpdb->insert_id;
        return self::get_character( $character_id );
    }

    /**
     * Busca um personagem por ID
     */
    public static function get_character( $character_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_characters';
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND is_active = 1",
            $character_id
        ));
    }

    /**
     * Busca todos os personagens de um usuário
     */
    public static function get_user_characters( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_characters';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND is_active = 1 ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Busca todos os personagens (para DMs)
     */
    public static function get_all_characters( $adventure_id = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_characters';

        if ( $adventure_id > 0 ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT c.*, u.display_name as player_name FROM $table c 
                 LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
                 WHERE c.adventure_id = %d AND c.is_active = 1 ORDER BY c.name ASC",
                $adventure_id
            ));
        }

        return $wpdb->get_results(
            "SELECT c.*, u.display_name as player_name FROM $table c 
             LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
             WHERE c.is_active = 1 ORDER BY c.name ASC"
        );
    }

    /**
     * Atualiza um personagem
     */
    public static function update_character( $character_id, $data, $user_id = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_characters';

        // Verificar se o personagem existe e pertence ao user (se não for admin/DM)
        $character = self::get_character( $character_id );
        if ( ! $character ) {
            return new \WP_Error( 'not_found', 'Personagem não encontrado.', array( 'status' => 404 ) );
        }

        if ( $user_id > 0 && $character->user_id != $user_id && ! current_user_can( 'rmt_manage_all_characters' ) ) {
            return new \WP_Error( 'forbidden', 'Você não pode editar este personagem.', array( 'status' => 403 ) );
        }

        // Campos permitidos para atualização
        $allowed_fields = array(
            'name', 'subrace', 'subclass', 'level', 'experience_points', 'background', 'alignment',
            'strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma',
            'max_hp', 'current_hp', 'temp_hp', 'armor_class', 'initiative_bonus', 'speed',
            'hit_dice', 'hit_dice_remaining', 'death_saves_success', 'death_saves_failure',
            'proficiency_bonus',
            'save_str', 'save_dex', 'save_con', 'save_int', 'save_wis', 'save_cha',
            'gold', 'silver', 'copper', 'electrum', 'platinum',
            'avatar_url', 'age', 'height', 'weight', 'eyes', 'skin', 'hair',
            'personality_traits', 'ideals', 'bonds', 'flaws', 'backstory', 'appearance_notes',
            'features_traits', 'proficiencies_languages', 'spell_data', 'extra_data', 'conditions',
            'adventure_id', 'is_dead',
        );

        // Skills
        $all_skills = DnD5e_Rules::get_skills();
        foreach ( array_keys( $all_skills ) as $skill_key ) {
            $allowed_fields[] = 'skill_' . $skill_key;
        }

        $update_data = array();
        foreach ( $data as $key => $value ) {
            if ( in_array( $key, $allowed_fields ) ) {
                $update_data[ $key ] = $value;
            }
        }

        if ( empty( $update_data ) ) {
            return new \WP_Error( 'no_data', 'Nenhum dado para atualizar.', array( 'status' => 400 ) );
        }

        // Recalcular HP se nível ou constituição mudaram
        if ( isset( $update_data['level'] ) || isset( $update_data['constitution'] ) ) {
            $new_level = isset( $update_data['level'] ) ? intval( $update_data['level'] ) : $character->level;
            $new_con = isset( $update_data['constitution'] ) ? intval( $update_data['constitution'] ) : $character->constitution;
            $update_data['max_hp'] = DnD5e_Rules::calculate_max_hp( $character->class, $new_level, $new_con );
            $update_data['proficiency_bonus'] = DnD5e_Rules::proficiency_bonus( $new_level );
            $update_data['hit_dice'] = $new_level . 'd' . DnD5e_Rules::get_classes()[ $character->class ]['hit_die'];
        }

        $result = $wpdb->update( $table, $update_data, array( 'id' => $character_id ) );

        if ( $result === false ) {
            return new \WP_Error( 'db_error', 'Erro ao atualizar personagem.', array( 'status' => 500 ) );
        }

        return self::get_character( $character_id );
    }

    /**
     * Deleta (soft delete) um personagem
     */
    public static function delete_character( $character_id, $user_id = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rmt_characters';

        $character = self::get_character( $character_id );
        if ( ! $character ) {
            return new \WP_Error( 'not_found', 'Personagem não encontrado.', array( 'status' => 404 ) );
        }

        if ( $user_id > 0 && $character->user_id != $user_id && ! current_user_can( 'rmt_manage_all_characters' ) ) {
            return new \WP_Error( 'forbidden', 'Você não pode deletar este personagem.', array( 'status' => 403 ) );
        }

        $result = $wpdb->update( $table, array( 'is_active' => 0 ), array( 'id' => $character_id ) );

        return $result !== false;
    }

    /**
     * Aplica condição a um personagem (usado pelo DM)
     */
    public static function apply_condition( $character_id, $condition_key, $duration = '', $source = '' ) {
        $character = self::get_character( $character_id );
        if ( ! $character ) return false;

        $conditions = json_decode( $character->conditions, true ) ?: array();
        
        // Verificar se já tem a condição
        foreach ( $conditions as &$c ) {
            if ( $c['type'] === $condition_key ) {
                $c['duration'] = $duration;
                $c['source'] = $source;
                $c['applied_at'] = current_time( 'mysql' );
                return self::update_character( $character_id, array( 'conditions' => wp_json_encode( $conditions ) ) );
            }
        }

        // Adicionar nova condição
        $conditions[] = array(
            'type'       => $condition_key,
            'duration'   => $duration,
            'source'     => $source,
            'applied_at' => current_time( 'mysql' ),
        );

        return self::update_character( $character_id, array( 'conditions' => wp_json_encode( $conditions ) ) );
    }

    /**
     * Remove condição de um personagem
     */
    public static function remove_condition( $character_id, $condition_key ) {
        $character = self::get_character( $character_id );
        if ( ! $character ) return false;

        $conditions = json_decode( $character->conditions, true ) ?: array();
        $conditions = array_filter( $conditions, function( $c ) use ( $condition_key ) {
            return $c['type'] !== $condition_key;
        });

        return self::update_character( $character_id, array( 'conditions' => wp_json_encode( array_values( $conditions ) ) ) );
    }

    /**
     * Concede XP a um personagem e verifica level up
     */
    public static function grant_xp( $character_id, $xp_amount ) {
        $character = self::get_character( $character_id );
        if ( ! $character ) return false;

        $new_xp = $character->experience_points + $xp_amount;
        $new_level = DnD5e_Rules::level_from_xp( $new_xp );
        $leveled_up = $new_level > $character->level;

        $update = array(
            'experience_points' => $new_xp,
            'level'             => $new_level,
        );

        $result = self::update_character( $character_id, $update );

        return array(
            'character'  => $result,
            'xp_gained'  => $xp_amount,
            'leveled_up' => $leveled_up,
            'new_level'  => $new_level,
        );
    }
}
