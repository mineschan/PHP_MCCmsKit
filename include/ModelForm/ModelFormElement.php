<?

class ModelFormElement{

  var $type;
	var $field;
	var $label;
	var $position = "left";
	var $tableWidth = 100;
	
	var $rows = 5;
	var $default = "";
	var $required = false;
	
	var $trueLabel;
	var $falseLabel;
	
	var $dataSource;
	var $menuDefault;
	var $menuDefaultValue;
	var $displayUseSource = false;
	
	var $protectPattern;
	
	var $url;
	var $html;

	function ModelFormElement(){
			
	}
	
	function tableWidth($width){
 		$this->tableWidth = $width;
		return $this;
	}

	function rows($rows){
 		$this->rows = $rows;
		return $this;
	}
	
	function dataSource($dictionary){
		$this->dataSource = $dictionary;
		return $this;
	}

	function menuDefault($default,$value = ""){
		$this->menuDefault = $default;
		$this->menuDefaultValue = $value;
		return $this;
	}
	
	function displayUseSource(){
		$this->displayUseSource = true;
		return $this;
	}
	
	function setBooleanLabel($false,$true){
		$this->trueLabel = $true;
		$this->falseLabel = $false;
		return $this;
	}
	
	function toLeft(){
		$this->position = "left";
		return $this;
	}
	
	function toRight(){
		$this->position = "right";
		return $this;
	}

	function toBottom(){
		$this->position = "bottom";
		return $this;
	}
	
	function setPosition($position){
		$this->position = "right";
		return $this;	
	}	
	
	function setDefault($default){
		$this->default = $default;
		return $this;
	}
	
	function protectPattern($pattern){
		$this->protectPattern = $pattern;
		return $this;
	}
	
	function required($boolean = true){
		$this->required = $boolean;
		return $this;
	}
	
}
