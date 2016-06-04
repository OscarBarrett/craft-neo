<?php
namespace Craft;

class Neo_CriteriaModel extends ElementCriteriaModel
{
	private $_allElements;
	private $_currentFilters = [];
	
	private $_descendant = null;

	public static function convert(ElementCriteriaModel $ecm)
	{
		$attributes = array_filter($ecm->getAttributes(), function($value)
		{
			return (bool) $value;
		});

		return new Neo_CriteriaModel($attributes);
	}

	public function __construct($attributes, $_ = null)
	{
		$elementType = craft()->elements->getElementType(Neo_ElementType::NeoBlock);

		parent::__construct($attributes, $elementType);
	}

	public function copy()
	{
		$copy = parent::copy();

		if(!empty($this->_allElements))
		{
			$copy->setAllElements($this->_allElements);
		}

		return $copy;
	}

	public function setAttribute($name, $value)
	{
		if(parent::setAttribute($name, $value))
		{
			$method = '_filter' . ucfirst($name);

			if(craft()->request->isLivePreview() && method_exists($this, $method))
			{
				$this->_currentFilters[$method] = $value;

				$this->_runFilters();
			}

			return true;
		}

		return false;
	}

	public function setAllElements($elements)
	{
		$this->_allElements = $elements;

		$this->_runFilters();
	}

	private function _runFilters()
	{
		if(!empty($this->_allElements))
		{
			$elements = array_filter($this->_allElements, function($element)
			{
				return $this->_elementFilters($element);
			});

			$this->setMatchedElements($elements);
		}
	}

	private function _elementFilters($element)
	{
		foreach($this->_currentFilters as $method => $value)
		{
			if(!$this->$method($element, $value))
			{
				return false;
			}
		}

		return true;
	}

	private function _filterLevel($element, $value)
	{
		if(!$value)
		{
			return true;
		}

		return $element->level == $value;
	}

	private function _filterDescendantOf($element, $value)
	{
		$this->_descendant = $value;

		if(!$value)
		{
			return true;
		}

		$elements = $this->_allElements;
		$found = false;

		foreach($elements as $searchElement)
		{
			if($searchElement === $value)
			{
				$found = true;
			}
			else if($found)
			{
				if($searchElement->level > $value->level)
				{
					if($searchElement === $element)
					{
						return true;
					}
				}
				else
				{
					break;
				}
			}
		}

		return false;
	}

	private function _filterDescendantDist($element, $value)
	{
		if(!$value || !$this->_descendant)
		{
			return true;
		}

		return $element->level <= $this->_descendant->level + $value;
	}

	private function _filterLimit($element, $value)
	{
		return true; // TODO
	}

	private function _filterLocale($element, $value)
	{
		return true; // TODO
	}

	private function _filterLocaleEnabled($element, $value)
	{
		return true; // TODO
	}

	private function _filterOrder($element, $value)
	{
		return true; // TODO
	}

	private function _filterStatus($element, $value)
	{
		return true; // TODO
	}

	private function _filterCollapsed($element, $value)
	{
		return true; // TODO
	}
}
