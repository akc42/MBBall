/*
 	Copyright (c) 2008,2009 Alan Chandler
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

MBB = function() {
	var m_names = ["Jan","Feb","Mar","Apr","May","Jun","Jly","Aug","Sep","Oct","Nov","Dec"];
	var d_names = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"]
	var formatDate = function(d) {
		//d is a string with seconds from 1st Jan 1970
		var myDate = new Date(d.toInt()*1000);
		var ch = myDate.getHours();
		var ap = (ch < 12)? 'am':'pm';
		ch = (ch == 0)?12:(ch > 12)?ch-12:ch;
		var min = myDate.getMinutes();
		min = min + "";
		min = (min.length == 1)?'0'+min:min;
		return	myDate.getDate() + ' ' + m_names[myDate.getMonth()] + ' ' + myDate.getFullYear() +
				' '+ ch + ':' + min + ' ' + ap + ' ('+d_names[myDate.getDay()] +')';
	};
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
	                    div.removeClass('loading');
	                    div.adopt(html);
	                    iSP(div);
	               },
	               onFailure: function(){
                       var output = message || '<p>Failed to Read Page from '+url+'<p>';
        	           div.adopt(output);
              	   }
	           });
            },
            loadPage: function(params) {
		        this.div.empty();
		        this.div.addClass('loading');
	            this.request.get(Object.merge(reqOpts,params || {}));
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
	            this.req.get(Object.merge(reqOpts,params));
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
		    	d = datespan.get('text');
		    	if(d == '' || d=='0') {
		    		datespan.set('text','');
		    	} else {
		    		datespan.set('text',formatDate(d));
		    	}
		    });
	    },
	    intValidate: function(el) {
		    el.removeClass('error');
		    if(el.value == '') return true;
		    if (isNaN(el.value.toInt())) {
		    	el.addClass('error');
		    	return false;
		    }
		    return true;
	    },
	    textValidate: function(el) {
	        el.removeClass('error');
	        if(el.value == '') {
	  	        el.addClass('error');
                return false;
            }      
	        return true;
	    },
 	    emoticon: new Class({
		    initialize: function(container,outputDivs) {
		    	var that = this;
		    	this.currentFocus = outputDivs[0];
		    	outputDivs.addEvent('focus',function(e) {
		    		that.currentFocus = this;
		    	});
		    	container.getElements('img').addEvent('click', function(e) {
		    		e.stop();
		    		var doBlur = function(e) {
		    			e.stop();
		    			this.removeEvent('blur',doBlur);
		    			this.fireEvent('change',e);
		    		};
		    		var key = this.get('alt');
		    		pageTracker._trackPageview('/football/event/emoticon-click/'+key.substr(1));
		    		that.currentFocus.value += key;
		    		that.currentFocus.focus();
		    		that.currentFocus.addEvent('blur',doBlur);
		    	});
		    },
		    addTextareas:function(outputDivs) {
		    	var that = this;
		    	outputDivs.addEvent('focus',function(e) {
		    		that.currentFocus = this;
		    	});
		    }	
	    })
    };
}();

var MBBall = new Class({
	initialize: function(me,errordiv) {
		this.me = me;
		MBB.setRO({'uid':me.uid,'pass':me.password});
		MBB.setErrorDiv (errordiv);
	}
});


var MBBUser = new Class({
	Extends: MBBall,
	initialize: function(me,params,errordiv) {
		this.parent(me,errordiv);
		var regdiv = $('registration');
		if(regdiv) { //exists means registration is open
			regdiv.getElementById('register').addEvent('submit', function(e) {
				e.stop();
				pageTracker._trackPageview('/football/register/submit');
				if(confirm("Click OK to register for the competition and agree to the condition")) {
					pageTracker._trackPageview('/football/register/agree');
					var regReq = new MBB.req('register.php', function(response) {
						pageTracker._trackPageview('/football/register/registered');
						window.location.reload(true); //reload the page to pick up self
					});
                    regReq.post($('register'));
				}
			});
		}
		if(me.registered) {
			this.teams = new Object({});
			this.lastpick = new Object({});
			var picks = $$('.ppick')
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
				if(that.teams[this.value] != undefined ) {
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
					if(lastValue) delete that.teams[lastValue.value];
					delete that.lastpick[this.name];
					that.lastpick[this.name] = this;
				}
			});
			
			//These items are only there if user has registered
			var make_picks = $('make_picks');
			if (make_picks) { //Only if there are any because time has not made all picks disappeared
				make_picks.addEvent('click', function(e) {
					e.stop();
					var validated = true;
					var answer = $('answer');
					if(answer) {
						//only here if answer is defined (no options to select (in which case Answer must be an integer
						if(!MBB.intValidate(answer)) {
							validated = false; //don't submit
							answer.value = '';
						}
					}
			
					var pickReq = new MBB.req('createpicks.php', function(response) {
						if (validated) {
							pageTracker._trackPageview('/football/picks/made');
							window.location.reload(true); //reload page to pick up picks
						} else {
							pageTracker._trackPageview('/football/picks/error-bonus');
							$('bonus_pick').getElement('textarea').value="ERROR - your picks were made, but the bonus question was NOT answered.  It needs to be a whole number (integer)"
						}
					});
					pickReq.post($('pick'));
					if(validated) {
						var content = $('content');
						content.getElement('table').destroy();
						var div = new Element('div',{'class':'loading'});
						div.inject(content);
					}
				});
				if($('emoticons')) this.emoticon = new MBB.emoticon($('emoticons'),$('registered').getElements('textarea'));
			}
		}
	}
});


var MBBAdmin = new Class({
	Extends: MBBall,
	initialize: function(me,cid,errordiv) {
		this.parent(me,errordiv);
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
				
				var el = $('playoffdeadline');
				var oldtime = el.value;
				var pod = new Calendar.Single(el,{format:'j M Y g:i a (D)',width:'235px',onHideStart:function(){
					if(new Date(el.value *1000) > new Date() || confirm('Do you mean to set the deadline before now')){
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
					$('userpick').empty();  //if round changes the user picks will need to change also, so just clear it out and let the user reload if he wants to
					var setMatchEvents = function (div) {
						var ou = $('ou');
						if (!ou.checked) {
							div.getElement('input[name=cscore]').readOnly = true;
						}
						var matchtime = div.getElement('input[name=mtime]');
						var oldtime = matchtime.value;
						var matchcal = new Calendar.Single(matchtime,{format:'D j M g:ia',width:'172px',onHideStart:function(){
							if(new Date(matchtime.value *1000) > new Date() || confirm('Do you mean to set the matchtime before now')){
								oldtime = matchtime.value;
								matchtime.fireEvent('change');
							} else {
									matchtime.value = oldtime;
									matchcal.resetVal();
							}
							return true;
						}});

						div.getElements('input').append(div.getElements('textarea')).addEvent('change', function(e) {
							var validated = true;
							if(this.name == 'cscore'
									|| this.name == 'hscore' 
									|| this.name == 'ascore') {
								if(!MBB.intValidate(this)) {
									validated = false;
								}
							}
							if (this.name == 'open' && this.checked) {
								var aid = div.getElement('input[name=aid]');
								if (aid.value ==  '' || aid.value == null) {
									this.checked = false;
									validated = false;
									aid.highlight('#F00');
								}
								if ( validated && (matchtime.value == '' || matchtime.value == 0)) {  //Ask user to confirm if no matchdate is set
									if (!confirm('Are you sure you want to open this match without a match date set?')) {
										validated = false;
										this.checked = false;
									}
								}

							}
							if(validated & this.name =='comment') {
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
						div.getElement('.hid').addEvent('click',function(e) {
							e.stop();
							// switch aid/hid over
							var aid = div.getElement('input[name=aid]');
							if(aid.value != null && aid.value != '' ) {
								// Can only switch if aid exists
								var switchReq = new MBB.req('switchhid.php',function(response){
									div.getElement('input[name=hid]').value = response.hid;
									aid.value = response.aid;
									e.target.set('text',response.hid);
									div.getElement('.aid').getFirst().set('text',response.aid);
									$('userpick').empty();
								});
								switchReq.get(Object.merge(params,{'hid':this.getElement('span').get('text')}));
							}
						});
						div.getElement('.aid').addEvent('click',function(e) {
							e.stop();
							var aid = div.getElement('input[name=aid]');
							if(aid.value != null && aid.value != '' ) {
								var open = div.getElement('input[name=open]');
								if (open.checked) {
									open.highlight('#F00');
								} else {
									var removeaidReq = new MBB.req('removeaid.php',function(response){
									// remove aid from match
										aid.value = null;
										e.target.set('text','---');
										$('T'+response.aid).removeClass('inmatch');
										$('userpick').empty();
									});
								}
								removeaidReq.get(Object.merge(params,{'hid':div.getElement('input[name=hid]').value}));
							}
						});
						div.getElement('.del').addEvent('click',function(e) {
						  e.stop(); 
							if(confirm('This will delete the match.  Are you sure?')) {
								var deleteReq = new MBB.req('deletematch.php',function(response) {
									div.dispose();
									$('T'+response.hid).removeClass('inmatch');
									var aid = $('T'+response.aid);
									if(aid) aid.removeClass('inmatch'); //only if not null
									$('userpick').empty();
								});
								deleteReq.get(Object.merge(params,{'hid':div.getElement('input[name=hid]').value}));
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
							if(new Date(dead.value *1000) > new Date() || confirm('Do you mean to set the question deadline before now')){
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
									if (! confirm('There are no open matches, are you sure you wish to open the round?')) {
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
												addaidReq.get(Object.merge(params,{
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
												if (!$('ou').checked)  match.getElement('input[name=cscore]').readOnly=true;
												setMatchEvents(match);
												team.getParent().addClass('inmatch');
											});
											matchPage.loadPage(Object.merge(params,{'hid':teamName}));
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
							tnicClicked = function(e) {
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
									var addAll = new MBB.req('addalltic.php', function (response) {
										var teams = response.teams;
										$('tnic').empty();
										var tic = $('tic').empty();
										teams.each(function(team,i) {	
											tic.adopt(makeTeam(team));
										});
									});
									addAll.get({'cid':params.cid});
								} else {
									$('lock_cell').highlight('#F00');
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
					this.adminpick = new MBB.subPage(this,'adminpick.php',$('userpick'),function(div) {
						if ($('pick')) { //We loaded the page and there is something there
							this.teams = new Object({});
							this.lastpick = new Object({});
							MBB.adjustDates(div);
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
									if(lastValue) delete that.teams[lastValue.value];
									delete that.lastpick[this.name];
									that.lastpick[this.name] = this;
								}
							});
							
							//These items are only there if user has registered
							$('pick').addEvent('submit', function(e) {
								e.stop();
								var answer = $('answer');
								if(answer) {
									//only here if answer is defined (no options to select (in which case Answer must be an integer
									if(!MBB.intValidate(answer)) {
										return false; //don't submit
									}
								}
						
								var pickReq = new MBB.req('createpicks.php', function(response) {
									$('userpick').empty();
								});
								pickReq.post($('pick'));
							});
						}
					});
					if(params.cid !=0) {
						MBB.adjustDates(div);
						$$('#registered input.bbapprove').addEvent('change',function(e) {
							e.stop();
							if(confirm('You are changing the approval status of a Baby Backup for this Competition. Are you sure you want to do this?')) {
								var updateBBa = new MBB.req('bbapprove.php',function(response) {
								});
								updateBBa.get(Object.merge(params,{'bbuid':this.name,'approval':this.checked}));
							} else {
								this.checked = !this.checked;
							}
						});
						div.getElements('.del').each(function (comp,i) {
							comp.addEvent('click', function(e) {
								e.stop();
								if(confirm('This will Un-Register this User from this Competition. Do you wish to Proceed?')) {
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
		if (this.me.admin) {
			Object.append(params,{'global':true});
		}
		this.competitions.loadPage(params);
		this.competition.loadPage(params);

	}
});
