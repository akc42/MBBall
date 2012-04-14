// Calendar: a Javascript Module for Mootools that adds accessible and unobtrusive date pickers to your form input hidden elements 
// Derived from <http://electricprism.com/aeron/calendar> Calendar RC4, Copyright (c) 2007 Aeron Glemann <http://electricprism.com/aeron>, MIT Style License
//
/*	Copyright (c) 2008 Alan Chandler
*	see COPYING.txt in this directory for more details
*/

Calendar = function() {
	var calendarloaded = false;
	var calrequested = false;
	var url;
	var scripts = document.getElements('script');
	scripts.every(function(script) {
		var u = script.src.substr(script.src.length - 11);
		if (u === 'calendar.js') {
			url = script.src.substr(0, script.src.length - 2) + 'html';
			return false;
		}
		return true;
	});
	var calcopy = new Element('div');
	var calqueue = new Chain();
	var calendar = function(bind,callback) {
		var calling = callback.bind(bind);
		function doCallback() {
			calling(calcopy.clone(true,true));
		}
		if (calendarloaded) {
			calling(calcopy.clone(true,true));
			calqueue.callChain();
			return true;
		}
		if(!calrequested) {
			var req = new Request({
				url:url,
				onSuccess:function(html) {
					calcopy.set('html',html);
					calendarloaded = true;
					calqueue.callChain();
				},
				onFailure: function(xhr) {
				    var i = 0;
				}
			});
			req.get();
			calrequested = true;
		}
		calqueue.chain(doCallback);
		return false;
	}


	return {
		Single: new Class({
			Implements: [Events, Options],
			options: {
				classes: [],
				// ['calendar', 'prev', 'next', 'minute','hour','month', 'year', 'today', 'invalid', 'valid', 'inactive', 'active', 'hover', 'hilite']
				days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				// days of the week starting at sunday
				draggable: true,
				end: new Date(Date.UTC(2999, 11, 31)),
				// null maans current time
				format: 'jS M Y g:i a',
				months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				nodate: 'No Date Set',
				offset: 0,
				// first day of the week: 0 = sunday, 1 = monday, etc..
				onHideStart: Class.empty,
				onHideComplete: Class.empty,
				onShowStart: Class.empty,
				onShowComplete: Class.empty,
				onUpdate: Class.empty,
				start: new Date(Date.UTC(1000, 0, 1)),
				// null means current time
				titles:[],
				tweak: {
					x: 8,
					y: -4
				}, // tweak calendar positioning
				width:'195px'		//Correct width for formating, but if this is changed width will have to change.
	
			},

			initialize: function(input, options) {
				var keys;
				var values;
				var div;
				this.setOptions(options);
				//Basic validation
				if (typeOf(input) != 'element') return false;
				if (input.get('tag') != 'input') return false;
				if (input.type != 'hidden') return false;
				this.input = input;
				if (input.value === '') {
					this.input.value = '0';
				}
				// initialise where we are going to display the calender when it starts
				// create our classes array
				keys = ['calendar', 'picker', 'prev', 'next','nav', 'ap', 'minute', 'hour', 'month', 'year','unset', 'today', 'invalid', 'valid', 'inactive', 'active', 'hover', 'hilite'];

				values = keys.map(function(key, i) {
					if (this.options.classes[i]) {
						if (this.options.classes[i].length) {
							key = this.options.classes[i];
						}
					}
					return key;
				},this);
				this.options.classes = values.associate(keys);

				keys = ['Hr','Mi','am','pm','Unset'];
				values = keys.map(function (key,i) {
					if (this.options.titles[i]) {
						if(this.options.titles[i].length) {
							key = this.options.titles[i];
						}
					}
					return key;
				},this);
				this.options.titles = values.associate(keys);

				div = new Element('div', {'class': this.options.classes.calendar,'style' : 'width:'+this.options.width});
				div.wraps(input);
				this.button = new Element('button', {'type': 'button','class': this.options.classes.calendar}).inject(div);
				this.span = new Element('span', {'class':this.options.classes.calendar}).inject(div);
				this.visible = false;
				// create cal element with css styles required for proper cal functioning
				this.picker = new Element('div', {
					'styles': {
						left: '-1000px',
						opacity: 0,
						position: 'absolute',
						top: '-1000px',
						zIndex: 1000
					}
				}).addClass(this.options.classes.picker).inject(document.body);

				// iex 6 needs a transparent iframe underneath the calendar in order to not allow select elements to render through
				if (window.ie6) {
					this.iframe = new Element('iframe', {
						'styles': {
							left: '-1000px',
							position: 'absolute',
							top: '-1000px',
							zIndex: 999
						}
					}).injectInside(document.body);
					this.iframe.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)';
				}

				// initialize fade method
				this.fx = new Fx.Tween(this.picker, {
					onStart: function() {
						if (this.picker.getStyle('opacity') == 0) { // show
							var size;
							var coord;
							var x,y;
							size = window.getScrollSize();
							coord = this.button.getCoordinates();
							x = coord.right + this.options.tweak.x;
							y = coord.top + this.options.tweak.y;

							// make sure the calendar doesn't open off screen
							if (!this.picker.coord) this.picker.coord = this.picker.getCoordinates();

							if (x + this.picker.coord.width > size.x) x -= (x + this.picker.coord.width - size.x);
							if (y + this.picker.coord.height > size.y)  y -= (y + this.picker.coord.height - size.y);

							this.picker.setStyles({ left: x + 'px', top: y + 'px',display:'block' });

							if (window.ie6) {
								this.iframe.setStyles({ height: this.picker.coord.height + 'px', left: x + 'px', top: y + 'px', width: this.picker.coord.width + 'px',display:'block' });
							}
							this.fireEvent('showStart', this);
						} else { // hide
							this.fireEvent('hideStart', this);
						}
					}.bind(this),
					onComplete: function() {
						if (this.picker.getStyle('opacity') == 0) { // hidden
							this.picker.setStyles({ left: '-1000px', top: '-1000px',display:'none' });
							if (window.ie6) {
								this.iframe.setStyles({ height: this.picker.coord.height + 'px', left: '-1000px', top: '-1000px', width: this.picker.coord.width + 'px',display:'none' });
							}
							this.fireEvent('hideComplete', this);
						} else { // shown
							this.fireEvent('showComplete', this);
						}
					}.bind(this)
				});

				// initialize drag method
				if (this.options.draggable) {
					this.drag = new Drag.Move(this.picker, {
						onDrag: function() {
							if (window.ie6) {
								this.iframe.setStyles({
									left: this.calendar.style.left,
									top: this.calendar.style.top
								});
							}
						}.bind(this)
					});
				}
				
				this.val = false;
				if (this.input.value.toInt() > 0) {
					this.val = true;
				}
				// set start and end dates (which might adjust the input.value
				this.setStart(this.options.start);
				this.setEnd(this.options.end);
				this.resetVal();
				this.picker.empty();

				calendar(this,function(picker) {
					var table;
					var navs;
					var i;
					var firsthr,firstmi,secondhr,secondmi;
					var that = this;
					picker.inject(this.picker);
					this.button.addEvent('click',function(e) {
						e.stop();
						that.toggle();
					});


					// heading of the day columns
					table=picker.getElement('table');
					table.getElements('th').each(function(el,i) {
						var title = this.options.days[(i + this.options.offset) % 7];
						el.empty(); //clear out marker info
						el.appendText(title.substr(0,1));
						el.set('title',title);
						el.getNext();
					},this);

					//get all key elements and save them
					navs = picker.getElements('.'+this.options.classes.prev);
					navs.each(function(nav) {
					//see if it is a year navigation
						if(nav.hasClass(this.options.classes.nav)) {
							this.navprevyear = nav;
							nav.removeClass(this.options.classes.nav);
						} else {
							this.navprevmonth = nav;
						}
						nav.removeClass(this.options.classes.prev);
					},this);

					navs = picker.getElements('.'+this.options.classes.next);
					navs.each(function(nav) {
					//see if it is a year navigation
						if(nav.hasClass(this.options.classes.nav)) {
							this.navnextyear = nav;
							nav.removeClass(this.options.classes.nav);
						} else {
							this.navnextmonth = nav;
						}
						nav.removeClass(this.options.classes.next);
					},this);

					picker.getElement('.hour').empty().appendText(this.options.titles.Hr);
					picker.getElement('.minute').empty().appendText(this.options.titles.Mi);
					this.days = table.getElements('td'); //me need to set up the actual details dynamically
					this.am = picker.getElement('.am').empty().appendText(this.options.titles.am).removeClass('am');
					this.pm = picker.getElement('.pm').empty().appendText(this.options.titles.pm).removeClass('pm');
					//get key elements for hours and minutes
					firsthr = picker.getElement('.firsthr').removeClass('firsthr');
					firstmi = picker.getElement('.firstmi').removeClass('firstmi');
					secondhr = picker.getElement('.secondhr').removeClass('secondhr');
					secondmi = picker.getElement('.secondmi').removeClass('secondmi');
					this.hours = [];
					this.mins = [];
					for (i = 0 ; i<6 ; i++) {
						this.hours[i] = firsthr;
						this.hours[i+6] = secondhr;
						this.mins[i] = firstmi;
						this.mins[i+6] = secondmi;
						firsthr = firsthr.getNext();
						secondhr = secondhr.getNext();
						firstmi = firstmi.getNext();
						secondmi = secondmi.getNext();
					}

					picker.getElement('.unset').empty().appendText(this.options.titles.Unset).addEvent('click',function(e) {
						var offset = ((new Date(that.year, that.month, 1).getDay() - that.options.offset) + 7) % 7; // day of the week (offset)
						if(that.date >=0) {
							that.days[that.date+offset-1].removeClass(that.options.classes.active);
							that.date = -1;
						}
						if (that.hour>=0) {
							that.hours[that.hour%12].removeClass(that.options.classes.active);
							that.am.removeClass(that.options.classes.active);
							that.pm.removeClass(that.options.classes.active);
						}
						that.hour = -1;

						if (that.min>=0) {
							that.mins[that.min/5].removeClass(that.options.classes.active);
							that.min = -1;
						}
						that.checkVal();
						that.newDay();
					});
				});

			},
			resetVal: function() {
				var d = new Date();
				if (this.input.value.toInt() > 0) {
					d.setTime(this.input.value.toInt()*1000);
					this.date = d.getDate(); // 1 - 31
					this.hour = d.getHours(); // 0 - 23
					this.min = d.getMinutes(); //'0' - '59'
					this.min = this.min - this.min%5;  //round to nearest 5 minutes below
					// Also need to format this date in the span
					this.span.set('text', this.format(d));	
				} else {
					this.date = -1;
					if (d.getHours() < 12) {
						this.hour = -1;
					} else {
						this.hour = -2;
					}
					this.min = -1;
					this.span.set('text',this.options.nodate);
				}
				this.month = d.getMonth(); // 0 - 11
				this.year = d.getFullYear(); // 19xx - 20xx				
			},
			setVal: function() {
				if (this.val) {
					var d = new Date(this.year,this.month,this.date,this.hour,this.min)
					if (d < this.getStart()) {
						d=this.getStart();
					} else {
						if (d > this.getEnd()) {
							d = this.getEnd();
						}
					}
					this.input.value = d.getTime() / 1000;
					this.span.set('text', this.format(d));
				} else {
					this.input.value = 0;
					this.span.set('text', this.options.nodate);
				}
				this.fireEvent('update');
			},
			checkVal: function() {
				if (this.val) {
					if(this.date < 0 || this.hour <0 || this.min <0) {
						this.val = false;
					}
					this.setVal();
				} else {
					if (this.date >= 0 && this.hour >= 0 && this.min >=0) {
						this.val = true;
						this.setVal();
					}
				}
			},
			getStart: function() {
				return (this.start) ? this.start: new Date();
			},
			setStart: function(start) {
				var d;
				this.start = (start) ? ((this.options.start) ? (
						(start > this.options.start) ? start: this.options.start) : (start > new Date()) ? start: new Date())
						: ((this.options.start) ? ((this.options.start > new Date()) ? this.options.start: null) : null);
				if (this.input.value.toInt() != 0 && this.input.value.toInt() < this.getStart().getTime() / 1000) {
					this.min = -1; //invalidate it but leave date the same
					this.val = false;
				}
			},
			getEnd: function() {
				return (this.end) ? this.end: new Date();
			},
			setEnd: function(end) {
				var d;
				this.end = (end) ? ((this.options.end) ? (
						(end < this.options.end) ? end: this.options.end) :(end < new Date()) ? end: new Date())
						: ((this.options.end) ? ((this.options.end < new Date()) ? this.options.end: null) : null);
				if (this.input.value.toInt() != 0 && this.input.value.toInt() > this.getEnd().getTime() / 1000) {
					this.min = -1; //invalidate it.
					this.val = false;
				}
			},
			toggle: function() {
				if (this.visible) {
					document.removeEvent('mousedown', this.hide); // always remove the current mousedown script first
					this.drag.detach(); //hoping this will prevent drag holding on 
					this.fx.start('opacity', 1, 0);
					this.visible = false;
				} else {
					document.removeEvent('mousedown', this.hide); // always remove the current mousedown script first
					var that = this;
					this.hide = function(e) {
						var el = e.target;
						
						while (el !== document.body && el.nodeType === 1) {
							if (el === that.picker || el === that.button ) {
								e.stop;
								return false;
							}
							el = el.getParent();
						}
						that.toggle();
						return true;
					};
					this.drag.attach();
					document.addEvent('mousedown', this.hide);

					this.newDay();

					this.newMorY();
					this.fx.start('opacity', 0, 1);
					this.visible = true;
				}
			},
			newMorY: function() {
				var offset;
				var i;
				var dates,datee;
				var td;
				var today,d;
				var that = this;
				//put the class of the month (allows different months pictures to be done via css
				this.picker.addClass(this.options.months[this.month].toLowerCase());
								//Now add the navigation
				this.picker.getElement('.month').empty().appendText(this.options.months[this.month]);
				this.picker.getElement('.year').empty().appendText(this.year);
					//see if it is a year navigation
				if(this.navprevyear) {
					this.navprevyear.removeEvents().removeClass(this.options.classes.prev);
					if(this.year !== this.getStart().getFullYear()) {
						this.navprevyear.addClass(this.options.classes.prev).addEvent('click',function(e) {
							e.stop();
							that.year--;
							that.setVal();
							that.newMorY();
						});
					}
				}
				if(this.navnextyear) {
					this.navnextyear.removeEvents().removeClass(this.options.classes.next);
					if(this.year !== this.getEnd().getFullYear()) {
						this.navnextyear.addClass(this.options.classes.next).addEvent('click',function(e) {
							e.stop();
							that.year++;
							that.setVal();
							that.newMorY();
						});
					}
				}

				this.navprevmonth.removeEvents().removeClass(this.options.classes.prev);
				if (this.year !== this.getStart().getFullYear() || this.month !== this.getStart().getMonth()) {
					this.navprevmonth.addEvent('click',function(e) {
						e.stop();
						that.picker.removeClass(that.options.months[that.month].toLowerCase());
						that.month--;
						if(that.month < 0) {
							that.month=11;
							that.year--;
						}
						that.setVal();
						that.newMorY();
					}).addClass(this.options.classes.prev);
				}
				
				this.navnextmonth.removeEvents().removeClass(this.options.classes.next);
				if (this.year !== this.getEnd().getFullYear() || this.month !== this.getEnd().getMonth()) {
					this.navnextmonth.addEvent('click',function(e) {
						e.stop();
						that.picker.removeClass(that.options.months[that.month].toLowerCase());
						that.month++
						if(that.month > 11) {
							that.month=0;
							that.year++;
						}
						that.setVal();
						that.newMorY();
					}).addClass(this.options.classes.next);
				}

				offset = ((new Date(this.year, this.month, 1).getDay() - this.options.offset) + 7) % 7; // day of the week (offset)
				d= new Date();
				if (this.year == d.getFullYear() && this.month == d.getMonth()) {
					today = d.getDate() -1 + offset;
				}
				for ( i = 0; i < 42; i++) { // 1 to 42 (6 x 7 or 6 weeks)
					dates = new Date(this.year,this.month,i-offset+1);
					datee = new Date(dates);
					dates.setHours(0,0,0,0);
					datee.setHours(23,59,59,999);
					td = this.days[i]
					td.empty().appendText(dates.getDate()).removeEvents()
							.removeClass(this.options.classes.valid)
							.removeClass(this.options.classes.invalid)
							.removeClass(this.options.classes.active)
							.removeClass(this.options.classes.today);
					if(this.getStart()>datee) {
						td.addClass(this.options.classes.invalid);
					} else {
						if (this.getEnd() < dates) {
							td.addClass(this.options.classes.invalid);
						} else {
							if(dates.getMonth() === this.month) {
								//CSS should make the most important of these stand out
								td.addClass(this.options.classes.valid);
								if (today === i) td.addClass(this.options.classes.today);
								td.store('i',i);
								if (this.date === i-offset+1) td.addClass(this.options.classes.active);
								td.addEvent('click', function(e) {
									var i;
									e.stop();
									i = this.retrieve('i');
									if (that.date>=0) that.days[that.date-1+offset].removeClass(that.options.classes.active);
									that.date = i-offset+1;
									this.addClass(that.options.classes.active);
									that.checkVal();
								});
							}
						}
					}
				}
			},
			newDay: function() {
				var that = this;
				this.am.removeEvents()
						.removeClass(this.options.classes.active)
						.removeClass(this.options.classes.valid)
						.removeClass(this.options.classes.invalid);
				if (this.date>=0) {
					var dates = new Date(this.year,this.month,this.date);
					var datee = new Date(dates);
					dates.setHours(0,0,0,0); //first moment of am
					datee.setHours(11,59,59,999); //last moment of am
				}
				if (this.date<0 || this.hour<0 || (datee > this.getStart() && dates < this.getEnd())) {
					this.am.addEvent('click',function(e) {
						e.stop();
						if(that.hour >=12) that.hour -=12;
						if(that.hour === -2) that.hour = -1;
						that.pm.removeClass(that.options.classes.active);
						that.am.addClass(that.options.classes.active);
						that.checkVal();
						that.newDay();
					}).addClass(this.options.classes.valid);
					if (this.hour < 12 && this.hour > -2) {
						this.am.addClass(this.options.classes.active);
					}
				} else {
					this.am.addClass(this.options.classes.invalid);
				}

				this.pm.removeEvents()
						.removeClass(this.options.classes.active)
						.removeClass(this.options.classes.valid)
						.removeClass(this.options.classes.invalid);
				if (this.date>=0) {
					dates.setHours(12,0,0,0);
					datee.setHours(23,59,59,999);
				}
				if(this.date<0 ||  this.hour<0 || (datee > this.getStart() && dates < this.getEnd())) {
					this.pm.addEvent('click',function(e) {
						e.stop();
						if (that.hour == -1 ) that.hour = -2;
						if(that.hour<12 && that.hour >=0 ) that.hour +=12;
						that.am.removeClass(that.options.classes.active);
						that.pm.addClass(this.options.classes.active)
						that.checkVal();
						that.newDay();
					}).addClass(this.options.classes.valid);
					if(this.hour >=12 || this.hour === -2) {
						this.pm.addClass(this.options.classes.active);
					}
				} else {
					this.pm.addClass(this.options.classes.invalid);
				}
					

				for (i = 0; i<12 ; i++) {
					var mi = i*5;
					this.hours[i].removeEvents()
						.removeClass(this.options.classes.active)
						.removeClass(this.options.classes.valid)
						.removeClass(this.options.classes.invalid);
					this.mins[i].removeEvents()
						.removeClass(this.options.classes.active)
						.removeClass(this.options.classes.valid)
						.removeClass(this.options.classes.invalid);
					if (this.date>=0) {
						if(this.hour >=12) {
							dates.setHours(i+12,0,0,0);
							datee.setHours(i+12,59,59,999);
						} else {
							dates.setHours(i,0,0,0);
							datee.setHours(i,59,59,999);
						}
					}
					if(this.date<0 || (datee > this.getStart() && dates < this.getEnd())) {
						this.hours[i].store('i',i).addClass(this.options.classes.valid).addEvent('click',function(e) {
							e.stop();
							var i = this.retrieve('i');
							if(that.hour>=0) {
								that.hours[that.hour%12].removeClass(that.options.classes.active);
								if(that.hour >= 12) {
									that.hour = i+12;
								} else {
									that.hour = i;
								}
							} else {
								if (that.hour === -1 ) {
									that.hour = i;
								} else {
									that.hour = i+12;
								}
							}
							this.addClass(that.options.classes.active);
							that.checkVal();
							that.newDay();
						});
						if(this.hour >= 0 && this.hour%12 === i) this.hours[i].addClass(this.options.classes.active);
					} else {
						this.hours[i].addClass(this.options.classes.invalid);
					}
					if (this.date>=0 && this.hour>=0) {
						dates.setHours(this.hour,mi,0,0);
						datee.setHours(this.hour,mi,59,999);
					}
					if(this.date<0 || this.hour<0 || datee > this.getStart() && dates < this.getEnd()) {
						this.mins[i].store('i',i).addClass(this.options.classes.valid).addEvent('click',function(e) {
							var i = this.retrieve('i');
							e.stop();
							if(that.min >= 0) that.mins[(that.min/5)%12].removeClass(that.options.classes.active);
							that.min = i*5;
							this.addClass(that.options.classes.active);
							that.checkVal();
							return false;
						});
						if (this.min === mi ) this.mins[i].addClass(this.options.classes.active);
					} else {
						this.mins[i].addClass(this.options.classes.invalid);
					}
				}
			},
			format: function(date) {
				var str = '';

				if (date) {
					var j = date.getDate(); // 1 - 31
					var w = date.getDay(); // 0 - 6
					var l = this.options.days[w]; // Sunday - Saturday
					var n = date.getMonth() + 1; // 1 - 12
					var f = this.options.months[n - 1]; // January - December
					var y = date.getFullYear() + ''; // 19xx - 20xx
					var h = date.getHours(); // 0 - 23
					var G = h + ''; // h as string
					var g = (h == 0) ? 12 : ((h > 12) ? h - 12 : h) + ''; // '1' to '12'
					var i = date.getMinutes() + ''; //'0' - '59'
					i = (i.length == 1) ? '0' + i: i;
					var format = this.options.format;
					var loop;

					for (loop = 0,
					len = format.length; loop < len; loop++) {
						var cha = format.charAt(loop); // format char
						switch (cha) {
							// year cases
						case 'y':
							// xx - xx
							y = y.substr(2);
						case 'Y':
							// 19xx - 20xx
							str += y;
							break;

							// month cases
						case 'm':
							// 01 - 12
							if (n < 10) {
								n = '0' + n;
							}
						case 'n':
							// 1 - 12
							str += n;
							break;
						case 'M':
							// Jan - Dec
							f = f.substr(0, 3);
						case 'F':
							// January - December
							str += f;
							break;

							// day cases
						case 'd':
							// 01 - 31
							if (j < 10) {
								j = '0' + j;
							}
						case 'j':
							// 1 - 31
							str += j;
							break;

						case 'D':
							// Sun - Sat
							l = l.substr(0, 3);
						case 'l':
							// Sunday - Saturday
							str += l;
							break;

						case 'N':
							// 1 - 7
							w += 1;
						case 'w':
							// 0 - 6
							str += w;
							break;

						case 'S':
							// st, nd, rd or th (works well with j)
							if (j % 10 == 1 && j != '11') {
								str += 'st';
							} else if (j % 10 == 2 && j != '12') {
								str += 'nd';
							} else if (j % 10 == 3 && j != '13') {
								str += 'rd';
							} else {
								str += 'th';
							}
							break;

						case 'a':
							str += (h < 12) ? 'am': 'pm';
							break;
							i
						case 'A':
							str += (h < 12) ? 'AM': 'PM';
							break;

						case 'g':
							str += g;
							break;
						case 'h':
							str += (g.length == 1) ? '0' + g: g;
							break;
						case 'G':
							str += G;
							break;
						case 'H':
							str += (G.length == 1) ? '0' + G: G;
							break;
						case 'i':
							str += i;
							break;
						default:
							str += cha;
						}
					}
				}
			return str; //  return format with values replaced
			}
		}),
		Multiple: new Class({
			Implements: [Events, Options],
			options: {
				pad: 1440,
				//minutes gap between calendars - one day is default - 0 means no contraints
				onHideStart: Class.empty,
				onHideComplete: Class.empty,
				onShowStart: Class.empty,
				onShowComplete: Class.empty,
				onUpdate: Class.empty
			},
			initialize: function(input, options) {
				this.setOptions(options);
				this.calendars = [];
				var newOptions = Object.append({},options);
				newOptions = Object.append(newOptions,{onUpdate:this.update.bind(this)});
				input.each(function(item, i) {
					this.calendars.push(new Calendar.Single(item,newOptions));
				},this);
				this.calendars.sort(function(a, b) {
					return a.input.value.toInt() - b.input.value.toInt();
				});
				this.check();//check constraints are met
			},
			check: function() {
				// We check all the other calendar contraints again
				// so that they do not overlap this by the options
				if (this.options.pad != 0) {
					this.calendars.each(function(cal, i) {
						var d;
						var v;
						if (i != 0) {
							var prev = this.calendars[i - 1];
							if (prev.input.value.toInt() != 0) {
								if (cal.input.value.toInt() != 0) {
									d=new Date();
									v =(cal.input.value.toInt() - this.options.pad * 60);
									d.setTime(v * 1000);
									prev.setEnd(d);
									if(prev.visible) prev.newMorY();
								}
								d = new Date();
								v = (prev.input.value.toInt() + this.options.pad * 60);
								d.setTime( v * 1000);
								cal.setStart(d);
								if(cal.visible) cal.newMorY();
							} else {
								if (cal.input.value.toInt() != 0) {
									d=new Date();
									v =(cal.input.value.toInt() - this.options.pad * 60);
									d.setTime(v * 1000);
									prev.setEnd(d);
									if(prev.visible) prev.newMorY()
								}
							}
						}
					},this);
				}
			},
			update:function () {
				this.check(); //check constraints and then tell our clients (if they want to know)
				this.fireEvent('update');
			}
		})
	};
} ();
