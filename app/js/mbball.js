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
    var errorDiv;
    return {
        setErrorDiv: function(div) {
            errorDiv = div;
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
	            this.request.get(params);
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
	            this.req.get(params);
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
	initialize: function(errordiv) {
		MBB.setErrorDiv (errordiv);
	}
});

