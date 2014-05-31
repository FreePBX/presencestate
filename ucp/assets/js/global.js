var PresencestateC = UCPC.extend({
	init: function(){
		this.presenceStates = {};
		this.presenceSpecials = {startSessionStatus: null, endSessionStatus: null};
		this.menu = null;
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
		var display = (message !== '') ? niceState + ' - <span class="message">' + message + '</span>' : niceState;
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

			switch(data.presence.State) {
				case 'available':
					state = 'Available';
				break;
				case 'chat':
					state = 'Chat';
				break;
				case 'xa':
					state = 'Extended Away';
					status = '_away';
				break;
				case 'away':
					state = 'Away';
					status = '_away';
				break;
				case 'dnd':
					state = 'Do Not Disturb';
					status = '_busy';
				break;
				case 'unavailable':
					state = 'Unavailable';
					status = '_busy';
				break;
				case 'not_set':
					state = 'Not Set';
					status = '_offline';
				break;
			}
			var niceState = (data.presence.niceState !== '') ? data.presence.niceState : state;
			var display = (message !== '') ? niceState + ' - <span class="message">' + message + '</span>' : niceState;
			var stateHTML = '<div class="presence-item active" data-id="0"><img src="modules/Presencestate/assets/images/status'+status+'.png">'+niceState+'<span class="message">'+messageP+'</span></div><hr>';
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
				stateHTML = stateHTML + '<div class="presence-item" data-id="'+value.id+'"><img src="modules/Presencestate/assets/images/status'+image+'.png">' + value.nice + '<span class="message">'+message+'</span></div>';
			});
			//presence box doesnt exist so create it
			var update = false;
			if(!$('#presence').length) {
				$("<style type='text/css'> #presence {z-index:50;overflow-x: hidden;overflow-y: hidden;position: absolute;bottom: 79px;background-color: whitesmoke;width: 200px;border-top-left-radius: 5px;border-top-right-radius: 5px;padding-left: 5px;border-top: 1px solid;border-left: 1px solid;border-right: 1px solid;transition: height .5s;height: 22px;left: 0;right: 0;margin-left: auto;margin-right: auto;} .status-box.open {height: 200px;} #presence-box {cursor:pointer; display: inline-block;padding-right: 5px;height: 20px;margin-top: 1px;border: 1px solid transparent;} #presence-box:hover, #presence-box.lock {background-color: lightgray;border: 1px solid} #presence-menu {z-index:51;width: 188px;position: absolute;background-color: whitesmoke;bottom: 98px;right: 24px;border: 1px solid rgb(194, 194, 194);display:none;box-shadow: 2px 2px 6px #888888;padding-top: 3px;font-size: 85%;left:0;right:0;margin-left:auto;margin-right:auto;} #presence-menu hr {margin-top: 0px;margin-bottom: 0px;} .presence-item {cursor:pointer;border: 1px solid transparent;padding-left: 5px;} .presence-item:hover {background-color: lightgray;border: 1px solid} #presence-menu .message {display: block;font-size: 90%;padding-left: 15px;font-style: italic;} #status-message .message {font-size: 80%;font-style: italic;}</style>").appendTo("head");
				$('#container-fixed-left').append('<div id="presence"><div id="presence-box"><img id="status-image" src="modules/Presencestate/assets/images/status'+status+'.png"><i class="fa fa-caret-down"></i></div> <span id="status-message">'+display+'</span></div><div id="presence-menu">'+stateHTML+'</div>');

				$('#presence-box').click(function() {
					$('#presence-menu').toggle();
					$(this).toggleClass('lock');
				});

				$('html').click(function(event) {
					if(($(event.target).parents().index($('#presence-box')) == -1) && $(event.target).parents().index($('#presence-menu')) == -1) {
						if($('#presence-menu').is(":visible")) {
							$('#presence-menu').hide();
							$('#presence-box').removeClass('lock');
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
	$('#presence').css('bottom','3px');
	$('#presence-menu').css('bottom','22px');
});
$(window).bind('showFooter', function( event ) {
	$('#presence').css('bottom','79px');
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
