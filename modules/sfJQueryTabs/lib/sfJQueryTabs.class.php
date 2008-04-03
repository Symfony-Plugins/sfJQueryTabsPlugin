<?php
/**
 * sfJQueryTabs
 * 
 * Plugin for the Framework Symfony that generates tabs dynamically
 * 
 * LICENSE: This source file is subject to MIT license, that is available 
 * through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * @author    jordi llonch <jordi [at] laigu [dot] net>
 * @copyright Jordi Llonch
 * @license   http://www.opensource.org/licenses/mit-license.php   MIT
 * @version   Release: 0.0.1 alpha
 * @version   SVN: $Id$
 * @link      http://www.laigu.net/
 */

class sfJQueryTabs extends sfWebRequest
{
  /**
  * Render tabs
  * 
  * @return string
  */
  public static function render()
  {
    if (SF_ENVIRONMENT=="dev") $current_url = "/".SF_APP."_".SF_ENVIRONMENT.".php";
    else $current_url = "/".SF_APP.".php";
    
    $arrTabs = sfYaml::load(sfConfig::get('sf_app_config_dir').DIRECTORY_SEPARATOR."jquerytabs.yml");
    if (!is_array($arrTabs)) return;
    //print_r($arrTabs);
    $tabsGroup = "default";
    
    // scripts
    $render = sfJQueryTabs::scripts($arrTabs[$tabsGroup], $current_url);
    
    $render .= '<div id="menu">';
    // ul list
    $render .= sfJQueryTabs::ulList($arrTabs[$tabsGroup]);
    
    // divs
    $render .= sfJQueryTabs::divs($arrTabs[$tabsGroup]);
    $render .= '</div>';
    
    return $render;
  }
  
  /**
  * Javascript to run tabs
  * 
  * @param  $arrList      array   menu items
  * @param  $current_url  string  base url   
  * @return string
  */
  private static function scripts($arrList, $current_url)
  {
    $render = '
<script language="javascript" type="text/javascript">
function loadTab(idIframe, url) 
{
  // Load url from iframe 
  $(idIframe)[0].src = url;
  // Wait until iframe is loaded to resize it
  $($(idIframe)[0]).load(function(){
    var iframeHeight = $(idIframe)[0].contentDocument.body.scrollHeight + 50;
    if (iframeHeight < 300) iframeHeight = 300;
    $(idIframe)[0].height = iframeHeight
  });  
}

$(document).ready(function(){
    // Load first tab'."\n";
    
    $arrKeys = array_keys($arrList);
    $render .= 'loadTab("#ifrm'.$arrKeys[0].'", "'.$current_url.'/'.$arrList[$arrKeys[0]]["link"].'");'."\n";
    
    $render .= '
    $("#menu ul").tabs({ 
    // Preload a blank iframe (for best visual effect)
    select: function(ui) {
      var selectedIndex = $(\'#menu > ul\').data(\'selected.tabs\');
      switch (selectedIndex) {
';
    $i = 0;
    foreach ($arrList AS $k => $v)
    {
      $render .= 'case '.$i.': ';
      $render .= sfJQueryTabs::scriptsSelectCase($k, $v);
      $render .= 'break;'."\n";
      $i++;
    }
    $render .= '
      }
    }, 
    // Load tab
    show: function(ui) {'."\n";
    
    $render .= sfJQueryTabs::scriptsSwitch($current_url, $arrList);
    
    $render .= '  
      }
    });
  });
</script>'."\n";
        
    return $render;
  }

  /**
  * Javascript: case
  * 
  * @param  $k  mixed   key
  * @param  $v array    value   
  * @return string
  */
  private static function scriptsSelectCase($k, $v)
  {
    if (!isset($v["items"])) 
    {
      return '$("#ifrm'.$k.'")[0].src = "";';
    }
    else
    {
      $render = "";
      foreach ($v["items"] AS $k2 => $v2)
      {
        $render .= sfJQueryTabs::scriptsSelectCase($k2, $v2);
      }
      return $render;
    }
  }
  
  /**
  * Javascript: switch
  * 
  * @param  $current_url  string  base url   
  * @param  $k  mixed     key
  * @param  $v array      value   
  * @return string
  */
  private static function scriptsSwitch($current_url, $arrList, $idMenu="menu")
  {
    $render = 'var selectedIndex'.$idMenu.' = $(\'#'.$idMenu.' > ul\').data(\'selected.tabs\');
switch (selectedIndex'.$idMenu.') {'."\n";
    $i = 0;
    foreach ($arrList AS $k => $v)
    {
      if (!isset($v["items"])) 
      {
        if (isset($v["link"])) $render .= 'case '.$i.': loadTab("#ifrm'.$k.'", "'.$current_url.'/'.$v["link"].'"); break;'."\n";
      }
      else
      {
        $render .= 'case '.$i.':'."\n";
        $render .= sfJQueryTabs::scriptsSwitch($current_url, $v["items"], 'container_'.$k);
        $render .= 'break;'."\n";
      }
      $i++;
    }
    $render .= '}'."\n";
    return $render;
  }
  
  /**
  * ul html
  * 
  * @param  $arrList      array   menu items
  * @return string
  */
  private static function ulList($arrList)
  {
    $render = '<ul>'."\n";
    foreach ($arrList AS $k => $v)
    {
      if (isset($v["text"])) $render .= '<li><a href="#tab-'.$k.'"><span>'.$v["text"].'</span></a></li>'."\n";
      else $render .= '<li><a href="#tab-'.$k.'"></a></li>'."\n";
    }
    $render .= '</ul>'."\n";
    return $render;
  }

  /**
  * div html
  * 
  * @param  $arrList      array   menu items
  * @return string
  */
  private static function divs($arrList)
  {
    $render = "";
    foreach ($arrList AS $k => $v)
    {
      if (!isset($v["items"])) 
      {
        if (isset($v["text"])) 
        {
          $render .= '<div id="tab-'.$k.'"><iframe id="ifrm'.$k.'" width="100%" src="" frameborder="0"></iframe></div>'."\n";
        }
      }
      else
      {
        $render .= '<div id="tab-'.$k.'">'."\n";
        $render .= '<div id="container_'.$k.'">'."\n";
        $render .= sfJQueryTabs::ulList($v["items"]);
        $render .= sfJQueryTabs::divs($v["items"]);
        $render .= '</div>'."\n";
        $render .= '</div>'."\n";
      }
    }
    
    return $render;
  }
}