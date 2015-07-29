!(function(){
    var Audio = function(audio, $player, options){
        this.audio = audio;
        this.$player = $player;
        this.options = $.extend({}, Audio.DEFAULTS, options);

        this.setLabel();
        this.$player
        .on("click.qhm.audio", $.proxy(this.togglePlayer, this));

        if (this.options.bar) {
            this.createBar();
        }

        $(this.audio)
        .on("play.qhm.audio", $.proxy(this.options.playCallback, this))
        .on("pause.qhm.audio", $.proxy(this.options.pauseCallback, this))
        .on("timeupdate.qhm.audio", $.proxy(this.update, this))
        .on("ended.qhm.audio", $.proxy(this.end, this));
    };

    Audio.DEFAULTS = {
        pauseOtherPlayers: true,
        bar: true,
        barColor: '#2ecc71',
        barHeight: 2,
        playLabel: 'Play',
        pauseLabel: 'Pause',
        playCallback: function(){
            this.setLabel();

            if (this.options.pauseOtherPlayers) {
              Audio.pauseAllPlayers();
            }
            if (this.options.bar) {
                var top = 0;
                $.each(Audio.playingAudios, function(){
                    top += this.options.barHeight;
                });
                this.$bar.addClass("in").css("top", top);
            }
            Audio.playingAudios[this.toString()] = this;
        },
        pauseCallback: function(){
            this.setLabel();
            if (this.options.bar) {
                this.$bar.removeClass("in");
            }
            delete Audio.playingAudios[this.toString()];
            var top = 0;
            $.each(Audio.playingAudios, function(i){
                this.$bar.css("top", top);
                top += this.options.barHeight;
            })
        }
    };

    Audio.playingAudios = {};

    Audio.pauseAllPlayers = function(){
      $.each(Audio.playingAudios, function(){
        this.pause();
      })
    };

    Audio.prototype.setLabel = function(){
        if (this.audio.paused) {
            this.$player.html(this.options.playLabel);
        } else {
            this.$player.html(this.options.pauseLabel);
        }
    }

    Audio.prototype.togglePlayer = function(e){
        e.preventDefault();

        if (this.audio.paused) {
            this.play();
        } else {
            this.pause();
        }
    };

    Audio.prototype.play = function(callback){
        if ( ! this.audio.paused) return;
        this.audio.play();
    };

    Audio.prototype.pause = function(callback){
        if (this.audio.paused) return;
        this.audio.pause();
    };

    Audio.prototype.update = function(e){
        if (this.options.bar) {
            var value = this.audio.currentTime / this.audio.duration * 100;
            this.$bar.attr("data-value", value).width(value + "%");
        }
    };

    Audio.prototype.end = function(e){
        if (this.options.bar) {
            this.$bar.attr("data-value", 0).width(0);
        }
    }

    Audio.prototype.createBar = function(){
        var $bar = $('<div></div>', {
            class: "qhm-plugin-audio-bar fade",
            dataValue: 0,
            dataColor: this.options.barColor
        }).css("background-color", this.options.barColor);
        $("body").append($bar);
        this.$bar = $bar;
    };

    Audio.prototype.toString = function(){
        return "QHM Audio: " + this.audio.id;
    };

    $.fn.qhmaudio = function (option) {
        return this.each(function () {
            var $this   = $(this)
            var data    = $this.data('qhm.audio')
            var options = $.extend({}, Audio.DEFAULTS, $this.data(), typeof option == 'object' && option)
            var action  = typeof option == 'string' ? option : false

            var target_id = $this.attr("href") || $this.attr("data-target")
            var audio = $(target_id).get(0)

            // Target audio not found
            // Audio API not implemented
            if (typeof audio === "undefined" || typeof audio.paused === "undefined") {
                return;
            }
            if (!data) $this.data('qhm.audio', (data = new Audio(audio, $this, options)))
            else if (action) data[action]()
        })
    }

    $.fn.qhmaudio.Constructor = Audio

    $(document).on("ready", function(){

        $("[data-toggle=qhm-audio]").qhmaudio();

    });
})();
