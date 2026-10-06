<?php

/**
 * @package     Cyonima\Component\Cyonima\Administrator
 * @copyright   (C) 2026 Cyonima
 * @license     GNU General Public License version 2 or later
 */

namespace Cyonima\Component\Cyonima\Administrator\View\Certificatetemplate;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Certificate template edit view.
 */
class HtmlView extends BaseHtmlView
{
	public $item;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('certificatetemplate.id', Factory::getApplication()->input->getInt('id'));

		$this->item = $model->getItem();

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$isNew = empty($this->item->id);

		ToolbarHelper::title(Text::_('COM_CYONIMA_CERTIFICATE_TEMPLATE') . ': ' . ($isNew ? Text::_('JNEW') : $this->item->title), 'image');
		ToolbarHelper::apply('certificatetemplate.apply');
		ToolbarHelper::save('certificatetemplate.save');
		ToolbarHelper::cancel('certificatetemplate.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
