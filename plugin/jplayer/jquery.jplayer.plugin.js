$(function(){
	var isiPad = navigator.userAgent.match(/iPad/i) != null,
		playItem = 0;

	var playlistOptions = {
		cssSelector: {
			play: ".jp-play",
			pause: ".jp-pause",
			stop: ".jp-stop",
			next: ".jp-next",
			previous: ".jp-previous",
			seekBar: ".jp-seekbar-bar",
			playBar: ".jp-play-bar",
			volumeMax: ".jp-volume-max",
			volumeBar: ".jp-volume-bar",
			volumeBarValue: ".jp-volume-bar-value",
			mute: ".jp-mute",
			unmute: ".jp-unmute",
			currentTime: ".jp-current-time",
			duration: ".jp-duration",
			playlist: ".jp-playlist",
			repeat: ".jp-repeat",
			repeatOff: ".jp-repeat-off",
			shuffle: ".jp-shuffle",
			shuffleOff: ".jp-shuffle-off",
			noSolution: ".jp-no-solution" // For error feedback when jPlayer cannot find a solution.
		}
	};
	
	$("div.jquery_jplayer").each(function(){
		var $player = $(this),
			autoPlay = isiPad, showList, artWork,
			$template = $player.next(),
			templateSelector = "#" + this.id + "+div",
			playList = $(this).data("playList.jplayer"),
			isSingle = (playList.length === 1);
		
		autoPlay = /^true$/.test($player.attr("jp-auto"));
		showList = /^true$/.test($player.attr("jp-showlist"));
		verticalVolume = /^true$/.test($player.attr("jp-vertical-volume"));
		artWork = $player.attr("jp-artwork");

		if (isSingle)
		{
			showList = false;
			$template.addClass("jp-type-single");
			$template.find(".jp-next, .jp-previous, .jp-show-list").remove();
			$template.find(".jp-play-title").text(playList[0].title);
		}
		else
		{
			$template.addClass("jp-type-playlist")
		}

		if (showList)
		{
			$(".jp-show-list", $template).click(function(){
				$template.find(".jp-playlist").toggle();
			});
			$(".jp-list-close", $template).click(function(){
				$template.find(".jp-playlist").hide();
			});
		}
		else
		{
			$template.find(".jp-playlist").hide();
		}
		
		if (artWork.length > 0)
		{
			$(".jp-poster img", $template).attr({
				src: artWork,
				alt: "",
				title: ""
			});
		}
		
		//edit option
		playlistOptions.jPlayer = $player;
		playlistOptions.cssSelectorAncestor = templateSelector;
		
		new jPlayerPlaylist(
			playlistOptions,
			playList,
			{
				playlistOptions: {autoPlay: autoPlay},
				swfPath: "plugin/jplayer",
				supplied: "mp3",
				wmode:"window",
				volume: 0.5,
				verticalVolume: verticalVolume
			}
		);
		
	});

	$(".jquery_jplayer").bind($.jPlayer.event.play + ".jPlayer", function(e){
		$(e.jPlayer.options.cssSelectorAncestor+" .jp-play-title").text(e.jPlayer.status.media.title);
	})
	.bind($.jPlayer.event.canplay, function(e){
		$("+div div.jp-poster img", this).attr({
			src: e.jPlayer.status.media.poster,
			alt: e.jPlayer.status.media.title,
			title: e.jPlayer.status.media.title
		});
	});

/* 	$('#jplayer-inspector').jPlayerInspector({jPlayer:$('#jquery_jplayer_0')}); */

});