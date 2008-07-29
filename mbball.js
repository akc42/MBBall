var MBBall = new Class({
	initialize: function(version,me) {
		this.me = me;
		this.requestOptions = {'uid':me.uid,'pass':me.password};
		var span=$('version');
		span.set('text',version);
// Need to update all the  dates
		var datespans = $$('span.date');
		datespans.each(function (datespan,i) {
			var d = new Date(datespan.get('text').toInt()*1000);
			datespan.set('text',d.toLocaleString());
		});
	},
	logout: function () {
		var logoutRequest = new Request ({url: 'logout.php',autoCancel:true}).get($merge(myRequestOptions,
				{'mbball':version},MooTools,
				{'browser':Browser.Engine.name+Browser.Engine.version,'platform':Browser.Platform.name}));
	}
});

MBBUser = new Class({
	extends: MBBall,
	initialize: function(version,me) {
		this.parent(version,me);
// other stuff
	}
}

MBBAdmin = new Class({
	extends: MBBall,
	initialize: function(version,me) {
		this.parent(version,me);
// other stuff
	}
}
