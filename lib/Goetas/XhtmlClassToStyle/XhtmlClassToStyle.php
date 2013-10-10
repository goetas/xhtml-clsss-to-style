<?php
namespace Goetas\XhtmlClassToStyle;
use Sabberworm\CSS\Property\CssNamespace;

use Sabberworm\CSS\Parser;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Symfony\Component\CssSelector\CssSelector;

class XhtmlClassToStyle {
	public function applyCss(DOMDocument $xml, $css) {

		list($rules, $nampespaces) = $this->extractRulesFormCss($css);

		$this->sortCssRules($rules);

		$this->applyRulesToDocument($xml, $rules, $nampespaces);
	}
	protected function applyRulesToDocument(DOMDocument $doc, array $rules, array $nss) {
		$domXpath = new DOMXPath($doc);

		foreach ($nss as $prefix => $value){
			$domXpath->registerNamespace($prefix?$prefix:null,$value);
		}
		foreach ($rules as $rule) {
			$xpath = CssSelector::toXPath($rule["selector"]);
			foreach ($domXpath->query($xpath) as $nodo){
			    $styles = array($nodo->getAttribute("style"));
				foreach ($rule["properties"] as $name => $value){
					$styles[$name] = "$name:$value";
				}
				$nodo->setAttribute("style", trim(implode(";", $styles),";"));
			}
		}
	}


	protected function extractRulesFormCss($css) {
		$oCssParser = new Parser($css);
		$oCssDocument = $oCssParser->parse();

		$rules = array();

		foreach ($oCssDocument->getAllRuleSets() as $cssRule){

			$rule = array();

			$selectors = $cssRule->getSelector();

			foreach ($selectors as $selector){

				$rule["selector"] = $selector->getSelector();
				$rule["specificity"] = $selector->getSpecificity();

				$rule["properties"] = array();
				foreach ($cssRule->getRules() as $prop){
					$rule["properties"][$prop->getRule()] = strval($prop->getValue());
				}

				$rules[]= $rule;
			}
		}
		$namespaces = array();

		foreach ($oCssDocument->getContents() as $decBlock){
			if($decBlock instanceof CssNamespace){
				$namespaces[$decBlock->getPrefix()] = $decBlock->getValue();
			}
		}

		return array($rules, $namespaces);
	}


	private function sortCssRules(array &$rules) {
		uasort($rules, function ($r1, $r2) {
			return $r1["specificity"]-$r2["specificity"];
		});
	}
	/**
	 * @see http://www.w3.org/TR/CSS2/cascade.html#specificity
	 */
	private function calculateSpecificity($selector) {
		$a = 0*1000;
		$b = substr_count($selector, "#")*100;
		$c = (substr_count($selector, ".")*10)+(substr_count($selector, ":")*10);
		$d = substr_count(preg_replace('/\s+/', " ", trim($selector)), " ")*1;
		return $a+$b+$c+$d;
	}

	protected function getAllParentNs(DOMElement $element) {
		$namespaces=array("fo"=> "http://www.w3.org/1999/XSL/Format");

		$namespaces [$element->namespaceURI] = $element->namespaceURI;
		foreach ($element->attributes as $attribute){
			$namespaces[$element->namespaceURI] = $element->namespaceURI;
		}

		if($this->parentNode->nodeType == \XML_ELEMENT_NODE && $this->parentNode instanceof DOMElement ){
			foreach ($this->getAllParentNs($this->parentNode) as $ns){
				$namespaces[$ns]=$ns;
			}
		}
		return $namespaces;
	}
}