jQuery(document).ready(function($) {
    var mediaUploader;

    $('#mc_audio_upload_button').on('click', function(e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Extend the wp.media object
        mediaUploader = wp.media({
            title: mcAudioMetabox.i18n.chooseAudio,
            button: {
                text: mcAudioMetabox.i18n.useAudio
            },
            library: {
                type: 'audio'
            },
            multiple: false
        });

        // When a file is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Set the audio URL in the hidden field
            $('#mc_audio_url').val(attachment.url);
            
            // Update the preview
            var preview = '<div style="padding: 10px; background: #f0f0f1; border-radius: 4px;">' +
                         '<audio controls style="width: 100%; margin-bottom: 5px;">' +
                         '<source src="' + attachment.url + '" type="audio/mpeg">' +
                         '</audio>' +
                         '<div style="font-size: 11px; color: #666; word-break: break-all;">' +
                         attachment.filename +
                         '</div>' +
                         '</div>';
            
            $('#mc_audio_preview').html(preview);
            
            // Show the remove button
            $('#mc_audio_remove_button').show();
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Remove audio button
    $('#mc_audio_remove_button').on('click', function(e) {
        e.preventDefault();
        
        // Clear the audio URL
        $('#mc_audio_url').val('');
        
        // Update the preview
        var preview = '<div style="padding: 10px; background: #f0f0f1; border-radius: 4px; color: #666; font-style: italic;">' +
                     mcAudioMetabox.i18n.noAudioSelected +
                     '</div>';
        
        $('#mc_audio_preview').html(preview);
        
        // Hide the remove button
        $(this).hide();
    });
});

