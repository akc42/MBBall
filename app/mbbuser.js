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
			this.teams = $H({});
			this.lastpick = $H({});
			var picks = $$('.ppick')
			var that =this;
			// We make a hash of every checked item - which we can then use when an item changes to
			// check that the new item isn't already picked, and if so set it back
			picks.each(function(item) {
				if(item.checked) {
					that.teams.set(item.value,item);
					that.lastpick.set(item.name,item);
				}
			});
			picks.addEvent('change',function(e) {
				e.stop();
				var lastValue = that.lastpick.get(this.name);
				if(that.teams.has(this.value)) {
					//this team already has a selection, so lets find out what
					var existingSelection = that.teams.get(this.value);
					existingSelection.getParent().highlight('#F00');
					// now change it back
					this.checked = false;
					if(lastValue) lastValue.checked = true;
				} else {
					// This team did not have a selection before, so now set one
					// and take out old values;
					that.teams.set(this.value,this);
					if(lastValue) that.teams.erase(lastValue.value);
					that.lastpick.erase(this.name);
					that.lastpick.set(this.name,this);
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
	},
	checkReg: function() {
	}
});

