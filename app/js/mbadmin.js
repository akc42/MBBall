/*
 	Copyright (c) 2010 Alan Chandler
    This file is part of MBBall, an American Football Results Picking
    Competition Management software suite.

    MBBall is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBBall is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBBall (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

*/


var MBBAdmin = new Class({
	Extends: MBBall,
	initialize: function(admin,cid,errordiv,messages,maps) {
		this.parent(errordiv);
		var params = {'cid':cid, 'rid':0};
		var emoticons;
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
					if(confirm(messages.deletecomp)) {
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
						if (params.cid == 0 || $('def').checked ) {
							params.cid = response.cid;
							params.rid = 0;
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
				
				var el = $('playoffdeadline');
				var oldtime = el.value;
				var pod = new Calendar.Single(el,{format:'j M Y g:i a (D)',width:'235px',onHideStart:function(){
					if(new Date(el.value *1000) > new Date() || confirm(messages.deadline)){
						oldtime = el.value;
						el.fireEvent('change');
					} else {
						el.value = oldtime;
						pod.resetVal();
					}
					return true;
				}});
				//We only want to do this if there is a competition to get
				// Validate competition Details and Change Dates to seconds since 1970
				//if anything in the form changes then submit automatically
				div.getElements('input').append(div.getElements('select')).append(div.getElements('textarea')).addEvent('change', function(e) {
					if(this.id =='bbapproval') {
						$$('#registered input.bbapprove').each(function (item) {
							item.readOnly = !e.target.checked;
						});
					}
					var validated = true;
					if(!MBB.intValidate($('gap'))) {
						validated = false;
					}
					if(validated & this.name =='condition') {
						// with emoticons it is possible for the change event on this element to fire twice
						// this check prevents a round trip to the server when it is not necessary
						var oldcontent = this.retrieve('old');
						this.store('old',this.value);
						if (oldcontent == this.value) validated = false;
					}
					if(validated) {
						var updateReq = new MBB.req('updatecomp.php', function(response) {
							//Shouldn't need to load page as its all there (but we might have updated the summary)
							owner.competitions.loadPage(params);
							// Or the user picks information (but then user can reload the page)
							$('userpick').empty();
						});
						updateReq.post($('compform'));
					}
				});
				if (emoticons) {
					emoticons.addTextareas(div.getElements('textarea'));
				} else {
					emoticons = new MBB.emoticon($('emoticons'),$('content').getElements('textarea'));
				}
			}
			this.rounds = new MBB.subPage(this,'rounds.php',$('rounds'), function(div) {
				//Initialise to click on a round to load single round
				this.round = new MBB.subPage(this,'round.php',$('round'),function(div) {
					var answer;
					var noopts;
					var matchUpdateLocked = false;
					$('userpick').empty();  //if round changes the user picks will need to change also, so just clear it out and let the user reload if he wants to
					var setMatchEvents = function (div) {
						var ou = $('ou');
						if (!ou.checked) {
							div.getElement('input[name=cscore]').readOnly = true;
						}
						var matchtime = div.getElement('input[name=mtime]');
						var oldtime = matchtime.value;
						var matchcal = new Calendar.Single(matchtime,{format:'D j M g:ia',width:'172px',onHideStart:function(){
							if(new Date(matchtime.value *1000) > new Date() || confirm(messages.matchtime)){
								oldtime = matchtime.value;
								matchtime.fireEvent('change');
							} else {
									matchtime.value = oldtime;
									matchcal.resetVal();
							}
							return true;
						}});

						div.getElements('input[type=text]').append(div.getElements('input[type=checkbox]')).append(div.getElements('textarea')).append(div.getElements('input[name=mtime]')).addEvent('change', function(e) {
						  if(e) e.stop();
						  var validated = true;
							if(validated && (this.name == 'cscore'
									|| this.name == 'ascore' 
									|| this.name == 'hscore')) {
								if(!MBB.intValidate(this)) {
									validated = false;
								}
							}
							if (validated && this.name == 'open' && this.checked) {
								var hid = div.getElement('input[name=hid]');
								if (hid.value ==  '' || hid.value == null) {
									this.checked = false;
									validated = false;
									hid.highlight('#F00');
								}
								if ( validated && (matchtime.value == '' || matchtime.value == 0)) {  //Ask user to confirm if no matchdate is set
									if (!confirm(messages.nomatchdate)) {
										validated = false;
										this.checked = false;
									}
								}

							}
							if(validated && this.name =='comment') {
								// with emoticons it is possible for the change event on this element to fire twice
								// this check prevents a round trip to the server when it is not necessary
								var oldcontent = this.retrieve('old');
								this.store('old',this.value);
								if (oldcontent == this.value) validated = false;
							}	
							if (validated) {
								var updateReq = new MBB.req('updatematch.php',function(response) {
								  //Should not be necessary to update page (but may have effected the user picks part)
								  $('userpick').empty();
								});
								updateReq.post(div.getElement('form'));
							}
						});
						div.getElement('.aid').addEvent('click',function(e) {
							e.stop();
							// switch aid/hid over
							var hid = div.getElement('input[name=hid]');
							if(hid.value != null && hid.value != '' ) {
								// Can only switch if aid exists
								var switchReq = new MBB.req('switchaid.php',function(response){
									div.getElement('input[name=aid]').value = response.aid;
									hid.value = response.hid;
									e.target.set('text',response.aid);
									div.getElement('.hid').getFirst().set('text',response.hid);
									$('userpick').empty();
								});
								switchReq.get(Object.merge(params,{'aid':this.getElement('span').get('text')}));
							}
						});
						div.getElement('.hid').addEvent('click',function(e) {
							e.stop();
							var hid = div.getElement('input[name=hid]');
							if(hid.value != null && hid.value != '' ) {
								var open = div.getElement('input[name=open]');
								if (open.checked) {
									open.highlight('#F00');
								} else {
									var removehidReq = new MBB.req('removehid.php',function(response){
									// remove hid from match
										hid.value = null;
										e.target.set('text','---');
										$('T'+response.hid).removeClass('inmatch');
										$('userpick').empty();
									});
								}
								removehidReq.get(Object.merge(params,{'haid':div.getElement('input[name=aid]').value}));
							}
						});
						div.getElement('.del').addEvent('click',function(e) {
						  e.stop(); 
						  matchUpdateLocked = true;
						  var referReq = new MBB.req('matchrefers.php',function(response) {
							if(response.referers == 0 || confirm(messages.deletematch)) {
								var deleteReq = new MBB.req('deletematch.php',function(response) {
									matchUpdateLocked = false;
									div.dispose();
									if (response.cid != 0) { //looks like we actually managed to delete it
									  $('T'+response.aid).removeClass('inmatch');
									  var hid = $('T'+response.hid);
									  if(hid) hid.removeClass('inmatch'); //only if not null
									}
									$('userpick').empty();
								});
								deleteReq.get(Object.merge(params,{'aid':div.getElement('input[name=aid]').value}));
							} else { 
							  matchUpdateLocked = false;
							}
						  });
						  referReq.get(Object.merge(params,{'aid':div.getElement('input[name=aid]').value}));
						});
						var underdog = div.getElement('input[name=underdog]');
						var AwayUnder = false;	//set true if we need to negate values (because its the array side that is the underdog
						var inputValue = underdog.value.toInt();
						if (inputValue < 0) {
						  AwayUnder = true;
						  inputValue = - inputValue;
						}
						var indexedValue = maps.underdog.indexOf(inputValue);  //convert from score to step value
						if (indexedValue < 0 ) indexedValue = 0;
						if (AwayUnder && indexedValue > 0){
						  indexedValue = -indexedValue;
						  div.getElement('.aid').getElement('span').addClass('isUnderdog');
						  div.getElement('.open').addClass('isUnderdog');
						} else if (indexedValue != 0) {
						  div.getElement('.hid').getElement('span').addClass('isUnderdog');
						  div.getElement('.open').addClass('isUnderdog');
						} 
						var slider = div.getElement('.slider');
						var knob = slider.getElement('.knob');
						new Slider(slider,knob,{
						  minstep:-5,
						  maxstep:5,
						  initial:indexedValue,
						  minortick:1,
						  majortick:10,
						  onTick: function(step) {
						    knob.set("text",maps.underdog[Math.abs(step)]);
						  },
						  onChange:function(step) {
						    if (step == 0) {
						      div.getElement('.aid').getElement('span').removeClass('isUnderdog');
						      div.getElement('.hid').getElement('span').removeClass('isUnderdog');
						      div.getElement('.open').removeClass('isUnderdog');
						      underdog.value = 0;
						    } else if (step < 0 ) {
						      div.getElement('.aid').getElement('span').addClass('isUnderdog');
						      div.getElement('.hid').getElement('span').removeClass('isUnderdog');
						      div.getElement('.open').addClass('isUnderdog');
						      underdog.value = -maps.underdog[-step];
						    } else {
						      div.getElement('.hid').getElement('span').addClass('isUnderdog');
						      div.getElement('.aid').getElement('span').removeClass('isUnderdog');
						      div.getElement('.open').addClass('isUnderdog');
						      underdog.value = maps.underdog[step];
						    }
						    var updateReq = new MBB.req('updatematch.php',function(response) {
							//Should not be necessary to update page (but may have effected the user picks part)
						      $('userpick').empty();
						    });
						    updateReq.post(div.getElement('form'));
						  }
						});
		 
					};
					var changeSelectedAnswer = function(e) {
					  //called when an option has been selected as the correct answer
						e.stop();
						if(this.checked) {
							var changeAnsReq = new MBB.req('changeselans.php', function(response) {
								answer = response.opid;
								$('answer').value = answer;
							});
							changeAnsReq.get(Object.merge(params,{'opid':this.value}));
						}
					};
					var changeAnswer = function(e) {
						e.stop();
						if(MBB.textValidate(this)) {
					  		var changeAnsReq = new MBB.req('changeans.php', function(response) {
								//Nothing to do here?
							});
							changeAnsReq.get(Object.merge(params,{'opid':this.name,'label':this.value}));
						}
					};
					var deleteAnswer = function(e) {
						e.stop();
						var deleteAnsReq = new MBB.req('deleteans.php', function(response) {
							e.target.getParent().getParent().dispose();
							var nullAnsRow = $('nullanswer').getParent().getParent();
							if(nullAnsRow.getNext() == null) {
								nullAnsRow.dispose();
								$('answer').readOnly = false;
								$('answer').value = '';
								answer = 0;
								noopts = 0;
							} else {
								if (response.opid == answer) {
									$('nullanswer').checked = true;
									$('answer').value = 0;
									answer = 0;
								}
							}
							$('userpick').empty();
						});
						deleteAnsReq.get(Object.merge(params,{'opid':this.get('id').substr(1).toInt()}));
					};
					if(params.rid != 0) {
						MBB.adjustDates(div);
						elAns =$('answer');
						if(elAns.value == '') {
							answer = 0;
						} else {
							answer = elAns.value.toInt();
						}
						var dead = $('deadline');
						var oldtime = dead.value;
						var deadcal = new Calendar.Single(dead,{format:'j M Y g:i a (D)',width:'235px',onHideStart:function(){
							if(new Date(dead.value *1000) > new Date() || confirm(messages.quesdead)){
								oldtime = dead.value;
								dead.fireEvent('change');
							} else {
								dead.value = oldtime;
								deadcal.resetVal();
							}
							return true;
						}});
						div.getElements('input').append(div.getElements('textarea')).addEvent('change', function(e) {
							if (this.id == 'ou') {
								//if ou changes then all the matches combined scores are diabled or not
								$('matches').getElements('input[name=cscore]').each(function(item) {
									item.readOnly = ! e.target.checked;
								});
							}
							var validated = true;
							
							if (!MBB.intValidate($('value'))) {
								validated = false;
							}
							if (!MBB.intValidate(elAns)) {
								validated = false;
							}
							if (!MBB.textValidate($('rname'))) {
								validated = false;
							}
							if(validated & this.name =='question') {
								// with emoticons it is possible for the change event on this element to fire twice
								// this check prevents a round trip to the server when it is not necessary
								var oldcontent = this.retrieve('old');
								this.store('old',this.value);
								if (oldcontent == this.value) validated = false;
							}	
							// Ask user to confirm if he wants to open a round with no open matches
							if ( validated && this.name == 'open' && this.checked) {
								var openmatches = $('matches').getElements('input[name=open]');
								if(openmatches.length == 0 ||
										openmatches.every(function (match) {
											return (!match.checked);
										})
									) {
									if (! confirm(messages.nomatchround)) {
										validated = false;
										this.checked = false;
									}
								}
							}
							if(validated) {
								var updateReq = new MBB.req('updateround.php', function(response) {
									//Should not be necessary to update page
									$('userpick').empty(); //but user picks may have changed
								});
								var clearCache = div.getElement('input[name=cache]');
								if(this.name == 'open')
								  clearCache.value = true;
								else
								  clearCache.value = div.getElement('input[name=open]').checked;
								updateReq.post(document.id('roundform'));
							}
						});
						var points = div.getElement('input[name=value]');
						var slider = div.getElement('.slider');
						var knob = slider.getElement('.knob');
						var stepValue = maps.points.indexOf(points.value.toInt()); //map back from points to step
						if (stepValue < 0) stepValue = 0;
						new Slider(slider,knob,{
						  minstep:0,
						  maxstep:6,
						  initial:stepValue,
						  minortick:1,
						  majortick:10,
						  onTick:function(step) {
						    knob.set("text",maps.points[step]);
						  },
						  onChange:function(step) {
						    points.value = maps.points[step];
						    var updateReq = new MBB.req('updateround.php', function(response) {
							    //Should not be necessary to update page
							    $('userpick').empty(); //but user picks may have changed
						    });
						    updateReq.post($('roundform'));
						  }
						});
						// if user clicks on create option area we need to create an option
						$('option').addEvent('click', function(e) {
							e.stop();
							if (typeof(noopts) == 'number' ) { //if set option page must have loaded
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
										answer = 0;
										$('answer').value = 0;
									}
									var option = new Element('tr').adopt(
										new Element('td').adopt(
											new Element('input',{
												'type':'radio',
												'name':'option',
												'class':'option_select',
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
									$('userpick').empty();
								});
								newOptionReq.get(Object.merge(params,{'opid':noopts+1}));
							}
						});
						if (emoticons) {
							emoticons.addTextareas(div.getElements('textarea'));
						} else {
							emoticons = new MBB.emoticon($('emoticons'),$('content').getElements('textarea'));
						}
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
							if (emoticons) {
								emoticons.addTextareas(div.getElements('textarea'));
							} else {
								emoticons = new MBB.emoticon($('emoticons'),$('content').getElements('textarea'));
							}
						}
						$('userpick').empty();
					});
					this.options = new MBB.subPage(this,'options.php',$('options'),function(div) {
						if (params.cid != 0 && params.rid != 0) {
							noopts = $('noopts').value.toInt();
							if (noopts != 0) {
								$('answer').readOnly = true;
								$('nullanswer').addEvent('change',changeSelectedAnswer);
								var of = $('optionform');
								of.getElements('.option_select').addEvent('change',changeSelectedAnswer);
								of.getElements('.option_input').addEvent('change',changeAnswer);
								of.getElements('.del').addEvent('click',deleteAnswer);
							}
						}
						$('userpick').empty();
					});
					this.teams = new MBB.subPage(this,'teams.php',$('teams'),function (div) {
						if (params.cid != 0) {
							var lock = $('lock');
							var teamUpdateLocked = false;
							var tnicClicked;
							var teamClicked = function (e) {
								e.stop();
								var team = this;
								if(!lock.checked) {
								    if(!teamUpdateLocked) {
									teamUpdateLocked = true;
									var remTiC = new MBB.req('remtic.php', function (response) {
									  if (response.OK) {
										var div = new Element('div',{'id':'S'+response.tid,'class':'tic'});
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
										if(document.id('P'+response.tid))document.id('P'+response.tid).dispose(); //Get rid of playoff stuff if its there
									  } else {
									    alert(messages.constraint);
									  } 
									  teamUpdateLocked = false;
									});
									remTiC.get({'cid':params.cid,'tid':team.get('text')});
								    }
								} else {
								  if(!matchUpdateLocked) { //we can only do something when we are not doing another
									//only do something if not already in a match
									if(!this.getParent().hasClass('inmatch')) {
										var teamName = this.get('text');
										if ($$('.match').every(function(match) {
											var hidSpan = match.getElement('input[name=hid]'); 
											if(hidSpan.value == '') {
												// found a match so we can add this team to it
												var addhidReq = new MBB.req('addhid.php', function(response) {
												  if(response.hid != '---') {
													hidSpan.value = teamName;
													match.getElement('div.hid').getFirst().set('text',teamName);
													team.getParent().addClass('inmatch');
												  }
												  matchUpdateLocked = false; //release for another click
												});
												matchUpdateLocked = true;  //Can't do two of these in parallel, so lock out
												addhidReq.get(Object.merge(params,{
													'aid':match.getElement('input[name=aid]').value,
													'hid':teamName}));
												return false;
											}
											return true;
										})){
											// Was no match with missing AID, so create new match
											var match = new Element('div',{'class':'match'});
											match.inject($('matches'));
											var matchPage = new MBB.subPage(this,'creatematch.php',match, function(div) {
												if (!$('ou').checked)  match.getElement('input[name=cscore]').readOnly=true;
												setMatchEvents(match);
												team.getParent().addClass('inmatch');
												matchUpdateLocked = false; //Release
											});
											matchupdateLocked = true; //prevent any other clicks doing something whilst be build this match
											matchPage.loadPage(Object.merge(params,{'aid':teamName}));
										}
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
								var div = new Element('div',{'id':'T'+team,'class':'tic'});
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
							var makePlayOffSlide = function(slider,knob,initial,team) {
							  var stepValue = maps.playoff.indexOf(initial.toInt()); //map back from points to step
							  if (stepValue < 0) stepValue = 0;
							  var poff = new Slider(slider,knob,{
							    minstep:1,
							    maxstep:5,
							    initial:stepValue+1,
							    minortick:1,
							    onTick:function(step) {
							      knob.set("text",maps.playoff[step-1]);
							    },
							    onChange:function(step) {
							      var setPOScore = new MBB.req('setposcore.php',function(response) {
								//don't think there is anything to do
							      });
							      setPOScore.get({'cid':params.cid,'tid':team,'pscore':maps.playoff[step-1]});
							    }
							  });
							};
							var makePlayoffDiv= function(team,where,el) {
							  var div = new Element('div',{'id':'P'+team,'class':'tic'});
							  var slider = new Element('div',{'class':'pslide'});
							  var knob = new Element('div',{'class':'knob','text':'1'}).inject(slider);
							  slider.inject(div);
							  if(where == 'b') div.inject(el,'before');
							  if(where == 'a') el.adopt(div);
							  makePlayOffSlide(slider,knob,1,team);
							  return;
							};
							//For all teams currently in the compeition we need to create their sliders
							document.id('pop').getElements('div.tic').each(function(team){
							  var slider = team.getElement('.pslide');
							  var knob = slider.getElement('.knob');
							  makePlayOffSlide(slider,knob,knob.get("text"),team.get('id').substring(1));
							});
							  
							tnicClicked = function(e) {
								e.stop();
								if(!lock.checked) {
								  if(!teamUpdateLocked) {
								    teamUpdateLocked = true;
									var team=this;
									var addTiC = new MBB.req('addtic.php', function (response) {
										var div = makeTeam(response.tid);
										//go down tics looking for first div with team name greater than
										//ours and inject before it
										if($$('#tic div').every(function(item,i) {
											var ateam = item.getElement('span').get('text') 
											if( ateam > response.tid) {
												div.inject(item,'before');
												makePlayoffDiv(response.tid,'b',document.id('P'+ateam));
												return false;
											}
											return true;
										})) {
											$('tic').adopt(div);
											makePlayoffDiv(response.tid,'a',document.id('pop'));
										} 
										team.getParent().dispose();
										teamUpdateLocked = false;
									});
									addTiC.get({'cid':params.cid,'tid':team.get('text')});
								  }
								} else {
									$('lock_cell').highlight('#F00');
								}
							};
							$$('#tic span').addEvent('click',teamClicked);
							$$('#tnic span').addEvent('click',tnicClicked);
							$$('#tic input').addEvent('change',changeMpStatus);
							$('addall').addEvent('click',function(e) {
								e.stop();
								if(!lock.checked) {
								  if(!teamUpdateLocked) {
									var addAll = new MBB.req('addalltic.php', function (response) {
										teamUpdateLocked = false;
										var teams = response.teams;
										$('tnic').empty();
										var tic = $('tic').empty();
										var pop = document.id('pop').empty(); //make empty because all teams will be returned
										teams.each(function(team,i) {	
											tic.adopt(makeTeam(team));
											makePlayoffDiv(team,'a',pop);
										});
									});
									teamUpdateLocked = true;
									addAll.get({'cid':params.cid});
								  }
								} else {
									$('lock_cell').highlight('#F00');
								}
							});
							//swap over playoff and tnic columns as we move in and out of lock mode
							lock.addEvent('change',function(e) {
							  e.stop();
							  if(lock.checked) {
							    document.id('tnichead').addClass('hidden');
							    document.id('tnic').addClass('hidden');
							    document.id('pophead').removeClass('hidden');
							    document.id('pop').removeClass('hidden');
							  } else {
							    document.id('pophead').addClass('hidden');
							    document.id('pop').addClass('hidden');
							    document.id('tnichead').removeClass('hidden');
							    document.id('tnic').removeClass('hidden');
							  }
							});
						}
					});
					if (params.rid != 0){
						this.matches.loadPage(Object.merge(params,{'ou':$('ou').checked }));
						this.options.loadPage(Object.merge(params,{'answer':answer}));
					} else {
						this.matches.loadPage(params);
						this.options.loadPage(params);
					}
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
						// try and find the highest open round to display
						if (params.rid != 0) {
							var highestOpenRound = 0;
							var roundii = div.getElements('input[name=open]')
							roundii.each(function(round) {
								if (round.value != 0) {
									highestOpenRound = Math.max(highestOpenRound, round.value);
								}
							});
							if (highestOpenRound != 0) params.rid = highestOpenRound;
						}
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
								if(confirm(messages.deleteround)) {
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
					this.adminpick = new MBB.subPage(this,'adminpick.php',$('userpick'),function(div) {
						if ($('pick')) { //We loaded the page and there is something there 
							MBB.adjustDates(div);
							this.teams = new Object({});
							this.lastpick = new Object({});
							var picks = div.getElements('.ppick');
							var that =this;
							// We make a hash of every checked item - which we can then use when an item changes to
							// check that the new item isn't already picked, and if so set it back
							picks.each(function(item) {
								if(item.checked) {
									that.teams[item.value] = item;
									that.lastpick[item.name] = item;
								}
							});
							picks.addEvent('change',function(e) {
								e.stop();
								var lastValue = that.lastpick[this.name];
								if(that.teams[this.value] != undefined) {
									//this team already has a selection, so lets find out what
									var existingSelection = that.teams[this.value];
									existingSelection.getParent().highlight('#F00');
									// now change it back
									this.checked = false;
									if(lastValue) lastValue.checked = true;
								} else {
									// This team did not have a selection before, so now set one
									// and take out old values;
									that.teams[this.value] = this;
									div.getElement('input[name=P'+this.value+']').value='yes'; //team we just selected
									if(lastValue) {
									  delete that.teams[lastValue.value];
									  div.getElement('input[name=P'+lastValue.value+']').value = 'yes'; //team we just deselected
									}
									delete that.lastpick[this.name];
									that.lastpick[this.name] = this;									
								}
							});
							div.getElements('input.opt_pick').addEvent('change',function(e) {
							  e.stop();
							  document.id('admin_answer').value='yes'; //say admin has changed a pick (it might be a single answer or one of a set of options
							});
							div.getElements('input.match_pick').addEvent('change',function(e) {
							  e.stop();
							  div.getElement('input[name=A'+this.name.substr(1)+']').value='yes'; //say admin changed this match pick for this team
							});
							//These items are only there if user has registered
							$('make_picks').addEvent('click', function(e) {
								e.stop();
								var validate = true;
								var answer = $('answer');
								if(answer) {
									//only here if answer is defined (no options to select (in which case Answer must be an integer
									if(!MBB.intValidate(answer)) {
									  validated = false; //Summit but leave an
									  answer.value = '';
									}
								}
						
								var pickReq = new MBB.req('createpicks.php', function(response) {
								  if (validated) {
									  _gaq.push(['_trackPageview','/football/picks/adminmade']);
									  document.id('pick').dispose();
								  } else {
									  $('bonus_pick').getElement('textarea').value=messages.noquestion;
								  }
								});
								pickReq.post($('pick'));
							});
						}
					});
					if(params.cid !=0) {
						MBB.adjustDates(div);
						$$('#registered input.gapprove').addEvent('change',function(e) {
							e.stop();
							if(confirm(messages.approve)) {
								var updatega = new MBB.req('gapprove.php',function(response) {
								});
								updatega.get(Object.merge(params,{'bbuid':this.name,'approval':this.checked}));
							} else {
								this.checked = !this.checked;
							}
						});
						div.getElements('.del').each(function (comp,i) {
							comp.addEvent('click', function(e) {
								e.stop();
								if(confirm(messages.unregister)) {
									var deleteReq = new MBB.req('deleteregistration.php',function (response) {
										owner.adminreg.loadPage(Object.merge(params,{'bbar':$('bbapproval').checked}));
									});
									deleteReq.get({'cid': params.cid,'ruid':comp.id.substr(1).toInt()});
								}
							});
						});
						var that = this;
						div.getElements('.user_name').addEvent('click', function(e) {
							e.stop();
							that.adminpick.loadPage(Object.merge(params,{
								'auid':this.get('id').substr(1),
								'name':this.get('text'),
								'gap':$('gap').value,
								'pod':$('playoffdeadline').value
							}));
						});
					}
				});
				this.newround.loadPage(Object.merge(params,{'mr':maxround+1}));
				this.adminreg.loadPage(Object.merge(params,{'bbar':$('bbapproval').checked}));
			});
			this.rounds.loadPage(params);	
		});
		if (admin) {
			Object.append(params,{'global':true});
		}
		this.competitions.loadPage(params);
		this.competition.loadPage(params);

	}
});
