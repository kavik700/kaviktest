jQuery(document).ready(function($) {
    // Initialize custom audio player controls
    $('.mc-custom-audio-player').each(function() {
        var player = $(this);
        var audio = player.find('.mc-audio-element')[0];
        var playBtn = player.find('.mc-audio-play-btn');
        var timeDisplay = player.find('.mc-audio-time');
        var volumeBtn = player.find('.mc-audio-volume-btn');
        
        if (!audio) {
            console.error('Audio element not found in player');
            return;
        }
        
        // Format time helper
        function formatTime(seconds) {
            if (isNaN(seconds) || !isFinite(seconds)) {
                return '0:00';
            }
            var mins = Math.floor(seconds / 60);
            var secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        // Update time display
        function updateTime() {
            var current = formatTime(audio.currentTime);
            var duration = formatTime(audio.duration);
            timeDisplay.text(current + ' / ' + duration);
        }
        
        // Load metadata immediately and wait for it
        $(audio).on('loadedmetadata', function() {
            updateTime();
        });
        
        if (audio.readyState >= 1) {
            updateTime();
        } else {
            audio.load();
        }
        
        // Play/Pause button
        playBtn.on('click', function(e) {
            e.preventDefault();
            console.log('Play button clicked');
            
            if ($(this).prop('disabled')) {
                console.log('Button is disabled');
                return false;
            }
            
            if (audio.paused) {
                console.log('Playing audio');
                audio.play().catch(function(error) {
                    console.error('Error playing audio:', error);
                });
            } else {
                console.log('Pausing audio');
                audio.pause();
            }
        });
        
        // Update button icon on play/pause
        $(audio).on('play', function() {
            playBtn.find('.play-icon').hide();
            playBtn.find('.pause-icon').show();
        });
        
        $(audio).on('pause', function() {
            playBtn.find('.play-icon').show();
            playBtn.find('.pause-icon').hide();
        });
        
        // Update progress bar
        $(audio).on('timeupdate', function() {
            var progress = (audio.currentTime / audio.duration) * 100;
            player.find('.mc-audio-progress-bar').css('width', progress + '%');
        });
        
        // Update time display
        $(audio).on('timeupdate', function() {
            updateTime();
        });
        
        // Volume button
        volumeBtn.on('click', function() {
            if (audio.muted) {
                audio.muted = false;
                volumeBtn.find('.volume-on').show();
                volumeBtn.find('.volume-off').hide();
            } else {
                audio.muted = true;
                volumeBtn.find('.volume-on').hide();
                volumeBtn.find('.volume-off').show();
            }
        });
        
        // When audio ends
        $(audio).on('ended', function() {
            playBtn.find('.play-icon').show();
            playBtn.find('.pause-icon').hide();
            player.find('.mc-audio-progress-bar').css('width', '0%');
        });
        
        // Stop audio only when quiz results are displayed
        $(document).on('mc_ld_quiz_completed', function() {
            if (!audio.paused) {
                audio.pause();
                audio.currentTime = 0;
                playBtn.find('.play-icon').show();
                playBtn.find('.pause-icon').hide();
                player.find('.mc-audio-progress-bar').css('width', '0%');
            }
        });
    });
    
    // Prevent seeking/scrubbing through audio timeline
    $('.mc-audio-element').each(function() {
        var audio = this;
        var lastTime = 0;
        var seekingAttempted = false;
        
        // Track the current playback position
        $(audio).on('timeupdate', function() {
            // If user tries to seek forward, reset to last valid position
            if (Math.abs(audio.currentTime - lastTime) > 1 && audio.currentTime > lastTime) {
                audio.currentTime = lastTime;
                
                // Show visual feedback on first attempt
                if (!seekingAttempted) {
                    seekingAttempted = true;
                    var wrapper = $(audio).closest('.mc-audio-wrapper');
                    var notice = $('<div class="mc-seek-warning" style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; margin: 10px 0; border-radius: 4px; font-size: 13px;">⚠️ Seeking forward is not allowed. Please listen to the audio from the current position.</div>');
                    wrapper.find('.mc-custom-audio-player').after(notice);
                    
                    setTimeout(function() {
                        notice.fadeOut(400, function() { $(this).remove(); });
                    }, 3000);
                }
                return;
            }
            lastTime = audio.currentTime;
        });
        
        // Prevent seeking event
        $(audio).on('seeking', function() {
            if (audio.currentTime > lastTime) {
                audio.currentTime = lastTime;
            }
        });
    });
    
    // Track audio plays
    $('.mc-audio-element').on('play', function() {
        var wrapper = $(this).closest('.mc-audio-wrapper');
        var questionId = wrapper.data('question-id');
        var listeningLimit = wrapper.data('listening-limit');
        var playCount = wrapper.data('play-count');

        // Check if already at limit (only if there is a limit)
        if (listeningLimit > 0 && playCount >= listeningLimit) {
            // Pause the audio
            this.pause();
            this.currentTime = 0;
            
            alert(mcAudioTracking.i18n.limitReached);
            return;
        }

        // Always track the play via AJAX
        $.ajax({
            url: mcAudioTracking.ajaxurl,
            type: 'POST',
            data: {
                action: 'track_audio_play',
                nonce: mcAudioTracking.nonce,
                question_id: questionId,
                listening_limit: listeningLimit
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update the play count
                    wrapper.data('play-count', response.data.play_count);
                    
                    // Only update the info message if there's a limit
                    if (!response.data.unlimited && response.data.limit > 0) {
                        var infoDiv = wrapper.find('.mc-audio-limit-info p');
                        var playCount = response.data.play_count;
                        var limit = response.data.limit;
                        var remaining = Math.max(0, response.data.remaining);
                        
                        if (remaining > 0) {
                            var timeText = remaining != 1 ? mcAudioTracking.i18n.times : mcAudioTracking.i18n.time;
                            var message = mcAudioTracking.i18n.youCanListen
                                .replace('%d', remaining)
                                .replace('%s', timeText)
                                .replace('%d', playCount)
                                .replace('%d', limit);
                            infoDiv.html('🎧 ' + message)
                                   .css({'color': '#666', 'font-weight': 'normal'});
                        } else {
                            var message = mcAudioTracking.i18n.noMorePlays
                                .replace('%d', playCount)
                                .replace('%d', limit);
                            infoDiv.html('🔒 ' + message)
                                   .css({'color': '#666', 'font-weight': 'normal'});
                            
                            // Disable the play button
                            var player = wrapper.find('.mc-custom-audio-player');
                            player.find('.mc-audio-play-btn').prop('disabled', true);
                        }
                    }
                }
            }
        });
    });
});

