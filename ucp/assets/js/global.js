var PresencestateC = UCPMC.extend({
	init: function() {
		this.presenceStates = {};
		this.presenceSpecials = { startSessionStatus: null, endSessionStatus: null };
		this.menu = null;
	},
	poll: function(data) {
		if (data.status) {
			this.menu = data.menu;
			this.changeStatus(data.presence.State, data.presence.Message);
		}
	},
	displayWidget: function(widget_id,dashboard_id) {
		var self = this;

		$(".grid-stack-item[data-rawname='presencestate'] select[name='status']").change(function() {
			var selected = $(this).find("option:selected");
			if (selected !== null) {
				id = $(selected).data('id');
				$.post( "index.php?quietmode=1&module=presencestate&command=set", { state: id }, function( data ) {
					self.menu = data.poller.menu;
					self.changeStatus(data.State, data.Message);
				});
			}
		});
	},
	displayWidgetSettings: function(widget_id, dashboard_id) {
		var self = this;

		/* Settings changes binds */
		$("div[data-rawname='presencestate'] .widget-settings-content .pssettings select").change(function() {
			self.savePSSettings();
		});
	},
	changeStatus: function(type, message) {
		$(".grid-stack-item[data-rawname='presencestate'] select[name='status']").selectpicker('val', type + (message !== '' ? ' (' + message + ')' : ''));
	},
	savePSSettings: function() {
		var self = this;

		var data = {};
		data.events = {};

		$("div[data-rawname='presencestate'] .widget-settings-content .pssettings select").each(function( index ) {
			if ($(this).hasClass("event")) {
				data.events[$( this ).attr("name")] = $(this).val();
			} else {
				data[$( this ).attr("name")] = $(this).val();
			}
		});

		$.post( "?quietmode=1&module=presencestate&command=savesettings", data, function( data ) {
			if (data.status) {
				self.presenceSpecials.startSessionStatus = data.startsessionstatus;
				self.presenceSpecials.endSessionStatus = data.endsessionstatus;
			} else {
				return false;
			}
		});
	}
});

$(document).ready(function() {
	$(window).bind("beforeunload", function() {
		if (self.presenceSpecials.endSessionStatus !== null && navigator.onLine) {
			$.ajax({
				url: "index.php?quietmode=1&module=presencestate&command=set",
				type: "POST",
				data: { state: self.presenceSpecials.endSessionStatus.id },
				async: false, //block the browser from closing to send our request, hacky I know
				timeout: 2000
			});
		}
	});
});
