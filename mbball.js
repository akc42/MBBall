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
				div.empty();
				div.adopt(html);
				iSP(div);
			},
			onFailure: function(){
				var output = message || '<p>Failed to Read Page from '+url+'<p>';
				div.empty();
				div.adopt(output);
			}
		});
	},
	loadPage: function(params) {
		this.request.get($merge(MBBRequestOptions,params || {}));
	}
});

var MBBReq = new Class({
	initialize: function (url,errorEl,success) {
		this.req = new Request.JSON({
			url:url,
			onComplete: function(response,html) {
				if(response) {
					success(response);
				} else {
					errorEl.set('text',html);
				}
			}
		});
	},
	get:function(params) {
		this.req.get($merge(MBBRequestOptions,params));
	},
	post: function (params) {
		this.req.post(params);
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
			datespan.removeClass('time');
			datespan.addClass('datetime');
			switch (datespan.get('tag')) {
			case "span":
				d = datespan.get('text');
				if(d == '' || d=='0') {
					datespan.set('text','');
					break;
				}
				datespan.set('text',new Date(d.toInt()*1000).toLocaleString().substr(0,24));
				break;
			case "input":
				d=datespan.value;
				if(d == '' || d=='0') {
					datespan.value = '';
					break;
				}
				datespan.value = new Date(d.toInt()*1000).toLocaleString().substr(0,24);
				break;
			default:
				break;
			}
		});
	},
	parseDate: function(el) {
		var secs
		el.removeClass('error');
		if (el.value == '' || el.value == '0') {
			secs = 0;
		} else {
			secs = Date.parse(el.value)/1000;
			if(isNaN(secs)) {
				el.addClass('error');
				return false;
			}
		}
		el.value = secs;
		el.removeClass('datetime');
		el.addClass('time');
		return true;
	},
	intValidate: function(el) {
		el.removeClass('error');
		if (isNaN(el.value.toInt())) {
			el.addClass('error');
			return false;
		}
		return true;
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
		var params = {'cid':cid, 'rid':0};
		this.parent(version,me);
		this.competitions = new MBBSubPage(
			this,
			'competitions.php',
			$('competitions'),
			function (div) {
				var owner = this.owner;
				$$('input.default').each(function(rb,i) {
					if(params.cid == 0 && rb.checked) params.cid = rb.value; 
					rb.addEvent('change',function(e) {
						var dcReq = new MBBReq('setdefault.php',$('compserr'), function (response) {
							if(params.cid ==0) {
								params.cid = response.cid;
								owner.competition.loadPage(params);
							}
						});	
						if(params.cid == 0 && rb.checked) params.cid = rb.value; 
						dcReq.post($('default_competition'));
					});
				}); 
				div.getElements('.selectthis').each(function (comp,i) {
					comp.addEvent('click',function(e) {
						e.stop();
						//Make the update form hold this entry
						params.cid = comp.id.substr(1).toInt()
						params.rid = 0;
						owner.competition.loadPage(params);
					});
				});
				div.getElements('.del').each(function (comp,i) {
					comp.addEvent('click', function(e) {
						e.stop();
						if(confirm('Deleting a Competition will delete all the Rounds and Matches associated with it. Do you wish to Proceed?')) {
							var deleteReq = new MBBReq('deletecomp.php',$('compserr'),function (response) {
								if (params.cid == response.cid) {
									params.cid = 0;
									params.rid = 0;
									owner.competition.loadPage(params);
								}
								owner.competitions.loadPage(params);
							});
							deleteReq.get({'cid':comp.id.substr(1).toInt()});
						}
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
						var createReq = new MBBReq('createcomp.php',$('compserr'), function(response) {
							if (params.cid == 0) {
								params.cid = response.cid;
								owner.competition.loadPage(params);
							}
							owner.competitions.loadPage(params);
						});
						createReq.post($('createform'));
					}
				});
			}
		);
		this.competition = new MBBSubPage (
			this,
			'competition.php',
			$('competition'),
			function (div) {
				var owner = this.owner;
				if(params.cid != 0) {
					//We only want to do this if there is a competition to get
					MBBmgr.adjustDates(div);
					// Validate competition Details and Change Dates to seconds since 1970
					$('compform').addEvent('submit', function(e) {
						e.stop();
						var validated = true;
						if(!MBBmgr.parseDate($('playoffdeadline'))) {
							validated = false;
						}
						if(!MBBmgr.intValidate($('gap'))) {
							validated = false;
						}
						if(validated) {
							var updateReq = new MBBReq('updatecomp.php',$('compserr'), function(response) {
								MBBmgr.adjustDates(div);
								//Shouldn't need to load page as its all there (but we might have updated the summary)
								owner.competitions.loadPage(params);
							});
							updateReq.post($('compform'));
						}
					});
				}
				this.rounds = new MBBSubPage(this,'rounds.php',$('rounds'), function(div) {
					var maxround;
					//Initialise to click on a round to load single round
					this.round = new MBBSubPage(this,'round.php',$('round'),function(div) {
						var answer;
						if(params.rid != 0) {
							answer =$('answer').value.toInt();
						} else {
							answer =0;
						}
						//Initialise Round Data Form
						this.matches = new MBBSubPage(this,'matches.php',$('matches'),function (div) {
						});
						this.options = new MBBSubPage(this,'options.php',$('options'),function(div) {
						})
						this.teams = new MBBSubPage(this,'teams.php',$('teams'),function (div) {
							if (params.cid != 0) {
								$('addall').addEvent('click',function() {
								});
							}
						});
						this.matches.loadPage(params);
						this.options.loadPage($merge(params,{'answer':answer}));
						this.teams.loadPage(params);
					});
					if (params.cid != 0) {
						if (params.rid == 0) {
							if (div.getElement('div')) {
								params.rid = div.getElement('div').get('id').substr(1).toInt();
								this.round.loadPage(params);
							} else {
								params.rid=0;
							}
							maxround = params.rid;
						}
						if (params.rid != 0) {
							//selects a round
							div.getElements('.selectthis').each(function (comp,i) {
								comp.addEvent('click',function(e) {
									e.stop();
									//Make the update form hold this entry
									params.rid = comp.id.substr(1).toInt()
									owner.competition.rounds.round.loadPage(params);
								});
							});
							// allows you to delete a round
							div.getElements('.del').each(function (comp,i) {
								comp.addEvent('click', function(e) {
									e.stop();
									if(confirm('Deleting a Round will delete all the Matches associated with it. Do you wish to Proceed?')) {
										var deleteReq = new MBBReq('deleteround.php',$('compserr'),function (response) {
											maxround--;
											if (params.cid == response.cid && params.rid == response.rid) {
												params.rid = 0;
												owner.competition.rounds.round.loadPage(params);
											}
											if (params.cid == response.cid && params.rid > response.rid ){
												// all above will have had their numbers changed
												params.rid--;
												owner.competition.rounds.round.loadPage(params);
											}
											owner.competition.rounds.loadPage(params);
										});
										deleteReq.get({'cid': params.cid,'rid':comp.id.substr(1).toInt()});
									}
								});
							});
						}
					}
					this.newround = new MBBSubPage(this,'newround.php',$('newround'),function(div) {
						if(params.cid !=0) {
							$('createroundform').addEvent('submit',function(e) {
								e.stop();
								var createReq = new MBBReq('createround.php',$('compserr'),function(response) {
									maxround++;
									if(params.rid == 0) {
										params.rid = response.rid;
										owner.competition.round.loadPage(params);
									}
									owner.competition.rounds.loadPage(params);
								});
								createReq.post($('createroundform'));
							});
						}
					});
					this.newround.loadPage($merge(params,{'mr':maxround+1}));
				});
				this.rounds.loadPage(params);	
			}
				);
		if (this.me.admin) {
			$extend(params,{'global':true});
		}
		this.competitions.loadPage(params);
		this.competition.loadPage(params);

	}
});
