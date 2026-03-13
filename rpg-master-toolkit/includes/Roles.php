<?php
/**
 * RPG Master Toolkit - Roles & Capabilities
 * 
 * Mapeia os papéis do WordPress para o sistema RPG:
 * - Assinante / Colaborador = Jogador
 * - Autor / Editor = Pode mestrar (aventuras pré-configuradas) e jogar
 * - Administrador = Pode tudo
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class Roles {

    /**
     * Configura as capabilities RPG nos roles existentes do WordPress
     */
    public static function setup_roles() {
        
        // =============================================
        // ADMINISTRADOR - Pode TUDO
        // =============================================
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            // Aventuras
            $admin->add_cap( 'rmt_manage_adventures' );   // Criar/editar/deletar aventuras
            $admin->add_cap( 'rmt_configure_adventures' ); // Configurar aventuras (cenas, NPCs, mapas)
            $admin->add_cap( 'rmt_run_adventures' );       // Mestrar aventuras
            
            // Personagens
            $admin->add_cap( 'rmt_create_characters' );    // Criar personagens
            $admin->add_cap( 'rmt_manage_all_characters' );// Editar qualquer personagem
            $admin->add_cap( 'rmt_view_all_characters' );  // Ver todos os personagens
            
            // Sessão
            $admin->add_cap( 'rmt_manage_sessions' );      // Gerenciar sessões ativas
            $admin->add_cap( 'rmt_control_players' );      // Controlar stats dos jogadores
            
            // NPCs e Monstros
            $admin->add_cap( 'rmt_manage_npcs' );
            $admin->add_cap( 'rmt_manage_monsters' );
            
            // Mapas
            $admin->add_cap( 'rmt_manage_maps' );
            
            // Display
            $admin->add_cap( 'rmt_view_display' );         // Ver tela de exibição
            
            // Sistema
            $admin->add_cap( 'rmt_manage_settings' );      // Configurações do plugin
        }

        // =============================================
        // EDITOR - Pode mestrar e jogar
        // =============================================
        $editor = get_role( 'editor' );
        if ( $editor ) {
            $editor->add_cap( 'rmt_run_adventures' );
            $editor->add_cap( 'rmt_create_characters' );
            $editor->add_cap( 'rmt_view_all_characters' );
            $editor->add_cap( 'rmt_manage_sessions' );
            $editor->add_cap( 'rmt_control_players' );
            $editor->add_cap( 'rmt_manage_npcs' );
            $editor->add_cap( 'rmt_manage_monsters' );
            $editor->add_cap( 'rmt_view_display' );
        }

        // =============================================
        // AUTOR - Pode mestrar (pré-configuradas) e jogar
        // =============================================
        $author = get_role( 'author' );
        if ( $author ) {
            $author->add_cap( 'rmt_run_adventures' );
            $author->add_cap( 'rmt_create_characters' );
            $author->add_cap( 'rmt_view_all_characters' );
            $author->add_cap( 'rmt_manage_sessions' );
            $author->add_cap( 'rmt_control_players' );
            $author->add_cap( 'rmt_view_display' );
        }

        // =============================================
        // COLABORADOR - Jogador
        // =============================================
        $contributor = get_role( 'contributor' );
        if ( $contributor ) {
            $contributor->add_cap( 'rmt_create_characters' );
            $contributor->add_cap( 'rmt_view_display' );
        }

        // =============================================
        // ASSINANTE - Jogador
        // =============================================
        $subscriber = get_role( 'subscriber' );
        if ( $subscriber ) {
            $subscriber->add_cap( 'rmt_create_characters' );
            $subscriber->add_cap( 'rmt_view_display' );
        }
    }

    /**
     * Remove todas as capabilities RPG (para desativação limpa)
     */
    public static function remove_roles() {
        $caps = array(
            'rmt_manage_adventures',
            'rmt_configure_adventures',
            'rmt_run_adventures',
            'rmt_create_characters',
            'rmt_manage_all_characters',
            'rmt_view_all_characters',
            'rmt_manage_sessions',
            'rmt_control_players',
            'rmt_manage_npcs',
            'rmt_manage_monsters',
            'rmt_manage_maps',
            'rmt_view_display',
            'rmt_manage_settings',
        );

        $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

        foreach ( $roles as $role_name ) {
            $role = get_role( $role_name );
            if ( $role ) {
                foreach ( $caps as $cap ) {
                    $role->remove_cap( $cap );
                }
            }
        }
    }

    /**
     * Retorna as capabilities de um papel RPG
     */
    public static function get_rpg_capabilities( $rpg_role ) {
        $caps = array(
            'admin' => array(
                'rmt_manage_adventures', 'rmt_configure_adventures', 'rmt_run_adventures',
                'rmt_create_characters', 'rmt_manage_all_characters', 'rmt_view_all_characters',
                'rmt_manage_sessions', 'rmt_control_players',
                'rmt_manage_npcs', 'rmt_manage_monsters', 'rmt_manage_maps',
                'rmt_view_display', 'rmt_manage_settings',
            ),
            'dm' => array(
                'rmt_run_adventures', 'rmt_create_characters', 'rmt_view_all_characters',
                'rmt_manage_sessions', 'rmt_control_players',
                'rmt_manage_npcs', 'rmt_manage_monsters', 'rmt_view_display',
            ),
            'player' => array(
                'rmt_create_characters', 'rmt_view_display',
            ),
        );

        return isset( $caps[ $rpg_role ] ) ? $caps[ $rpg_role ] : array();
    }
}
