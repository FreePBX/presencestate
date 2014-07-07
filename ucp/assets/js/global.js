var PresencestateC = UCPMC.extend({
	init: function(){
		this.presenceStates = {};
		this.presenceSpecials = {startSessionStatus: null, endSessionStatus: null};
		this.menu = null;
		this.transitioning = false;
	},
	poll: function(data){
		if(data.status) {
			var stateHTML = '';
			this.menu = data.menu;
			this.changeStatus(data.presence.State,data.presence.Message);
		}
	},
	display: function(event) {
		$('.pssettings select').change(function() {
			Presencestate.savePSSettings();
		});
	},
	hide: function(event) {

	},
	changeStatus: function(type,message) {
		var output = {text: 'Not Set', image: '_offline'};
		switch(type) {
			case 'available':
				output.text = 'Available';
				output.image = '';
			break;
			case 'chat':
				output.text = 'Chat';
				output.image = '';
			break;
			case 'xa':
				output.text = 'Extended Away';
				output.image = '_away';
			break;
			case 'away':
				output.text = 'Away';
				output.image = '_away';
			break;
			case 'dnd':
				output.text = 'Do Not Disturb';
				output.image = '_busy';
			break;
			case 'unavailable':
				output.text = 'Unavailable';
				output.image = '_busy';
			break;
			case 'not_set':
				output.text = 'Not Set';
				output.image = '_offline';
			break;
		}
		$('#status-image').attr('src','modules/Presencestate/assets/images/status'+output.image+'.png');
		var niceState = (this.presenceStates[type] !== '') ? this.presenceStates[type] : output.text;
		var display = (message !== '') ? niceState + ' - <div class="message">' + message + '</div>' : niceState;
		$('#status-message').html(display);
		this.buildMenu(false);
	},
	buildMenu: function(loggedin) {
		//build and update menu system
		//get the menu if it doesnt exist
		data = this.menu;
		if(data !== null && data.status) {
			Presencestate.presenceSpecials.startSessionStatus = data.startsessionstatus;
			Presencestate.presenceSpecials.endSessionStatus = data.endsessionstatus;
			if(loggedin && Presencestate.presenceSpecials.startSessionStatus !== null) {
				$.post( "index.php?quietmode=1&module=presencestate&command=set", {state: Presencestate.presenceSpecials.startSessionStatus.id}, function( data ) {
					Presencestate.changeStatus(data.State,data.Message);
				});
			}
			var status = '';
			var state = 'Not Set';
			var subtype = '';
			var message = (data.presence.Message !== null) && (data.presence.Message !== '') ? data.presence.Message : '';
			var messageP = (message !== '') ? '('+message+')' : '';
			var color = '#d1d1d1';
			switch(data.presence.State) {
				case 'available':
					state = 'Available';
					color = 'green';
				break;
				case 'chat':
					state = 'Chat';
					color = 'green';
				break;
				case 'xa':
					state = 'Extended Away';
					status = '_away';
					color = 'yellow';
				break;
				case 'away':
					state = 'Away';
					status = '_away';
					color = 'yellow';
				break;
				case 'dnd':
					state = 'Do Not Disturb';
					status = '_busy';
					color = 'red';
				break;
				case 'unavailable':
					state = 'Unavailable';
					status = '_busy';
					color = 'red';
				break;
				case 'not_set':
					state = 'Not Set';
					status = '_offline';
				break;
			}
			var niceState = (data.presence.niceState !== '') ? data.presence.niceState : state;
			var display = (message !== '') ? niceState + ' - <div class="message">' + message + '</div>' : niceState;
			var stateHTML = '<div class="presence-item active" data-id="0"><img src="modules/Presencestate/assets/images/status'+status+'.png">'+niceState+'<div class="message">'+messageP+'</div></div>';
			if($('#presence-box2 .p-msg').width() > 186) {
				var maxn = 186*2;
				$.keyframe.define([{
					name: 'marquee',
						'0%': {'text-indent': (maxn-$('#presence-box2 .p-msg').width())+'px'},
						'100%': {'text-indent': ($('#presence-box2 .p-msg').width()-maxn)+'px'}
				}]);
				$('.p-msg').resetKeyframe();
				$('.p-msg').playKeyframe('marquee 10000 linear 0 infinite alternate forwards');
			}
			$('#presence-box2 .p-btn i').css('color',color);
			$('#presence-box2 .p-msg').html(niceState + ' <span>'+messageP+'</span>');
			if(!$('#presence-box2').is(':visible')) {
				$('#presence-box2').fadeIn('slow');
			}
			$.each(data.states, function(index, value) {
				Presencestate.presenceStates[value.type] = value.nice;
				if(data.presence.State == value.type && (value.message === null || data.presence.Message == value.message)) {
					return true;
				}
				var message = (value.message !== null) && (value.message !== '') ? '('+value.message+')' : '';
				var image = '';
				switch(value.type) {
					case 'available':
					case 'chat':
						image = '';
					break;
					case 'xa':
					case 'away':
						image = '_away';
					break;
					case 'dnd':
					case 'unavailable':
						image = '_busy';
					break;
					case 'not_set':
						image = '_offline';
					break;
				}
				stateHTML = stateHTML + '<div class="presence-item" data-id="'+value.id+'"><img src="modules/Presencestate/assets/images/status'+image+'.png">' + value.nice + '<div class="message">'+message+'</div></div>';
			});
			//presence box doesnt exist so create it
			var update = false;
			if(!$('#presence').length) {
				$('#container-fixed-left').append('<div id="presence"><div id="presence-row"><div id="presence-box"><img id="status-image" src="modules/Presencestate/assets/images/status'+status+'.png"><i class="fa fa-caret-down"></i> <div id="status-message">'+display+'</div></div></div><hr></div><div id="presence-menu">'+stateHTML+'</div>');

				$("#presence").bind("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function(){ Presencestate.transitioning = false; });
				$("#presence").bind("animationstart webkitAnimationStart oAnimationStart MSAnimationStart", function(){ Presencestate.transitioning = true; });

				$('#presence-row').click(function() {
					$('#presence-menu').toggle();
					$('#presence-box').toggleClass('lock');
				});

				$('#presence').hover(function() {
					if(!Presencestate.transitioning) {
						$('#presence').addClass('expand');
						$('#presence').removeClass('shrink');
					}
				}, function() {
					if(!$('#presence-menu').is(":visible") && !Presencestate.transitioning) {
						$('#presence').addClass('shrink');
						$('#presence').removeClass('expand');
					}
				});

				$('html').click(function(event) {
					if(($(event.target).parents().index($('#presence-row')) == -1) && $(event.target).parents().index($('#presence-menu')) == -1 && $(event.target).prop('id') != 'presence-row') {
						if($('#presence-menu').is(":visible")) {
							$('#presence-menu').hide();
							$('#presence-box').removeClass('lock');
						}
					}

					if(($(event.target).parents().index($('#presence')) == -1) && ($(event.target).parents().index($('#presence-box')) == -1) && $(event.target).parents().index($('#presence-menu')) == -1 && $(event.target).prop('id') != 'presence') {
						if($('#presence').hasClass('expand') && !Presencestate.transitioning) {
							$('#presence').removeClass('expand');
							$('#presence').addClass('shrink');
						}
					}
				});
				update = true;
			} else {
				if(stateHTML !== $('#presence-menu').html()) {
					$('#presence-menu').html(stateHTML);
					update = true;
				}
			}

			//redefine clicks because data has changed
			if(update) {
				$('.presence-item').click(function() {
					$('#presence-menu').toggle();
					if($('#presence-menu').is(':visible')) {
						$('#presence-box').addClass('lock');
					} else {
						$('#presence-box').removeClass('lock');
					}
					var id = $(this).data('id');
					if(id !== 0) {
						$.post( "index.php?quietmode=1&module=presencestate&command=set", {state: id}, function( data ) {
							Presencestate.menu = data.poller.menu;
							Presencestate.changeStatus(data.State,data.Message);
						});
					}
				});
			}
		}
	},
	savePSSettings: function() {
		$('#message').fadeOut("slow");
		var data = {};
		$('.pssettings input[type="text"]').each(function( index ) {
			data[$( this ).attr('name')] = $( this ).val();
		});
		$('.pssettings input[type="checkbox"]').each(function( index ) {
			data[$( this ).attr('name')] = $( this ).is(':checked');
		});
		data.events = {};
		$('.pssettings select').each(function( index ) {
			if($(this).hasClass('event')) {
				data.events[$( this ).attr('name')] = $(this).val();
			} else {
				data[$( this ).attr('name')] = $(this).val();
			}
		});
		$.post( "?quietmode=1&module=presencestate&command=savesettings", data, function( data ) {
			if(data.status) {
				$('#message').addClass('alert-success');
				$('#message').text('Saved!');
				$('#message').fadeIn( "slow", function() {
					setTimeout(function() { $('#message').fadeOut("slow"); }, 2000);
				});
				Presencestate.presenceSpecials.startSessionStatus = data.startsessionstatus;
				Presencestate.presenceSpecials.endSessionStatus = data.endsessionstatus;
			} else {
				$('#message').addClass('alert-error');
				$('#message').text(data.message);
				return false;
			}
		});
	}
});
var Presencestate = new PresencestateC();
$(document).ready(function() {
	$(window).bind("beforeunload", function() {
		if(Presencestate.presenceSpecials.endSessionStatus !== null && navigator.onLine) {
			$.ajax({
				url: 'index.php?quietmode=1&module=presencestate&command=set',
				type: 'POST',
				data: {state: Presencestate.presenceSpecials.endSessionStatus.id},
				async: false, //block the browser from closing to send our request, hacky I know
				timeout: 2000
			});
		}
	});
});

//Logged In
$(document).bind('logIn', function( event ) {
	//set the offline image into memory but hide it as well so we don't see it, just a preload trick
	$('body').append('<img src="modules/Presencestate/assets/images/status_offline.png" style="display:none;">');
	if(this.menu === null) {
		$.get( "index.php?quietmode=1&module=presencestate&command=statuses", {}, function( data ) {
			UCP.menu = data;
			Presencestate.buildMenu(true);
		});
	}
});

//Build the menu when we detect we are online and execute status change
$(window).bind('online', function( event ) {
	if(UCP.loggedIn) {
		Presencestate.buildMenu(true);
	}
});

$(window).bind('hideFooter', function( event ) {
	$('#presence').addClass('move');
	$('#presence-menu').css('bottom','22px');
});
$(window).bind('showFooter', function( event ) {
	$('#presence').removeClass('move');
	$('#presence-menu').css('bottom','98px');
});

//Go into offline mode, basically when no internet is detected
$(window).bind('offline', function( event ) {
	if(UCP.loggedIn) {
		$('#status-image').attr('src','modules/Presencestate/assets/images/status_offline.png');
		var display = 'Offline';
		$('#status-message').html(display);
		$( '.presence-item' ).off("click", "**");
	}
});
