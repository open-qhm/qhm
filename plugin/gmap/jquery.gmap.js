!function ($) {
	"use strict"; // jshint ;_;
	
	/* Gmap CLASS DEFINITION
	* ========================= */

	var Gmap = function (element, options) {

		this.$element = $(element);
		this.options = options;
		this.marker = [];
		this.currentInfoWindow = null;

		var self = this
			,$selector = $(this.options.selector)
			,$mapdata = $("[data-mapping]", this.options.markerSelector);
		var height = this.options.mapHeight
			,width = this.options.mapWidth;

		
		if ($mapdata.length == 0) {
			this.options.mapOptions.center = new google.maps.LatLng(this.options.posLat, this.options.posLng);
		}
		else {
			var $mapping = $mapdata.eq(0);

			this.options.mapOptions.center = new google.maps.LatLng($mapping.data("lat"), $mapping.data("lng"));
			new google.maps.LatLng($mapping.data("lat"), $mapping.data("lng"))
			
			// height width の指定
			height = ($selector.data("map-height") != 'undefined' && $selector.data("map-height") != '') ? $selector.data("map-height") : height;
			width = ($selector.data("map-width") != 'undefined' && $selector.data("map-width") != '') ? $selector.data("map-width") : width;
			
			// zoom
			this.options.mapOptions.zoom = ($selector.data("map-zoom") != 'undefined' && $selector.data("map-zoom") != '') ? $selector.data("map-zoom") : this.options.mapOptions.zoom;

		}

		$selector.height(height).width(width);
		this.map = new google.maps.Map($selector[0], this.options.mapOptions);

		if ($mapdata.length > 0) {
			// マーカーの追加
			this.addMarker($mapdata);
		
			// リストの表示、非表示
			var $list = $(this.options.markerSelector)
				, ul_type = $list.data('list-type');
	
			if (ul_type == 'hide') {
				$list.hide();
			}
			else if (ul_type == 'before') {
				$list.insertBefore($selector);
			}
		}
		
	}

	Gmap.prototype = {
		load: function(e) {
			google.maps.event.addDomListener(window, "load", this);
		}
		, addMarker: function(element) {
			for (var i =0; i < element.length; i++)
			{
				var $mapinfo = element.eq(i);

				var pos = new google.maps.LatLng($mapinfo.data("lat"), $mapinfo.data("lng"));
				var marker = new google.maps.Marker({
						 position: pos
						,map:this.map
						,animation: google.maps.Animation.DROP
					});


 				setLinkClickEvent($mapinfo,marker);
				
				this.attachMessage(marker, $mapinfo.children(".info-box").html());
				this.marker.push(marker);
			}

			// 範囲を移動
			this.map.setCenter(pos);

		}
		, attachMessage: function(marker, msg){
			// 情報ウィンドウの表示
			var infodiv = '<div class="gmap-info-div">'+msg+'</div>';
			var infoWnd = new google.maps.InfoWindow({
//				new google.maps.InfoBox({
					content: infodiv
				});
		
			google.maps.event.addListener(marker, 'click', function(event) {
				if (this.currentInfoWindow) {
					this.currentInfoWindow.close();
				}
				
				infoWnd.open(marker.getMap(), marker);
			
				this.currentInfoWindow = infoWnd;
			});
		}
		, destroy: function(){
			this.$element.removeData('gmap').empty();
//			delete this;
		}
		, clearMarker: function(){
			for (var i in this.marker)
			{
				this.marker[i].setMap(null);
			}
		}
		, getZoom: function(){
			var zoom =  this.map.getZoom();
			return zoom;
		}
	}

	/**
	 * リンクのクリックイベントの登録
	 */
	var setLinkClickEvent = function(lnk, marker){
		
		lnk.bind('click', function(){
			google.maps.event.trigger(marker, 'click');
			// 地図の上部にスクロールする
			ORGM.scroll($(".orgm-gmap"));
		});
	}



	/* GMAP PLUGIN DEFINITION
	* ========================== */
	
	$.fn.gmap = function (option, element) {

		if (typeof option == 'string') {
			var data = this.data('gmap')
			return data[option](element);
		}

		return this.each(function () {
			var $this = $(this)
				,data = $this.data('gmap')
				,options = $.extend({}, $.fn.gmap.defaults, typeof option == 'object' && option, $this.data());

			if ( ! data) {
				$this.data('gmap', (data = new Gmap(this, options)))
				return;
			}
			
			data.load();
		});
	}

	$.fn.gmap.defaults = {
		 selector: "#map_canvas"
		,markerSelector: ".gmap-markers"
		,posLat: 34.77114
		,posLng: 135.505969
		,mapOptions: {
			 zoom: 13
			,mapTypeId: google.maps.MapTypeId.ROADMAP
			,center:{}
		}
		,mapWidth: "100%"
		,mapHeight: 300
	}
	$.fn.gmap.Constructor = Gmap;
	
}(window.jQuery);


function getLatLng(address) {
	
	// ジオコーダのコンストラクタ
	var geocoder = new google.maps.Geocoder();
		var data = {};
	
	// geocodeリクエストを実行。
	// 第１引数はGeocoderRequest。住所⇒緯度経度座標の変換時はaddressプロパティを入れればOK。
	// 第２引数はコールバック関数。
	geocoder.geocode({address: address}, function(results, status){
		if (status == google.maps.GeocoderStatus.OK) {

			// 結果の表示範囲。結果が１つとは限らないので、LatLngBoundsで用意。
//				data.bounds = new google.maps.LatLngBounds();

			if (results[0].geometry) {
				// 緯度経度を取得
				data.lat = results[0].geometry.location.lat();
				data.lng = results[0].geometry.location.lng();
					
				// 住所を取得(日本の場合だけ「日本, 」を削除)
				data.address = results[0].formatted_address.replace(/^日本, /, '');

					// 検索結果地が含まれるように範囲を拡大
//						data.bounds.extend(data.latlng);

			}
		}
		else {
			switch(status) {
				case google.maps.GeocoderStatus.ERROR :
					data.error = "サーバとの通信時に何らかのエラーが発生しました";
					break;
				case google.maps.GeocoderStatus.INVALID_REQUEST:
					data.error = "リクエストに問題があります";
					break;
				case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
					data.error = "短時間に変更しすぎです";
					break;
				case google.maps.GeocoderStatus.REQUEST_DENIED:
					data.error = "このページではジオコーダの利用が許可されていません";
					break;
				case google.maps.GeocoderStatus.UNKNOWN_ERROR:
					data.error = "サーバ側でなんらかのトラブルが発生しました";
					break;
				case google.maps.GeocoderStatus.ZERO_RESULTS:
					data.error = "見つかりません";
					break;
				default :
					data.error = "エラーが発生しました";
			}
		}
		return data;
	});
}

