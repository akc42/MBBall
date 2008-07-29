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

var MBBUser = new Class({
	Extends: MBBall,
	initialize: function(version,me) {
		this.parent(version,me);
// other stuff
	}
});

var MBBAdmin = new Class({
	Extends: MBBall,
	initialize: function(version,me) {
		this.parent(version,me);
		var cc = $('competitions');
		if(cc) {
			var req = new Request.HTML({
				url:'competitions.php', 
				onSuccess: function(html) {
					cc.set('text', '');
			//Inject the new DOM elements into the results div.
					cc.adopt(html);
				},
		//Our request will most likely succeed, but just in case, we'll add an
		//onFailure method which will let the user know what happened.
				onFailure: function() {
					cc.set('text', 'Failed to read competition data');
				}
			});
			req.get(this.requestOptions);
		}
		var c = $('competition');
		if(c) {
		}
// other stuff
	}
});
