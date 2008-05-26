MBball = function () {
	var version = 'v0.1';
	var me;
	var myRequestOptions;
return {
	init : function(user) {
		var span = $('version');
		span.set('text', version);		
// Save key data about me
		me =  user; 
		myRequestOptions = {'user': me.uid,'password': me.password};  //Used on every request to validate
	},
	logout: function () {
		var logoutRequest = new Request ({url: 'logout.php',autoCancel:true}).get($merge(myRequestOptions,
				{'mbball':version},MooTools,
				{'browser':Browser.Engine.name+Browser.Engine.version,'platform':Browser.Platform.name}));
	}
  }; 


}();
