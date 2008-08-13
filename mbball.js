/* MBB - Melindas Backuos Ball application 
 * (c) 2008 Alan Chandler - licenced under the GPL
*/
MBB = function() {
  var reqOpts;
  var errorDiv;
  return {
    setErrorDiv: function(div) {
      errorDiv = div;
    },
    setRO : function(ro) {
      reqOpts = ro;
    },
   subPage : new Class({
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
	    this.request.get($merge(reqOpts,params || {}));
    }
  }),
  req: new Class({
      initialize: function (url,success) {
        this.req = new Request.JSON({
          url:url,
          onComplete: function(response,html) {
            if(response) {
	      success(response);
	    } else {
	      errorDiv.empty();
	      errorDiv.adopt(html);
	    }
	  }
	});
      },
      get:function(params) {
	 this.req.get($merge(reqOpts,params));
      },
      post: function (params) {
	this.req.post(params);
      }
    }),
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
				datespan.set('text',new Date(d.toInt()*1000).toLocaleString().substr(0,21));
				break;
			case "input":
				d=datespan.value;
				if(d == '' || d=='0') {
					datespan.value = '';
					break;
				}
				datespan.value = new Date(d.toInt()*1000).toLocaleString().substr(0,21);
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
		if(el.value == '') return false;
		if (isNaN(el.value.toInt())) {
			el.addClass('error');
			return false;
		}
		return true;
	},
	textValidate: function(el) {
	  el.removeClass('error');
	  if(el.value = '') {
	  	el.addClass('.error');
        return false;
     }      
	  return true;
	}

  };
}();

var MBBall = new Class({
	initialize: function(version,me,errordiv) {
		this.me = me;
		MBB.setRO({'uid':me.uid,'pass':me.password});
		var span=$('version');
		span.set('text',version);
		MBB.setErrorDiv ($(errordiv));
	}
});


var MBBUser = new Class({
	Extends: MBBall,
	initialize: function(version,me,params,errordiv) {
		this.parent(version,me,errordiv);
		var regdiv = $('registration');
		if(regdiv) { //exists means registration is open
			regdiv.getElementById('register').addEvent('submit', function(e) {
				e.stop();
				if(confirm("Click OK to register for the competition and agree to the condition")) {
					var regReq = new MBB.req('register.php', function(response) {
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
	initialize: function(version,me,cid,errordiv) {
		this.parent(version,me,errordiv);
		var params = {'cid':cid, 'rid':0};
		this.competitions = new MBB.subPage(this,'competitions.php',$('competitions'),function (div) {
			var owner = this.owner;
			$$('input.default').each(function(rb,i) {
				if(params.cid == 0 && rb.checked) params.cid = rb.value; 
				rb.addEvent('change',function(e) {
					var dcReq = new MBB.req('setdefault.php', function (response) {
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
						var deleteReq = new MBB.req('deletecomp.php',function (response) {
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
				if(MBB.textValidate($('desc'))) {
					var createReq = new MBB.req('createcomp.php', function(response) {
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
		this.competition = new MBB.subPage (this,'competition.php',$('competition'),function (div) {
			var maxround;
			var owner = this.owner;
			if(params.cid != 0) {
				//We only want to do this if there is a competition to get
				MBB.adjustDates(div);
				// Validate competition Details and Change Dates to seconds since 1970
				//if anything in the form changes then submit automatically
				div.getElements('input').extend(div.getElements('select')).extend(div.getElements('textarea')).addEvent('change', function(e) {
					e.stop();
					if(this.id =='bbapproval') {
						$$('#registered input.bbapprove').each(function (item) {
							item.readOnly = !e.target.checked;
						});
					}
					var validated = true;
					if(!MBB.parseDate($('playoffdeadline'))) {
						validated = false;
					}
					if(!MBB.intValidate($('gap'))) {
						validated = false;
					}
					if(validated) {
						var updateReq = new MBB.req('updatecomp.php', function(response) {
							MBB.adjustDates(div);
							//Shouldn't need to load page as its all there (but we might have updated the summary)
							owner.competitions.loadPage(params);
						});
						updateReq.post($('compform'));
					} else {
						MBB.adjustDates(div);
					}
				});
			}
			this.rounds = new MBB.subPage(this,'rounds.php',$('rounds'), function(div) {
				//Initialise to click on a round to load single round
				this.round = new MBB.subPage(this,'round.php',$('round'),function(div) {
					var answer;
					var noopts;
					
					var setMatchEvents = function (div) {
						div.getElements('input').extend(div.getElements('textarea')).addEvent('change', function(e) {
							var validated = true;
							var surroundDiv = this.getParent();
							if(surroundDiv.hasClass('mtime') && !MBB.parseDate(this)){
								validated = false;
							}
							if(surroundDiv.hasClass('.csscore') 
									|| surroundDiv.hasClass('hscore') 
									|| surroundDiv.hasClass('ascore')) {
								if(!MBB.intValidate(this)) {
									validated = false;
								}
							}
							if (validated) {
								var updateReq = new MBB.req('updatematch.php',function(response) {
									MBB.adjustDates(div);
									//Should not be necessary to update page
								});
								updateReq.post(e.target.getParents('form')[0]);
							} else {
								//If we failed to validate we need to adjust dates back
								MBB.adjustDates(div);
							}
						});
						div.getElement('.hid').addEvent('click',function(e) {
							e.stop();
							// switch aid/hid over
							var switchReq = new MBB.req('switchhid.php',function(response){
								div.getElement('input[name=hid]').value = response.hid;
								div.getElement('input[name=aid]').value = response.aid;
								e.target.getFirst().set('text') = response.hid;
								e.target.getNext().getFirst().set('text') = response.aid;
							});
							switchReq.get($merge(params,{'hid':this.getElement('span').get('text')}));
							
						});
						div.getElement('.aid').addEvent('click',function(e) {
							e.stop();
							var removeaidReq = new MBB.req('removeaid.php',function(response){
							// remove aid from match
							  div.getElement('input[name=aid]').value = '';
							  e.target.getNext().getFirst().set('text') = '';
							  $('T'+response.aid).removeClass('inmatch');
							});
							removeaidReq.get($merge(params,{'hid':div.getElement('input[name=hid]').value}))
						});
						div.getElement('.del').addEvent('click',function(e) {
						  e.stop(); 
							if(confirm('This will delete the match.  Are you sure?')) {
								var deleteReq = new MBB.req('deletematch.php',function(response) {
									div.dispose();
									$('T'+response.hid).removeClass('inmatch');
									$('T'+response.aid).removeClass('inmatch');
								});
								deleteReq.get($merge(params,{'hid':div.getElement('input[name=hid]').value}));
							}
						});
					};
					var changeSelectedAnswer = function(e) {
					  //called when an option has been selected as the correct answer
						e.stop();
						if(this.checked) {
							var changeAnsReq = new MBB.req('changeselans.php', function(response) {
					      	answer = response.answer;
					      	$('answer').value = answer;
							});
					    	changeAnsReq.get($merge(params,{'opid':this.value}));
						}
					};
					var changeAnswer = function(e) {
					  e.stop();
					  var changeAnsReq = new MBB.req('changeans.php', function(response) {
					    //Nothing to do here?
					  });
					  changeAnsReq.get($merge(params,{'opid':this.name}));
					};
					var deleteAnswer = function(e) {
						e.stop();
						var deleteAnsReq = new MBB.req('deleteans.php', function(response) {
							if(--noopts <= 0) {
							// No more options left - so we are back to the traditional answer
								$('nullanswer').getParents('tr').dispose();
								$('answer').readOnly = false;
								$('answer').value = '';
								answer = 0;
							} else {
								if (response.opid == answer) {
									$('nullanswer').checked = true;
									$('answer').value = 0;
									answer = 0;
								}
							}
							e.target.getParents('tr').dispose();
						});
					};
					if(params.rid != 0) {
						MBB.adjustDates(div);
						elAns =$('answer');
						if(elAns.value == '') {
							answer = 0;
						} else {
							answer = elAns.value.toInt();
						}
						div.getElements('input').extend(div.getElements('textarea')).addEvent('change', function(e) {
							e.stop();
							if (this.id == 'ou') {
								//if ou changes then all the matches combined scores are diabled or not
								$$('.cscore').each(function(item) {
									item.input.readOnly = ! e.target.checked;
								});
							}
							var validated = true;
							if(!MBB.parseDate($('deadline'))) {
								validated = false;
							}
							if (!MBB.intValidate($('value'))) {
								validated = false;
							}
							if (!MBB.intValidate(elAns)) {
								validated = false;
							}
							if (!MBB.textValidate($('rname'))) {
								validated = false;
							}
							if(validated) {
								var updateReq = new MBB.req('updateround.php', function(response) {
									MBB.adjustDates(div);
									//Should not be necessary to update page
								});
								updateReq.post($('roundform'));
							} else {
								//If we failed to validate we need to adjust dates back
								MBB.adjustDates(div);
							}
						});
						// if user clicks on create option area we need to create an option
						$('option').addEvent('click', function(e) {
							e.stop();
							if (noopts) { //if set option page must have loaded
								var newOptionReq = new MBB.req('createoption.php', function(response) {
									$('answer').readOnly = true;
									if (noopts == 0) {
									//We just created our first option, so we also have to create the no answer row first
										var nuloption = new Element('tr').adopt(
											new Element('td').adopt(
												new Element('input',{
													'id':'nullanswer',
			 										'type':'radio',
			  										'name':'option',
			   										'value':0,
													'checked':true,
													'events':{'change':changeSelectedAnswer}
												})
											)
										).adopt(
											new Element('td',{
												'colspan':2
											}).adopt(
												new Element('span',{
													'text':'No Answer Set Yet'
												})
											)
										).inject($('optionform').getElement('tbody'));
									}
									var option = new Element('tr').adopt(
										new Element('td').adopt(
											new Element('input',{
												'type':'radio',
												'name':'option',
												'value':response.opid,
												'events':{'change':changeSelectedAnswer}
											})
										)
									).adopt(
										new Element('td').adopt(
											new Element('input',{
												'type':'text',
			 									'name':response.opid,
			 									'class':'option_input',
			 									'events':{'change':changeAnswer}
											})
										)
									).adopt(
										new Element('td').adopt(
											new Element('div',{
												'id':'O'+response.opid,
												'class':'del',
												'events':{'click':deleteAnswer}
											})
										)
									).inject($('optionform').getElement('tbody'));
									noopts = response.opid; //should be one more than before (no need to update hidden input - its ignored
								});
								newOptionReq.get($merge(params,{'opid':noopts+1}));
							}
						});
					} else {
						answer =0;
					}
					//Initialise Round Data Form
					this.matches = new MBB.subPage(this,'matches.php',$('matches'),function (div) {
						if (params.cid !=0 && params.rid != 0) {
							MBB.adjustDates(div);
							div.getElements('.match').each(function(match) {
								setMatchEvents(match);
							});
						}
					});
					this.options = new MBB.subPage(this,'options.php',$('options'),function(div) {
						if (params.cid != 0 && params.rid != 0) {
							noopts = $('noopts').value;
							if (noopts != 0) $('answer').readOnly = true;
							div.getElements('input[name=option]').addEvent('change',changeSelectedAnswer)
								.getParent().getNext().getFirst().addEvent('change',changeAnswer)
								.getParent().getNext().getFirst().addEvent('click',deleteAnswer);
						}
					})
					this.teams = new MBB.subPage(this,'teams.php',$('teams'),function (div) {
						if (params.cid != 0) {
							var lock = $('lock');
							var tnicClicked;
							var teamClicked = function (e) {
								e.stop();
								var team = this;
								if(!lock.checked) {
									var remTiC = new MBB.req('remtic.php', function (response) {
										var div = new Element('div',{'id':'S'+response.tid});
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
								} else {
									//only do something if not already in a match
									if(!this.getParent().hasClass('inmatch')) {
										var teamName = this.get('text');
										if ($$('.match').every(function(match) {
											var aidSpan = match.getElement('input[name=aid]'); 
											if(aidSpan.value == '') {
												// found a match so we can add this team to it
												var addaidReq = new MBB.req('addaid.php', function(response) {
													aidSpan.value = teamName;
													match.getElement('div.aid').getFirst().set('text',teamName);
													team.getParent().addClass('inmatch');
												});
												addaidReq.get($merge(params,{
													'hid':match.getElement('input[name=hid]').value,
													'aid':teamName}));
												return false;
											}
											return true;
										})){
											// Was no match with missing AID, so create new match
											var match = new Element('div',{'class':'match'});
											match.inject($('matches'));
											var matchPage = new MBB.subPage(this,'creatematch.php',match, function(div) {
												if (!$('ou').checked)  match.getElement('.cscore').readOnly=true;
												setMatchEvents(match);
												team.getParent().addClass('inmatch');
											});
											matchPage.loadPage($merge(params,{'hid':teamName}));
										}
									}
								}
							};
							var changeMpStatus = function(e) {
								var mpReq = new MBB.req('updatepostate.php',function(response) {
								});
								mpReq.get({'cid':params.cid,'tid':this.name,'mp':this.checked});
							};
							var makeTeam = function(team) {
								var div = new Element('div',{'id':'T'+team});
								var input = new Element('input',
									{'type':'checkbox','name':team,'events':{'change':changeMpStatus}}
								).inject(div);
								var span = new Element('span',{
									'class':'tid',
									'events': {'click':teamClicked},
									'text':team
								}).inject(div);
								return div;
							};
							var tnicClicked = function(e) {
								e.stop();
								if(!lock.checked) {
									var team=this;
									var addTiC = new MBB.req('addtic.php', function (response) {
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
							$$('#tic span').addEvent('click',teamClicked);
							$$('#tnic span').addEvent('click',tnicClicked);
							$$('#tic input').addEvent('change',changeMpStatus);
							$('addall').addEvent('click',function(e) {
								e.stop();
								if(!lock.checked) {
									var addAll = new MBB.req('addalltic.php', function (response) {
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
					this.matches.loadPage($merge(params,{'ou':$('ou').checked }));
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
									var deleteReq = new MBB.req('deleteround.php',function (response) {
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
				this.newround = new MBB.subPage(this,'newround.php',$('newround'),function(div) {
					if(params.cid !=0) {
						$('createroundform').addEvent('submit',function(e) {
							e.stop();
							var createReq = new MBB.req('createround.php',function(response) {
								maxround++;
								owner.competition.rounds.loadPage(params);
							});
							createReq.post($('createroundform'));
						});
					}
				});
				this.adminreg = new MBB.subPage(this,'adminreg.php',$('registered'),function(div) {
					var owner = this.owner;
					if(params.cid !=0) {
						MBB.adjustDates(div);
						$$('#registered input.bbapprove').addEvent('change',function(e) {
							e.stop();
							if(confirm('You are changing the approval status of a Baby Backup for this Competition. Are you sure you want to do this?')) {
								var updateBBa = new MBB.req('bbapprove.php',function(response) {
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
									var deleteReq = new MBB.req('deleteregistration.php',function (response) {
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
