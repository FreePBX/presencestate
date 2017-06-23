var PresencestateC = UCPMC.extend({
	init: function() {
		this.presenceStates = {};
		this.presenceSpecials = { startSessionStatus: null, endSessionStatus: null };
		this.menu = null;
	},
	poll: function(data) {
		if (data.status) {
			this.menu = data.menu;
			this.statusUpdate(data.presence.State, data.presence.Message);
		}
	},
	displayWidget: function(widget_id,dashboard_id) {
		var self = this;

		$(".grid-stack-item[data-id='"+widget_id+"'][data-rawname='presencestate'] select[name='status']").change(function() {
			var selected = $(this).find("option:selected");
			if (selected !== null) {
				id = $(selected).data('id');

				self.saveState(id);
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
	displaySimpleWidget: function(widget_id) {
		var self = this;
		$(".widget-extra-menu[data-id='"+widget_id+"'] select[name='status']").change(function() {
			var selected = $(this).find("option:selected");
			if (selected !== null) {
				id = $(selected).data('id');

				self.saveState(id);
			}
		});
	},
	displaySimpleWidgetSettings: function(widget_id) {
		this.displayWidgetSettings(widget_id);
	},
	statusUpdate: function(type, message) {
		$(".grid-stack-item[data-rawname='presencestate'] select[name='status']").selectpicker('val', type + (message !== '' ? ' (' + message + ')' : ''));
		$(".widget-extra-menu[data-module='presencestate'] select[name='status']").selectpicker('val', type + (message !== '' ? ' (' + message + ')' : ''));
	},
	saveState: function(id) {
		var self = this;

		data = { state: id };
		data.module = "presencestate";
		data.command = "set";

		$.post(UCP.ajaxUrl, data, null).always(function(data) {
			self.menu = data.poller.menu;
			self.statusUpdate(data.State, data.Message);
		});
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

		data.module = "presencestate";
		data.command = "savesettings";

		$.post(UCP.ajaxUrl, data, null).always(function(data) {
			if (data.status) {
				self.presenceSpecials.startSessionStatus = (data.startsessionstatus !== null) ? data.startsessionstatus.id : null;
				self.presenceSpecials.endSessionStatus = (data.endsessionstatus !== null) ? data.endsessionstatus.id : null;
			} else {
				return false;
			}
		});
	}
});

$(document).ready(function() {
	$(window).bind("beforeunload", function() {
		if (UCP.Modules.Presencestate.presenceSpecials.endSessionStatus !== null && navigator.onLine) {
			$.ajax({
				url: UCP.ajaxUrl + "?module=presencestate&command=set",
				type: "POST",
				data: { state: UCP.Modules.Presencestate.presenceSpecials.endSessionStatus },
				async: false, //block the browser from closing to send our request, hacky I know
				timeout: 2000
			});
		}
	});
});

$(document).on("logIn", function() {
	UCP.Modules.Presencestate.presenceSpecials.startSessionStatus = UCP.Modules.Presencestate.staticsettings.startSessionStatus;
	UCP.Modules.Presencestate.presenceSpecials.endSessionStatus = UCP.Modules.Presencestate.staticsettings.endSessionStatus;
	if (UCP.Modules.Presencestate.presenceSpecials.startSessionStatus !== null && navigator.onLine) {
		$.ajax({
			url: UCP.ajaxUrl + "?module=presencestate&command=set",
			type: "POST",
			data: { state: UCP.Modules.Presencestate.presenceSpecials.startSessionStatus }
		});
	}
});
