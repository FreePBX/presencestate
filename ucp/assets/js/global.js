var PresencestateC = UCPMC.extend({
	init: function() {
		this.presenceStates = {};
		this.presenceSpecials = { startSessionStatus: null, endSessionStatus: null };
		this.menu = null;
		this.transitioning = false;
	},
	poll: function(data) {
		if (data.status) {
			var stateHTML = "";
			this.menu = data.menu;
			this.changeStatus(data.presence.State, data.presence.Message);
			this.buildMenu(false);
		}
	},
	display: function(event) {
		$(".pssettings select").change(function() {
			Presencestate.savePSSettings();
		});
	},
	hide: function(event) {

	},
	changeStatus: function(type, message) {
		message = (message !== "") ? "(" + message + ")" : "";
		if (typeof this.menu.representations[type] !== undefined) {
			$("#presence-box2 .p-btn i").css("color", this.menu.representations[type].color);
			$("#presence-box2 .p-msg").html(this.menu.representations[type].name + " <span>" + message + "</span>");
		}
	},
	buildMenu: function(loggedIn) {
		//build and update menu system
		//get the menu if it doesnt exist
		var menu = Presencestate.menu;
		if (menu !== null && menu.status) {
			Presencestate.presenceSpecials.startSessionStatus = menu.startsessionstatus;
			Presencestate.presenceSpecials.endSessionStatus = menu.endsessionstatus;
			if (loggedIn && Presencestate.presenceSpecials.startSessionStatus !== null) {
				$.post( "index.php?quietmode=1&module=presencestate&command=set", { state: Presencestate.presenceSpecials.startSessionStatus.id }, function( data ) {
					Presencestate.changeStatus(data.State, data.Message);
					Presencestate.buildMenu(false);
				});
			}

			$("#presence-menu2 .statuses").html(menu.html);
			$(".presence-item2").on("click", function() {
				$("#presence-menu2 .statuses").css("opacity", "0.5");
				$(".presence-item2").off("click");
				var id = $(this).data("id");
				if (id !== 0) {
					$.post( "index.php?quietmode=1&module=presencestate&command=set", { state: id }, function( data ) {
						Presencestate.menu = data.poller.menu;
						Presencestate.changeStatus(data.State, data.Message);
						Presencestate.buildMenu(false);
						$("#presence-menu2 .statuses").css("opacity", "1");
					});
				}
			});

			if (!$("#presence-box2").is(":visible")) {
				$("#presence-box2").fadeIn("slow");
			}
		}

	},
	savePSSettings: function() {
		$("#message").fadeOut("slow");
		var data = {};
		$(".pssettings input[type=\"text\"]").each(function( index ) {
			data[$( this ).attr("name")] = $( this ).val();
		});
		$(".pssettings input[type=\"checkbox\"]").each(function( index ) {
			data[$( this ).attr("name")] = $( this ).is(":checked");
		});
		data.events = {};
		$(".pssettings select").each(function( index ) {
			if ($(this).hasClass("event")) {
				data.events[$( this ).attr("name")] = $(this).val();
			} else {
				data[$( this ).attr("name")] = $(this).val();
			}
		});
		$.post( "?quietmode=1&module=presencestate&command=savesettings", data, function( data ) {
			if (data.status) {
				$("#message").addClass("alert-success");
				$("#message").text("Saved!");
				$("#message").fadeIn( "slow", function() {
					setTimeout(function() { $("#message").fadeOut("slow"); }, 2000);
				});
				Presencestate.presenceSpecials.startSessionStatus = data.startsessionstatus;
				Presencestate.presenceSpecials.endSessionStatus = data.endsessionstatus;
			} else {
				$("#message").addClass("alert-error");
				$("#message").text(data.message);
				return false;
			}
		});
	}
}),
Presencestate = new PresencestateC();

$(document).ready(function() {
	$(window).bind("beforeunload", function() {
		if (Presencestate.presenceSpecials.endSessionStatus !== null && navigator.onLine) {
			$.ajax({
				url: "index.php?quietmode=1&module=presencestate&command=set",
				type: "POST",
				data: { state: Presencestate.presenceSpecials.endSessionStatus.id },
				async: false, //block the browser from closing to send our request, hacky I know
				timeout: 2000
			});
		}
	});
});

//Logged In
$(document).bind("logIn", function( event ) {
	$.get( "index.php?quietmode=1&module=presencestate&command=statuses", {}, function( data ) {
		Presencestate.menu = data;
		Presencestate.buildMenu(true);
	});
});

//Build the menu when we detect we are online and execute status change
$(window).bind("online", function( event ) {
	if (UCP.loggedIn) {
		Presencestate.buildMenu(true);
	}
});

//Go into offline mode, basically when no internet is detected
$(window).bind("offline", function( event ) {
	if (UCP.loggedIn) {
		$( ".presence-item" ).off("click", "**");
		UCP.changeStatus("not_set", "");
	}
});
