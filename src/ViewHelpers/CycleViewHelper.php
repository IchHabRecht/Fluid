<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper cycles through the specified values.
 * This can be often used to specify CSS classes for example.
 * **Note:** To achieve the "zebra class" effect in a loop you can also use the "iteration" argument of the **for** ViewHelper.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo"><f:cycle values="{0: 'foo', 1: 'bar', 2: 'baz'}" as="cycle">{cycle}</f:cycle></f:for>
 * </code>
 * <output>
 * foobarbazfoo
 * </output>
 *
 * <code title="Alternating CSS class">
 * <ul>
 *   <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo">
 *     <f:cycle values="{0: 'odd', 1: 'even'}" as="zebraClass">
 *       <li class="{zebraClass}">{foo}</li>
 *     </f:cycle>
 *   </f:for>
 * </ul>
 * </code>
 * <output>
 * <ul>
 *   <li class="odd">1</li>
 *   <li class="even">2</li>
 *   <li class="odd">3</li>
 *   <li class="even">4</li>
 * </ul>
 * </output>
 *
 * Note: The above examples could also be achieved using the "iteration" argument of the ForViewHelper
 *
 * @api
 */
class CycleViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * The values to be iterated through
	 *
	 * @var array|\SplObjectStorage
	 */
	protected $values = NULL;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('values', 'array', 'The array or object implementing \ArrayAccess (for example \SplObjectStorage) to iterated over', FALSE);
		$this->registerArgument('as', 'strong', 'The name of the iteration variable', TRUE);
	}

	/**
	 * Renders cycle view helper
	 *
	 * @return string Rendered result
	 * @api
	 */
	public function render() {
		$values = $this->arguments['values'];
		$as = $this->arguments['as'];
		if ($values === NULL) {
			return $this->renderChildren();
		}
		if ($this->values === NULL) {
			$this->initializeValues($values);
		}
		$currentCycleIndex = 0;
		$identifier = $this->getIdentifier($as);
		if ($this->renderingContext->getView()->getRenderingContext()->getVariableProvider()->exists($identifier)) {
			$currentCycleIndex = $this->renderingContext->getView()->getRenderingContext()->getVariableProvider()->get($identifier);
		}
		if ($currentCycleIndex === NULL || $currentCycleIndex >= count($this->values)) {
			$currentCycleIndex = 0;
		}

		$currentValue = isset($this->values[$currentCycleIndex]) ? $this->values[$currentCycleIndex] : NULL;
		$this->templateVariableContainer->add($as, $currentValue);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);

		$currentCycleIndex++;
		$this->renderingContext->getView()->getRenderingContext()->getVariableProvider()->add($identifier, $currentCycleIndex);

		return $output;
	}

	/**
	 * Sets this->values to the current values argument and resets $this->currentCycleIndex.
	 *
	 * @param array|\Traversable $values The array or \SplObjectStorage to be stored in $this->values
	 * @return void
	 * @throws ViewHelper\Exception
	 */
	protected function initializeValues($values) {
		if (is_object($values)) {
			if (!$values instanceof \Traversable) {
				throw new ViewHelper\Exception('CycleViewHelper only supports arrays and objects implementing \Traversable interface', 1248728393);
			}
			$this->values = iterator_to_array($values, FALSE);
		} else {
			$this->values = array_values($values);
		}
	}

	protected function getIdentifier($as) {
		return substr(md5($as . serialize($this->values)), 0, 8);
	}
}
