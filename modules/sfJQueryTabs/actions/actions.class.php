<?php

/**
 * sfJQueryTabs actions.
 *
 * @package    sfJQueryTabsPlugin
 * @subpackage sfJQueryTabs
 * @author     Jordi Llonch
 * @version    0.1
 */
class sfJQueryTabsActions extends sfActions
{
  /**
   * Executes index action
   *
   */
  public function executeIndex()
  {
    $this->getResponse()->addStyleSheet('/sfJQueryTabsPlugin/ui.tabs/ui.tabs.css');
    $this->getResponse()->addJavaScript('/sfJQueryTabsPlugin/ui.tabs/jquery-1.2.3.pack.js');
    $this->getResponse()->addJavaScript('/sfJQueryTabsPlugin/ui.tabs/ui.tabs.pack.js');
    $this->setLayout(sfLoader::getTemplateDir('sfJQueryTabs', 'layout.php').'/layout');
    $this->tabs = sfJQueryTabs::render();
  }
}
