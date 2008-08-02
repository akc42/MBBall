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
	initialize: function(version,me,cid) {
		this.parent(version,me);
		this.admin = $('admin');
		var adminclass = this;
		this.getCompetitions = new Request.HTML({
			url:'competitions.php', 
			onSuccess: function(html) {
				adminclass.admin.set('text', '');
				adminclass.admin.adopt(html);
		//Got the content now we have to add the events to it
				$$('input.default').each(function(rb,i) {
					rb.addEvent('change',function(e) {
						$('default_competition').send();
					});
				}); 
				$$('td.compdata').each(function (comp,i) {
					comp.addEvent('click',function(e) {
						//Make the update form hold this entry
						editComp(comp.id.substr(1).toInt());
					});
				});
				$('createform').addEvent('submit', function(e) {
					e.stop();
					var desc = $('desc');
					if(desc.value == '') {
						desc.addClass('error');
						desc.value= 'Please specify a Title for the Competition'
					} else {
						desc.removeClass('error');
						this.set('send',{onSuccess:function(html) {
							adminclass.manageComps();
						}}); 
						this.send();
					}
				});
				
			},
	//Our request will most likely succeed, but just in case, we'll add an
	//onFailure method which will let the user know what happened.
			onFailure: function() {
				adminclass.admin.set('text', 'Failed to read competition data');
			}
		});
		this.getCompetition = new Request.HTML({
			url:'competition.php',
			onSuccess: function(html) {
				adminclass.admin.set('text','');
				adminclass.admin.adopt(html);
			
//Lots more stuff
			},
	//Our request will most likely succeed, but just in case, we'll add an
	//onFailure method which will let the user know what happened.
			onFailure: function() {
				adminclass.admin.set('text', 'Failed to read competition data');
			}
		});
		if(cid==0) { 
			this.manageComps();
		} else {
			this.manageComp(cid);
		}
	},
	manageComps: function() {
		this.getCompetitions.get(this.requestOptions);
	},
	editComp: function() {
	}
});
