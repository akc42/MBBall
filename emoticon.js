var Emoticon = new Class({
	Implements:[Options,Events],
	options: {
		url:'emoticon.php',	//url to get json of the emoticons
  		loading:'loading'	//class to show in container whilst we are loading the icons
	},
	initialize: function(container,outputDivs,options) {
		var that = this;
		this.setOptions(options);
		this.container = container;
		this.outputDivs = Array.slice(outputDivs);  //check to see if this turns a single Output in to an array of one which I cna iterate over
		this.currentFocus = this.outputDivs[0];
		this.outputDivs.each('focus',function(e) {
			that.currentFocus = this;
		});			
	   //Set up emoticons
		this.emoticonSubstitution = new Hash({});
		this.emoticonRegExpStr = null;
		// go get the emoticons
		container.addClass(that.options.loading);
		var req = new Request.JSON({
			url:this.options.url,
   			method:'get',
			onComplete: function(emoticons) {
				container.removeClass(that.options.loading);
				var regExpStr = ':('; //start to make an regular expression to find them (the all start with :)
				emoticons.each(function(emoticon,i) {
					if(i!=0) regExpStr += '|';
					regExpStr += key.replace(/\)/g,'\\)') ;  //regular expression is key except if has ) in it which we need to escape
					var icon = new Element('img',{'src':emoticon.src,'alt':emoticon.key,'title':emoticon.key});
					icon.addEvent('click' function(e) {
						e.stop();
						that.currentFocus.value += ':'+emoticon.key;
						that.currentFocus.focus();
					});
					icon.inject(container);
					that.emoticonSubstitution.include(emoticon.key,'<img src="'+emoticon.src+'" alt="'+emoticon.key+'" title="'+emoticon.key+'" />');
				});
				regExpStr += ')';
				that.emoticonRegExpStr = new RegExp(regExpStr, 'gm');
			}
		}).get();
	},
 	replaceEmoticons:function(text) {
		var that = this;
		return text.replace(emoticonRegExpStr,function(match,p1) {
			return that.emoticonSubstitution.get(p1);
		});
	}
});
