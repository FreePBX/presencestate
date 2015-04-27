var PresencestateC = UCPMC.extend({
	init: function() {
		this.presenceStates = {};
		this.presenceSpecials = { startSessionStatus: null, endSessionStatus: null };
		this.menu = null;
		this.transitioning = false;
		this.calibrated = false;
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
		if (typeof this.menu.representations !== "undefined" && typeof this.menu.representations[type] !== "undefined") {
			if ($("#nav-btn-presencestate .p-msg span").text() != this.menu.representations[type].name + " " + message) {
				$("#nav-btn-presencestate .icon i").css("color", this.menu.representations[type].color);
				$("#nav-btn-presencestate .p-msg span").text(this.menu.representations[type].name + " " + message);
				$("#nav-btn-presencestate .p-msg").textfill();
				$(window).trigger("presenceStateChange");
			}
		} else {
			$("#nav-btn-presencestate .p-msg span").text(_("Status Not Set","presencestate"));
			$("#nav-btn-presencestate .p-msg").textfill();
		}
	},
	buildMenu: function(loggedIn) {
		//build and update menu system get the menu if it doesnt exist
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

			$("#presencestate-menu .statuses").html(menu.html);
			$("#presencestate-menu .presence-item").one("click", function() {
				$("#presencestate-menu .presence-item").css("cursor", "not-allowed");
				$("#presencestate-menu .statuses").css("opacity", "0.5");
				var id = $(this).data("id");
				if (id !== 0) {
					$.post( "index.php?quietmode=1&module=presencestate&command=set", { state: id }, function( data ) {
						Presencestate.menu = data.poller.menu;
						Presencestate.changeStatus(data.State, data.Message);
						Presencestate.buildMenu(false);
						$("#presencestate-menu .presence-item").css("cursor", "pointer");
						$("#presencestate-menu .statuses").css("opacity", "1");
					});
				}
			});

			if (!$("#nav-btn-presencestate").is(":visible")) {
				$("#nav-btn-presencestate").fadeIn("slow", function() {
					UCP.calibrateMenus();
				});
			}
		} else {
			//Presence is disabled for this user but we still need to have the drop down if the user has actions
			if (!$("#nav-btn-presencestate").is(":visible") && $("#presence-menu2 .options .fa").length > 0) {
				$("#nav-btn-presencestate").fadeIn("slow", function() {
					UCP.calibrateMenus();
				});
				$("#presencestate-menu .change-status").hide();
				$("#nav-btn-presencestate .icon .fa").css("color", "#7b7b7b").css("opacity", "1");
				$("#nav-btn-presencestate .p-msg").text(_("Actions List"));
			}
		}
		if(!this.calibrated) {
			UCP.calibrateMenus();
			this.calibrated = true;
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
				$("#message").text(_("Your settings have been saved"));
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
	},
	connect: function() {
		$.get( "index.php?quietmode=1&module=presencestate&command=statuses", {}, function( data ) {
			Presencestate.menu = data;
			Presencestate.buildMenu(true);
			$("#presencestate-menu .presence-item").css("cursor", "pointer");
			$("#presencestate-menu .statuses").css("opacity", "1");
		});
	},
	disconnect: function() {
		if (UCP.loggedIn) {
			$("#presencestate-menu .presence-item").off("click");
			$("#presencestate-menu .presence-item").css("cursor", "not-allowed");
			$("#presencestate-menu .statuses").css("opacity", "0.5");
			Presencestate.changeStatus("not_set", "");
		}
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
