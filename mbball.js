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
	initialize: function(version,me,params) {
		this.parent(version,me);
		var regdiv = $('registration');
		if(regdiv) { //exists means registration is open
			regdiv.getElementById('register').addEvent('submit', function(e) {
				e.stop();
				if(confirm("Click OK to register for the competition and agree to the condition")) {
					var regReq = new MBBReq('register.php',$('regerror'), function(response) {
						window.location.reload(true); //reload the page to pick up self
					});
                    regReq.post($('register'));
				}
			});
		}
	}
});


var MBBAdmin = new Class({
	Extends: MBBall,
	initialize: function(version,me,cid) {
		var params = {'cid':cid, 'rid':0};
		this.parent(version,me);
		this.competitions = new MBBSubPage(this,'competitions.php',$('competitions'),function (div) {
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
		});
		this.competition = new MBBSubPage (this,'competition.php',$('competition'),function (div) {
			var maxround;
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
					} else {
						MBBmgr.adjustDates(div);
					}
				});
			}
			this.rounds = new MBBSubPage(this,'rounds.php',$('rounds'), function(div) {
				//Initialise to click on a round to load single round
				this.round = new MBBSubPage(this,'round.php',$('round'),function(div) {
					var answer;
					if(params.rid != 0) {
						MBBmgr.adjustDates(div);
						elAns =$('answer');
						if(elAns.value == '') {
							answer = 0;
						} else {
							answer = elAns.value.toInt();
						}
						$('roundform').addEvent('submit', function(e) {
							e.stop();
							var validated = true;
							if(!MBBmgr.parseDate($('deadline'))) {
								validated = false;
							}
							if (!MBBmgr.intValidate($('value'))) {
								validated = false;
							}
							if (elAns.value != '') {
								if (!MBBmgr.intValidate(elAns)) {
									validated = false;
								} else {
									answer = elAns.value;
								}
							} else {
								answer = 0;
							}
							if(validated) {
								var updateReq = new MBBReq('updateround.php',$('compserr'), function(response) {
									MBBmgr.adjustDates(div);
									//Should not be necessary to update page
								});
								updateReq.post($('roundform'));
							} else {
								//If we failed to validate we need to adjust dates back
								MBBmgr.adjustDates(div);
							}
						});
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
							var lock = $('lock');
							var tnicClicked;
							var dragStart = function(e) {
								if(lock.checked) {
									e.stop();
									//Start drag
								}
							};
							var teamClicked = function (e) {
								e.stop();
								if(!lock.checked) {
									var team = this;
									var remTiC = new MBBReq('remtic.php',$('compserr'), function (response) {
										var div = new Element('div');
										var span = new Element('span',{
											'class':'tid',
											'events': {'click':tnicClicked},
											'text':response.tid
										}).inject(div);
										if($$('#tnic div').every(function(item,i) {
											if(item.getElement('span').get('text') > response.tid) {
												div.inject(item,'before');
												return false;
											}
											return true;
										})) {
											$('tnic').adopt(div);
										} 
										team.getParent().dispose();
									});
									remTiC.get({'cid':params.cid,'tid':team.get('text')});	
								}
							};
							var changeMpStatus = function(e) {
								var mpReq = new MBBReq('updatepostate.php',$('compserr'),function(response) {
								});
								mpReq.get({'cid':params.cid,'tid':this.name,'mp':this.checked});
							};
							var makeTeam = function(team) {
								var div = new Element('div',{'events':{
									'click':teamClicked,
									'mousedown':dragStart
								}});
								var input = new Element('input',
									{'type':'checkbox','name':team,'events':{'change':changeMpStatus}}
								).inject(div);
								var span = new Element('span',{
									'class':'tid',
									'events': {'click':tnicClicked},
									'text':team
								}).inject(div);
								return div;
							};
							var tnicClicked = function(e) {
								e.stop();
								if(!lock.checked) {
									var team=this;
									var addTiC = new MBBReq('addtic.php',$('compserr'), function (response) {
										var div = makeTeam(response.tid);
										if($$('#tic div').every(function(item,i) {
											if(item.getElement('span').get('text') > response.tid) {
												div.inject(item,'before');
												return false;
											}
											return true;
										})) {
											$('tic').adopt(div);
										} 
										team.getParent().dispose();
									});
									addTiC.get({'cid':params.cid,'tid':team.get('text')});
								}
							};
							var ticElements = $$('#tic span');
							ticElements.addEvent('click',teamClicked);
							ticElements.addEvent('mousedown', dragStart);
							$$('#tnic span').addEvent('click',tnicClicked);
							$$('#tic input').addEvent('change',changeMpStatus);
							$('addall').addEvent('click',function(e) {
								e.stop();
								if(!lock.checked) {
									var addAll = new MBBReq('addalltic.php',$('compserr'), function (response) {
										var teams = response.teams;
										$('tnic').empty();
										var tic = $('tic').empty();
										teams.each(function(team,i) {	
											tic.adopt(makeTeam(team));
										});
									});
									addAll.get({'cid':params.cid});
								}
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
						} else {
							params.rid=0;
						}
						maxround = params.rid;
					}
					this.round.loadPage(params);
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
									owner.competition.rounds.round.loadPage(params);
								}
								owner.competition.rounds.loadPage(params);
							});
							createReq.post($('createroundform'));
						});
					}
				});
				this.adminreg = new MBBSubPage(this,'adminreg.php',$('registered'),function(div) {
					var owner = this.owner;
					if(params.cid !=0) {
						MBBmgr.adjustDates(div);
						$('bbapproval').addEvent('change', function(e) {
							$$('#registered input.bbapprove').each(function (item) {
								item.disabled = !e.target.checked;
							});
						});
						$$('#registered input.bbapprove').addEvent('change',function(e) {
							e.stop();
							if(confirm('You are changing the approval status of a Baby Backup for this Competition. Are you sure you want to do this?')) {
								var updateBBa = new MBBReq('bbapprove.php',$('compserr'),function(response) {
								});
								updateBBa.get($merge(params,{'bbuid':this.name,'approval':this.checked}));
							} else {
								this.checked = !this.checked;
							}
						});
						div.getElements('.del').each(function (comp,i) {
							comp.addEvent('click', function(e) {
								e.stop();
								if(confirm('This will Un-Register this User from this Competition. Do you wish to Proceed?')) {
									var deleteReq = new MBBReq('deleteregistration.php',$('compserr'),function (response) {
										owner.adminreg.loadPage($merge(params,{'bbar':$('bbapproval').checked}));
									});
									deleteReq.get({'cid': params.cid,'ruid':comp.id.substr(1).toInt()});
								}
							});
						});
					}
				});
				this.newround.loadPage($merge(params,{'mr':maxround+1}));
				this.adminreg.loadPage($merge(params,{'bbar':$('bbapproval').checked}));
			});
			this.rounds.loadPage(params);	
		});
		if (this.me.admin) {
			$extend(params,{'global':true});
		}
		this.competitions.loadPage(params);
		this.competition.loadPage(params);

	}
});
