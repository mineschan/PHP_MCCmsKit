/*
 * MCAwesomeAlert: a jQuery plugin, version: 0.1 (2012-05-10)
 * @requires jQuery v1.3 or later
 *
 * MCAwesomeAlert is a javascript class using jquery to show awesome banner message
 * from the top of browser. Easy to use and easy to customize.
 *
 * Licensed under the MIT:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2012, MineS Chan (mineschan@gmail.com)
 */
var MCAwesomeAlert = {

  /******************************************************************************
	******* Private Settings, DO NOT change it if you don't know what are you doing
	******************************************************************************/
	$this:"",
	container :"",
	containerID :"MCAwesomeAlert",
	message :"No Message",
	closeTimer:"",
	customColor:"",
	
	/******************************************************************************
	******* Customize -- Params you may like to config
	******************************************************************************/
	hideDelay:3000,
	showDelay:200,
	boxHeight:45,

	//color
	colorAlert: "#8E1609",
	colorSuccess: "#2d9725",
	colorWarning: "#ffd322",
	
	
	/******************************************************************************
	******* Private Functions
	******************************************************************************/	
	init:function(){
		$this = this;
		
		if($("#"+$this.containerID).length == 0){
			$this.container = $("<div>").attr("id",$this.containerID).appendTo("body");
			$("<span>").appendTo($this.container);
			$("<div>").appendTo($this.container).addClass("btnClose").append($("<a>")).text("X").click($this.close);
		}else{
			clearTimeout($this.closeTimer);
		}
		
	},
	
	show:function(color){
		
		this.init();
		
		//tune with params
		if(color) $($this.container).css("background-color",color);
		$("span",$this.container).text($this.message);
		$($this.container).height($this.boxHeight);
		var spanHeight = parseInt($("span",$this.container).outerHeight());
		$("span",$this.container).css("top",(($this.boxHeight-spanHeight)/2) + "px");
		
		if($this.boxHeight < 100){
			$(".btnClose",$this.container).css("top","50%").css("margin-top","-" +((($(".btnClose",$this.container).outerHeight()/2))/2) + "px");
		}
		//ready,get set,show
		//$("#MCAwesomeAlert").show();
		$this.container.css("top","-" + ($($this.container).outerHeight() + 10)  + "px"); //we add 10px for hiding the shadow
		var topPad = parseInt($($this.container).css("padding-top"));
		//$($this.container).delay($this.showDelay).animate({"top":(0-topPad) +"px"},800,"easeOutBounce");
		//$($this.container).animate({"top":(0-topPad) +"px"},800,"easeOutBounce");
		$($this.container).animate({"top":(0-topPad) +"px"},800);
		


		//close and auto close
		$($this.container).click($this.close);
		$this.closeTimer = setTimeout($this.close,($this.hideDelay));
	},
	 
	
	/******************************************************************************
	******* Functions you should use to call
	******************************************************************************/		
	showWithMessageAndColor:function(aMessage,aColor){
		this.message = aMessage;
		this.customColor = aColor;
		this.show(aColor);
	},
	
	alert:function(aMessage){
		this.showWithMessageAndColor(aMessage,this.colorAlert);
	},	
	
	success:function(aMessage){
		this.showWithMessageAndColor(aMessage,this.colorSuccess);
	},
	
	warning:function(aMessage){
		this.showWithMessageAndColor(aMessage,this.colorWarning);
	},
	
	
	close:function(){
		clearTimeout($this.closeTimer);
		$($this.container).animate({"top":"-" + ($($this.container).outerHeight() + 10) +"px"},700);
	}
	

}
