var Presencestate = new function() {
	this.init = function() {

	};
	//Save Presence State Settings
	this.savePSSettings = function() {
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
				presenceSpecials.endsessionstatus = data.endsessionstatus
				presenceSpecials.startsessionstatus = data.startsessionstatus
			} else {
				$('#message').addClass('alert-error');
				$('#message').text(data.message);
				return false;
			}
		});
	};
};

//MUST REMAIN AT BOTTOM!
//This might not be needed as most browser seem to run doc ready anyways
//TODO: This should be in the higher up. each module should have this functionality from here on out!
$(function() {
	Presencestate.init();
});
