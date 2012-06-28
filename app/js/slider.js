/*	Copyright (c) 2012 Alan Chandler
*	see README.txt in this directory for more details
*/
var Slider = new Class({
  Implements:[Events,Options],

  Binds:['doDrag','sliderClick','sliderScroll'],
  
  options: {
    minstep:0,
    maxstep:100,
    initial:0,
    minortick:0,
    minorclass:'minortick',
    majortick:0,
    majorclass:'majortick',
    wheel:false,
    mode:'horizontal'
  },

  initialize: function(slider,knob,options) {
    this.setOptions(options);
    this.slider = document.id(slider);
    this.knob = document.id(knob);

    var sliderSize = this.slider.getSize();
    var knobSize = this.knob.getSize();
    this.steps = this.options.maxstep - this.options.minstep;
    this.previousStep = this.options.initial;
    var limit = {x:[0,0],y:[0,0]};
    var modifiers = {x:false,y:false};
    switch (this.options.mode) {
      case 'vertical':
	this.axis = 'y';
	this.property = 'bottom';
	this.pixelsPerStep = Math.floor((sliderSize.y - knobSize.y)/this.steps)
	this.offset = Math.round((sliderSize.y - knobSize.y - (this.pixelsPerStep*this.steps))/2)
	limit.y = [this.offset,this.offset+(this.pixelsPerStep*this.steps)];
	modifiers.y = this.property;
	this.halfKnob = Math.round(knobSize.y/2);
      break;
      case 'horizontal':
	this.axis = 'x';
	this.property = 'left';
	this.pixelsPerStep = Math.floor((sliderSize.x - knobSize.x)/this.steps)
	this.offset = Math.round((sliderSize.x - knobSize.x - (this.pixelsPerStep*this.steps))/2)
	limit.x = [this.offset,this.offset+(this.pixelsPerStep*this.steps)];
	modifiers.x = this.property;
	this.halfKnob = Math.round(knobSize.x/2);
      break;
    }

    //Set initial position of knob
    if(this.slider.getStyle('position') != 'relative') this.slider.setStyle('position','relative');
    if(this.knob.getStyle('position') != 'absolute') this.knob.setStyle('position','absolute');
    this.knob.setStyle(this.property,this.offset+(this.options.initial-this.options.minstep)*this.pixelsPerStep);

    //put ticks in place if there are any
    if(this.options.minortick != 0 || this.options.majortick != 0) {
      var tick;
      var position;
      for(i = this.options.minstep; i <= this.options.maxstep; i++) {
	position = this.offset+(i-this.options.minstep)*this.pixelsPerStep+this.halfKnob
	if(this.options.majortick !=0 && i % this.options.majortick == 0 ) {
	  tick = new Element('div',{'class':this.options.majorclass,'position':'absolute'});
	  tick.setStyle(this.property,position);
	  tick.inject(this.slider,'top');
	}
	else if (this.options.minortick != 0 && i % this.options.minortick == 0) {
	  tick = new Element('div',{'class':this.options.minorclass,'position':'absolute'});
	  tick.setStyle(this.property,position);
	  tick.inject(this.slider,'top');
	}
      }
    }
    
    
    this.drag = new Drag(this.knob,{
      grid:this.pixelsPerStep,
      limit:limit,
      invert:(this.options.mode == 'vertical'),
      modifiers:modifiers,
      snap:Math.floor(this.pixelsPerStep/2),
      onBeforeStart:(function() {
	this.isDragging = true;
      }).bind(this),
      onDrag:this.doDrag,
      onComplete:(function() {
	this.doDrag();
	this.isDragging = false;
	if(this.step != this.previousStep) this.fireEvent('change',this.step);
	this.previousStep = this.step;
      }).bind(this),
      onCancel: (function() {
	this.isDragging = false;
      }).bind(this),
      onStart:this.doDrag
    });
    
    this.attach();
  },
  
  attach:function() {
    this.slider.addEvent('mousedown',this.sliderClick);
    if(this.options.wheel) this.slider.addEvent('mousewheel',this.sliderScroll);
    this.drag.attach();
    return this;
  },
  detach:function() {
    this.slider.removeEvent('mousedown',this.sliderClick).removeEvent('mouswheel',this.sliderScroll);
    this.drag.detach();
    return this;
  },
  getStep:function(position) {
    var step;
    if (position < this.offset) {
      step = this.options.minstep;
    } else if (position > this.offset+(this.steps*this.pixelsPerStep)) {
      step = this.options.maxstep;
    } else {
      step = Math.round((position - this.offset)/this.pixelsPerStep) + this.options.minstep;
    }
   return step
  },
  sliderClick:function(event) {
    var position;
    if(this.isDragging || event.target == this.knob) return;
    if (this.axis == 'y') 
      position = this.slider.getSize().y - (event.page.y -this.slider.getPosition().y) - this.halfKnob; //appear to have clicked half a knob backwards
    else
      position = event.page.x - this.slider.getPosition().x-this.halfKnob; //appear to have clicked half a knob backwards
    var step = this.getStep(position);
    this.knob.setStyle(this.property,this.offset + (step-this.options.minstep)*this.pixelsPerStep);
    this.fireEvent('tick',step);
    if (this.previousStep != step) this.fireEvent('change',step);
    this.previousStep = step;
  },
  sliderScroll:function(event) {
    if(this.isDragging) return;
    var previousStep = this.previousStep
    if (event.wheel < 0) {
      if (this.previousStep > this.options.minstep) {
	this.previousStep--;
	this.knob.setStyle(this.property,this.offset + (this.previousStep-this.options.minstep)*this.pixelsPerStep);
      }
    } else {
      if(this.previousStep < this.options.maxstep) {
	this.previousStep++;
	this.knob.setStyle(this.property,this.offset + (this.previousStep-this.options.minstep)*this.pixelsPerStep);
	this.fireEvent('tick',this.previousStep).fireEvent('change',this.previousStep);
      }
    }
    this.fireEvent('tick',this.previousStep);
    if(previousStep != this.previousStep) this.fireEvent('change',this.previousStep);

  },
  doDrag:function () {
    var position;
    if (this.axis == 'y') 
      position = this.slider.getSize().y - this.knob.getPosition(this.slider).y;
    else 
      position = this.knob.getPosition(this.slider).x;
    this.step = this.getStep(position);
    
    this.fireEvent('tick',this.step);
  }
});
  
  
    
    
    
    