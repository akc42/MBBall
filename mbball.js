var MBBRequestOptions;
/*  Class to load a SubPage from 'url' into the 'div'. 
 *  On success call
 *	initializePage(div).  
 *  If the load fails, message is the text to fill the div with
*/

var MBBSubPage = new Class({
	initialize:function(owner,url,div,initializeSubPage,message) {
		this.owner = owner;
		this.div = div;
		var iSP = initializeSubPage.bind(this);
		this.request = new Request.HTML({
			url: url,
			onSuccess: function(html) {
				div.set('text','');
				div.adopt(html);
				iSP(div);
			},
			onFailure: function(){
				var output = message || '<p>Failed to Read Page from '+url+'<p>';
				div.set('text',output);
			}
		});
	},
	loadPage: function(params) {
		this.request.get($merge(MBBRequestOptions,params || {}));
	}
});

var MBBall = new Class({
	initialize: function(version,me) {
		this.me = me;
		MBBRequestOptions = {'uid':me.uid,'pass':me.password};
		var span=$('version');
		span.set('text',version);
	},
	adjustDates: function (el) {
		//sets up all date time fields under the supplied element
		var datespans = el.getElements('.time');
		datespans.each(function(datespan,i) {
			var d;
			el.removeClass('time');
			el.addClass('datetime');
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
		this.parent(version,me);
// other stuff
	}
});


var MBBAdmin = new Class({
	Extends: MBBall,
	initialize: function(version,me,cid) {
		var params = {};
		this.parent(version,me);
		this.competitions = new MBBSubPage(
			this,
			'competitions.php',
			$('competitions'),
			function (div) {
				var owner = this.owner;
				$$('input.default').each(function(rb,i) {
					rb.addEvent('change',function(e) {
						$('default_competition').send();
					});
				}); 
				$$('td.compdata').each(function (comp,i) {
					comp.addEvent('click',function(e) {
						//Make the update form hold this entry
						owner.competition.loadPage(comp.id.substr(1).toInt());
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
							owner.competitions.loadPage();
						}}); 
						this.send();
					}
				});
			}
		);
		this.competition = new MBBSubPage (
			this,
			'competition.php',
			$('competition'),
			function (div) {
				// Validate competition Details and Change Dates to seconds since 1970
				$('compform').addEvent('submit', function(e) {
						e.stop();
				});
//Lots more stuff

				this.rounds = new MBBSubPage(this,'rounds.php',$('rounds'), function(div) {
					var rid;
					if (div.getElement('div')) {
						rid = div.getElement('div').get('id').substr(1).toInt();
					} else {
						rid=0;
					}
					var maxround = rid;
					//Initialise to click on a round to load single round
					this.round = new MBBSubPage(this,'round.php',$('round'),function(div) {
						var answer =$('answer').value.toInt();
						//Initialise Round Data Form
						this.matches = new MBBSubPage(this,'matches.php',$('matches'),function (div) {
						});
						this.options = new MBBSubPage(this,'options.php',$('options'),function(div) {
						})
						this.teams = new MBBSubPage(this,'teams.php',$('teams'),function (div) {
							$('addall').addEvent('click',function() {
							});
						});
						this.matches.loadPage($merge(params,{'rid':rid}));
						this.options.loadPage($merge(params,{'rid':rid,'answer':answer}));
						this.teams.loadPage($merge(params,{'rid':rid}));
					});
					this.newround = new MBBSubPage(this,'newround.php',$('newround'),function(div) {

					});
					this.round.loadPage($merge(params,{'rid':rid}));
					this.newround.loadPage($merge(params,{'rid':maxround+1}));
				});
				this.rounds.loadPage(params);	
			}
		);
		if (this.me.admin) {
			$extend(params,{'global':true});
		}
		this.competitions.loadPage(params);
		if(cid!=0) {
			$extend(params,{'cid':cid});
			this.competition.loadPage(params);

		}
	}
});
