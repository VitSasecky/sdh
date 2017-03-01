<?php

namespace App\Controls\Grido\Components\Columns;

use Grido\Components\Columns\Text;
use Nette\Utils\Html;

/**
 * Class Boolean
 *
 * @package App\Controls\Grido\Components\Columns
 */
class Boolean extends Text
{
	/**
	 * {@inheritDoc}
	 */
	/**
	 * @param null $row
	 *
	 * @return Html
	 */
	public function getCellPrototype($row = null)
	{
		$cell = parent::getCellPrototype($row = null);
		$cell->class[] = 'center';
		return $cell;
	}

	/**
	 * @param $value
	 *
	 * @return \Nette\Utils\Html
	 */
	protected function formatValue($value)
	{
		$value = parent::formatValue($value);
		$icon = $value ? 'ok' : 'remove';
		$html = Html::el('i');
		$html->class = "glyphicon glyphicon-$icon icon-$icon";
		return $html;
	}
}
