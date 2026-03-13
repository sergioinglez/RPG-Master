/**
 * RPG Master Toolkit - Admin JavaScript
 */

jQuery(document).ready(function($) {

    // =============================================
    // MEDIA UPLOADER (para campos de imagem)
    // =============================================
    $(document).on('click', '.rmt-upload-image', function(e) {
        e.preventDefault();
        
        var targetField = $(this).data('target');
        
        var mediaUploader = wp.media({
            title: 'Selecionar Imagem',
            button: { text: 'Usar esta imagem' },
            multiple: false,
            library: { type: 'image' }
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#' + targetField).val(attachment.url);
            
            // Preview
            var preview = $('#' + targetField).siblings('img');
            if (preview.length) {
                preview.attr('src', attachment.url);
            }
        });

        mediaUploader.open();
    });

    // =============================================
    // INICIAR SESSÃO
    // =============================================
    $(document).on('click', '.rmt-start-session', function() {
        var adventureId = $(this).data('adventure-id');
        var btn = $(this);
        
        if (!confirm('Iniciar sessão para esta aventura?')) return;
        
        btn.prop('disabled', true).text('Iniciando...');
        
        $.ajax({
            url: rmtAdmin.rest_url + 'session/start',
            method: 'POST',
            headers: {
                'X-WP-Nonce': rmtAdmin.nonce,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ adventure_id: adventureId }),
            success: function(session) {
                alert('Sessão iniciada! ID: ' + session.id);
                window.location.href = ajaxurl.replace('admin-ajax.php', 
                    'edit.php?post_type=rmt_adventure&page=rmt-active-session');
            },
            error: function(xhr) {
                alert('Erro: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                btn.prop('disabled', false).text('▶️ Iniciar Sessão');
            }
        });
    });

    // =============================================
    // CARREGAR PERSONAGENS NO PAINEL DO DM
    // =============================================
    if ($('#rmt-dm-characters-list').length) {
        $.ajax({
            url: rmtAdmin.rest_url + 'dm/characters',
            headers: { 'X-WP-Nonce': rmtAdmin.nonce },
            success: function(characters) {
                if (characters.length === 0) {
                    $('#rmt-dm-characters-list').html('<p>Nenhum personagem cadastrado ainda.</p>');
                    return;
                }

                var html = '<table class="wp-list-table widefat striped"><thead><tr>';
                html += '<th>Jogador</th><th>Personagem</th><th>Classe/Nível</th><th>HP</th>';
                html += '</tr></thead><tbody>';

                characters.forEach(function(c) {
                    var hpPercent = c.max_hp > 0 ? Math.round((c.current_hp / c.max_hp) * 100) : 0;
                    var hpColor = hpPercent > 50 ? '#46b450' : hpPercent > 25 ? '#ffb900' : '#dc3232';
                    
                    html += '<tr>';
                    html += '<td>' + (c.player_name || 'N/A') + '</td>';
                    html += '<td><strong>' + c.name + '</strong></td>';
                    html += '<td>' + c.class + ' Lv.' + c.level + '</td>';
                    html += '<td><div class="rmt-hp-bar">';
                    html += '<div class="rmt-hp-bar-track"><div class="rmt-hp-bar-fill" style="width:' + hpPercent + '%;background:' + hpColor + '"></div></div>';
                    html += c.current_hp + '/' + c.max_hp;
                    html += '</div></td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';
                $('#rmt-dm-characters-list').html(html);
            },
            error: function() {
                $('#rmt-dm-characters-list').html('<p>Erro ao carregar personagens.</p>');
            }
        });
    }
});
