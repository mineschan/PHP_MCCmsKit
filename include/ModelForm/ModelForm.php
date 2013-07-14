<?

include_once("ModelFormElement.php");


class ModelForm {

	var $model;
	var $sectionName;
	var $sectionKey;
	var $dataFields;
	var $formURL;
	var $showEditButton = true;
	var $showInsertButton = true;
	var $showSortButton = false;
	var $sortTableParam;

	function ModelForm($sectionName,$sectionKey){
		$this->sectionName = $sectionName;
		$this->sectionKey = $sectionKey;
		
		$this->dataFields = array();
		
	}
	
	function setSortParam($order){
		$this->sortTableParam = $order;
		return $this;
	}

	function addDataField($type,$fieldName,$label){
		
	
		$elementObj = new ModelFormElement();
		$elementObj->type = $type;
		$elementObj->field = $fieldName;
		$elementObj->label = $label;
		$elementObj->position = $position;
		$elementObj->tableWidth = $tableWidth;
		$elementObj->default = $default;
		
		array_push($this->dataFields,$elementObj);
		return $elementObj;
	}
	
	function addFormSectionTitle($title){
	
		$elementObj = new ModelFormElement();
		$elementObj->type = "sectionTitle";
		$elementObj->title = $title;
		$elementObj->position = $position;
		
		array_push($this->dataFields,$elementObj);
		return $elementObj;
	}
	
	function addImage($url,$maxWidth,$maxHeight){
		
		$elementObj = new ModelFormElement();
		$elementObj->type = "image";
		$elementObj->url = $url;

		$elementObj->position = $position;		
		$elementObj->maxW = $maxWidth;
		$elementObj->maxH = $maxHeight;

		array_push($this->dataFields,$elementObj);		
		return $elementObj;
	}
	
	function addHTML($html){
		
		$elementObj = new ModelFormElement();
		$elementObj->type = "html";
		$elementObj->html = $html;
		$elementObj->position = $position;		

		array_push($this->dataFields,$elementObj);		
		return $elementObj;
	}
		
	function filterTableColumn($fields){
		$fieldArray = split(",",$fields);
		$columns = array();
	
		
		foreach($fieldArray as $field){
			
			foreach($this->dataFields as $dataField){
			
				if($dataField->field == $field){
					array_push($columns,$dataField);
					break;
				}
			}		
		
		}
		
		return $columns;
	}
	
	
	function showListUsingObjectAndFields($objects,$fields){
		
		$showColumns = $this->filterTableColumn($fields);	
				
		$html = file_get_contents("formLayout/list.html",FILE_USE_INCLUDE_PATH);
				
		//table options
		if($this->showSortButton)
			$tmp_options.= '<a class="awesome" href="'.$this->formURL.'?action=sort">排序'.$this->sectionName.'</a>';
		if($this->showInsertButton)
			$tmp_options.= ' <a class="awesome" href="'.$this->formURL.'?action=insert">新增'.$this->sectionName.'</a>';
		
		//variable
		$dataTotal = $this->model->numRows($objects);
		
		//
		//header 
	    foreach($showColumns as $showColumn){
			$tmp_columns.= '<th width="'.$showColumn->tableWidth.'">'.$showColumn->label.'</th>';
		}
		
		if($this->showEditButton)
			$tmp_columns .= '<td width="50"></td>';
		
		
		//content
		
		if(!is_array($objects)){
		
			$tmp_objects = array();
			while($object = mysql_fetch_array($objects)){
				array_push($tmp_objects,$object);
			}
			$objects = $tmp_objects;
		}
		
		foreach($objects as $object){
		
			$tmp_content.='<tr>';
			
			foreach($showColumns as $showColumn){
			
					if($showColumn->type=="boolean"){
						$rowValue = ($object[$showColumn->field])? $showColumn->trueLabel:$showColumn->falseLabel;
					
					}else if($showColumn->displayUseSource){
					
						$rowValue = $showColumn->dataSource[$object[$showColumn->field]];
					
					}else if($showColumn->protectPattern != ""){
					
						$rowValue = str_repeat($showColumn->protectPattern, strlen($object[$showColumn->field]));
						
					}else{
						$rowValue = $object[$showColumn->field];
					}

					$tmp_content .= '<td align="center">'.$rowValue.'</td>';
			}
		
			if($this->showEditButton){
			
				$tmp_content.='<td align="center">
							<a href="'.$this->formURL.'?action=update&id='.$object[$this->model->qId].'">編輯</a>
						</td>';
			}				
			$tmp_content.='</tr>';			
		}		
		$html = str_replace("{dataTotal}",$dataTotal,$html);
		$html = str_replace("{sectionName}",$this->sectionName,$html);
		
		$html = str_replace("{table_option}",$tmp_options,$html);
		$html = str_replace("{table_header}",$tmp_columns,$html);
		$html = str_replace("{table_content}",$tmp_content,$html);
		
		$html = str_replace("{sortTableParm}",$this->sortTableParam?$this->sortTableParam:"[0,0]",$html);
		echo $html;
	}

	
	function showForm($object){
	
		$subject = (($object)?"編輯":"新增").$this->sectionName;
		$action =  (($object)?"doUpdate":"doInsert");
		$formAction = $this->formURL.'?action='.$action;
		
		$requiredArray = array();
	
		foreach($this->dataFields as $dataField){
			$value = ($dataField->field)? $object->{$dataField->field} : NULL; 
		
			if($dataField->position == "left"){
				$tmp_left .= $this->genFormElement($dataField,$value);
			}else if($dataField->position == "right"){
				$tmp_right .= $this->genFormElement($dataField,$value);
			}else if($dataField->position == "bottom"){
				$tmp_bottom .= $this->genFormElement($dataField,$value);
			}
			
			if($dataField->required){
				array_push($requiredArray,"'".$dataField->field."'");
			}
		}
			
		$html = file_get_contents("formLayout/form1.html",FILE_USE_INCLUDE_PATH);
		$html = str_replace("{formSubject}",$subject,$html);
		$html = str_replace("{formAction}",$formAction,$html);
		$html = str_replace("{section_left}",$tmp_left,$html);
		$html = str_replace("{section_right}",$tmp_right,$html);
		$html = str_replace("{section_bottom}",$tmp_bottom,$html);
		
		
		//required script
		$required = implode(",",$requiredArray);
		$html = str_replace("{required_field}",$required,$html);
		
		
			
		echo $html;	
	}

	function genFormElement($dataField,$data = null){
	
		$element = "";
		$label = ($dataField->required)? "<span style='color:red'>*</span>".$dataField->label : $dataField->label;
		
	
		switch($dataField->type){
		
			//input = text
			case "text":
				$element .= '<span class="label">'.$label.'</span><input type="text" class="input1" name="'.$dataField->field.'" id="'.$dataField->field.'" value="'.$data.'" /><br>';
			break;
			
			//input = text
			case "password":
				$element .= '<span class="label">'.$label.'</span><input type="password" class="input1" name="'.$dataField->field.'" id="'.$dataField->field.'" value="'.$data.'" /><br>';
			break;			

			//input = date : use of query date picker
			case "date":
				$element .= '<span class="label">'.$label.'</span><input type="text" class="input1" name="'.$dataField->field.'" id="'.$dataField->field.'" value="'.$data.'" /><br>
				<script>
					$(function(){
						$("#'.$dataField->field.'").datepicker({dateFormat:\'yy-mm-dd\'});
					})
				</script>
				';
			break;	

			//input = textarea
			case "textarea":
				$element .= '<span class="label">'.$label.'</span><br><textarea class="textarea1 short" name="'.$dataField->field.'" id="'.$dataField->field.'" rows="'.$dataField->rows.'"/>'.$data.'</textarea><br>';
			break;
			
			// select menu
			case "select":
				$dbValue = ($data != NULL)?$data:$dataField->default;
				
				$element.='<span class="label">'.$label.'</span><select name="'.$dataField->field.'" id="'.$dataField->field.'">';
				
				if($dataField->menuDefault)
				    $element .= '<option value="'.$dataField->menuDefaultValue.'">'.$dataField->menuDefault.'</option>';
				    				
				foreach($dataField->dataSource as $key=>$value){
					$checked = ($dbValue == $key)?"selected":"";		
					$element .= '<option value="'.$key.'" '.$checked.'>'.$value.'</option>';
				}
				
				$element .= '</select><br>';
				
			break;			
			
			
			//input = checkbox
			case "boolean":
				$value = ($data != NULL)?$data:$dataField->default;
				$checked = ($value)?"checked":"";		
				$element .= '<span class="label">'.$label.'</span><input type="checkbox" name="'.$dataField->field.'" '.$checked.'><br>';
			break;
			
			//input = file
			case "file":
				$element .= '<span class="label">'.$label.'</span><input type="file" name="'.$dataField->field.'"><br>';
			break;
			
			//input = hidden
			case "hidden":
				$value = ($data)?$data:$dataField->default;
				$element .= '<input type="hidden" name="'.$dataField->field.'" value="'.$value.'" />';
			
			break;
			
			//image
			case "image":
			
				$path = $_SERVER["DOCUMENT_ROOT"].$dataField->url;
				if(file_exists($path) && $dataField->url != NULL)
					$element .= '<img src="'.$dataField->url.'" width="'.$dataField->maxW.'" height="'.$dataField->maxH.'" /><br>';
				else
					$element .="<i>Not set or File not exist</i><br>";
					
			break;
			
			
			case "html":
				$element .= $dataField->html;
			
			break;
			
			
			//<h2></h2>
			case "sectionTitle":
				$element .= "<h2>$dataField->title</h2><br>";
			break;
		
		}
		
		return $element;
	}


	function checkedFieldAs($field,$value){		
		return isset($field)?$value:(1-$value);
	}



}
















?>
