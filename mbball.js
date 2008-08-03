var MBBall = new Class({
	initialize: function(version,me,el) {
		this.me = me;
		this.requestOptions = {'uid':me.uid,'pass':me.password};
		var span=$('version');
		span.set('text',version);
		//sets up all date time fields under the supplied element
		var datespans = el.getElements('.time');
		datespans.each(function(datespan,i) {
			var d;
			el.removeClass('time');
			switch (datespan.get('tag')) {
			case "span":
				d = datespan.get('text');
				if(d == '' || d=='0') return;
				el.set('text',new Date(d.toInt()*1000).toLocalString().substr(0,24));
				break;
			case "input":
				d=datespan.value;
				if(d == '' || d=='0') return;
				el.value = new Date(d.toInt()*1000).toLocalString().substr(0,24);
				break;
			default:
				break;
			}
		});
	}	
});


var MBBUser = new Class({
	Extends: MBBall,
	initialize: function(version,me) {
		this.parent(version,me,$('content'));
// other stuff
	}
});


var MBBAdmin = new Class({
	Extends: MBBall,
	initialize: function(version,me,cid) {
		this.parent(version,me,$('admin'));
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
			this.editComp(cid);
		}
	},
	manageComps: function() {
		this.getCompetitions.get(this.requestOptions);
	},
	editComp: function(cid) {
		this.getCompetition.get($merge(this.requestOptions,{'cid':cid}));
	}
});
