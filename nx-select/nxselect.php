<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');


class JFormFieldNxSelect extends JFormField {

    protected $type = 'XMLSelect';

    // getLabel() left out

    public function getInput() {
		// Dokument Instanzieren
		$document = JFactory::getDocument();
		// Den XML-Pfad holen
		$formData 	= json_decode($this->form->getData('jform'),'true');
		$setupmsg 	= '<div class="alert alert-warning" style="width:25%;text-align:center;">Bitte Pfad zur XML angeben und Modul Speichern.</div>';
		$nofilemsg 	= '<div class="alert alert-danger" style="width:25%;text-align:center;">XML Datei wurde nicht gefunden, Bitte Pfad prüfen!</div>';
		$nofilemsg2 = '<div class="alert alert-danger" style="width:25%;text-align:center;">XML Datei wurde nicht gefunden - Die Anfrage wurde weitergeleitet, Bitte Pfad prüfen!</div>';
		
		if (isset($formData['id'])){										// 	Prüfen ob für dieses Modul bereits Daten vorliegen

			$file = $formData['params']['filepath'];						// 	Wir holen den Pfad zur XML aus dem Feld filepath
			$file_headers = @get_headers($file);

			if(strpos($file_headers[0], '404')){
				return $nofilemsg;
			}else if (strpos($file_headers[0], '302') && strpos($file_headers[7], '404')){
				return $nofilemsg2;
			}else{
				//print_r($file_headers);
				try{	
					if(!$xml = simplexml_load_file($file)){                 	// 	XML File in Parser einpflegen und Exceptionhandling           
						throw new Exception($nofilemsg);	
					}
				}
				catch (Exception $e){
					return $e->getMessage();
				}
				

				// XML Datei & Daten sind vorhanden
				if (array_key_exists('location', $formData['params'])) {
					$selection = $formData['params']['location'];					// 	Wert der bestehenden Auswahl abholen
					$xml = simplexml_load_file($file);								// 	Die XML File aus dem Pfad in der Var $file laden

					//######################################################			Script damit in der Auswahlbox die bisherige Auswahl gezeigt wird

					$selectedInfo = $xml->xpath('/Article/Location[@id='.$selection.']');
					$name = strval($selectedInfo[0]['name_de']);
					$CountryCode = strval($selectedInfo[0]['countrycode']);
					$selectedItem = ''.$name.' ('.$CountryCode.')';

					$document->addScriptDeclaration('
						jQuery(document).ready(function(){
							//alert("'.$selection.'");
							jQuery(function() {
								jQuery("#jform_params_location_chzn span").html(\''.$selectedItem.'\');
							});
						});
					');
				}else{
					$document->addScriptDeclaration('
						jQuery(document).ready(function(){
							jQuery(function() {
								jQuery("#jform_params_location_chzn span").html(\'Bitte wählen...\');
							});
						});
					');
				}
				
				$options = '';													//	In dieser Variable werden die Optionen als String angefügt
				
				// Alle Locations aus der XML lesen und mit den relevanten Daten als Option hinterlegen.
				foreach($xml->xpath('/Article/Location') as $location){
					$id 		= $location['id'];
					$name 		= $location['name_de'];
					$country 	= $location['countrycode'];

					// Als Option in die Select Liste hinzufügen
					$options = $options.'<option value="' . $id . ' "> ' . $name . ', '.$country.' (' . $id . ')</option>';
				}

				return '<select id="'.$this->id.'" name="'.$this->name.'">'.$options.'</select>';

			}
		}else{
			// Das Modul wurde gerade erstellt
			return $setupmsg;
		}
	}
}

?>
