<?php
/**
 * RPG Master Theme - Index (fallback)
 */
get_header();
?>
<div class="rmt-page">
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:40px;">
        <div style="font-size:80px;margin-bottom:20px;">🎲</div>
        <h1 style="font-family:'Cinzel',serif;color:#ffd700;font-size:3em;margin-bottom:10px;">RPG Master Toolkit</h1>
        <p style="color:#aaa;font-size:1.2em;margin-bottom:30px;">Ferramenta completa de apoio para mestres e jogadores de RPG</p>
        
        <div style="display:flex;gap:15px;flex-wrap:wrap;justify-content:center;">
            <?php if ( is_user_logged_in() ) : ?>
                <?php if ( rmt_user_can_dm() ) : ?>
                    <a href="<?php echo home_url('/rpg-master/'); ?>" class="rmt-btn rmt-btn-gold" style="text-decoration:none;padding:15px 30px;font-size:16px;">
                        🎲 Painel do Mestre
                    </a>
                <?php endif; ?>
                <a href="<?php echo home_url('/rpg-player/'); ?>" class="rmt-btn rmt-btn-primary" style="text-decoration:none;padding:15px 30px;font-size:16px;">
                    ⚔️ Painel do Jogador
                </a>
                <a href="<?php echo home_url('/rpg-display/'); ?>" class="rmt-btn" style="text-decoration:none;padding:15px 30px;font-size:16px;background:#0f3460;color:white;border-radius:8px;" target="_blank">
                    📺 Tela de Exibição
                </a>
            <?php else : ?>
                <a href="<?php echo wp_login_url( home_url('/rpg-player/') ); ?>" class="rmt-btn rmt-btn-gold" style="text-decoration:none;padding:15px 30px;font-size:16px;">
                    🔑 Entrar
                </a>
            <?php endif; ?>
        </div>

        <?php if ( is_user_logged_in() ) : ?>
            <p style="color:#555;font-size:13px;margin-top:30px;">
                Logado como: <?php echo wp_get_current_user()->display_name; ?> 
                (<?php echo rmt_get_user_rpg_role(); ?>)
                | <a href="<?php echo wp_logout_url( home_url() ); ?>" style="color:#dc3232;">Sair</a>
            </p>
        <?php endif; ?>
    </div>
</div>
<?php
get_footer();
