<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Navigation Plugin for Foundation 4
 * Based on the PyroCMS Navigation plugin
 *
 * @author     Joshua Chamberlain <josh@zephyri.co>
 */
class Plugin_Navbar extends Plugin
{

  public $version = '1.0.0';
  public $name = array(
    'en' => 'Navbar',
  );
  public $description = array(
    'en' => 'Build navigation links including links in dropdown menus using Foundation 4 compatiable markup.',
  );

  /**
   * Returns a PluginDoc array that PyroCMS uses 
   * to build the reference in the admin panel
   *
   * All options are listed here but refer 
   * to the Blog plugin for a larger example
   *
   * @todo fill the  array with details about this plugin, then uncomment the return value.
   *
   * @return array
   */
  public function _self_doc()
  {
    $info = array(
      'left' => array(
        'description' => array(
          'en' => 'Output links within a ul.left. Only useful if not returning an array.'
        )
      ),
      'right' => array(
        'description' => array(
          'en' => 'Output links within a ul.right. Only useful if not returning an array.'
        )
      ),
      'links' => array(
        'description' => array(
          'en' => 'Output links from a single navigation group. If [group_segment] is used it loads the group specified by that uri segment.'
        ),
        'single' => true,
        'double' => true,
        'variables' => 'url|title|total|target|classes|active|children }}{{ /children',
        'attributes' => array(
          'group' => array(
            'type' => 'text',// Can be: slug, number, flag, text, array, any.
            'flags' => '',
            'default' => 'asc',
            'required' => true,
          ),
          'group_segment' => array(
            'type' => 'number',
            'flags' => '',
            'default' => '',
            'required' => false,
          ),
          'divider' => array(
            'type' => 'flag',
            'flags' => 'Y|N',
            'default' => 'Y',
            'required' => false,
          ),
          'max_depth' => array(
            'type' => 'text',
            'flags' => '',
            'default' => '2',
            'required' => false,
          ),
        ),
      ),// end links method
    );
  
    return $info;
  }


  public function __construct() {
    $this->load->model('navigation/navigation_m');
  }


  /**
   * Build the left set of links
   */
  public function left() {
    return $this->links('left');
  }


  public function right() {
    return $this->links('right');
  }


  /**
   * Navigation
   *
   * Creates a list of menu items
   *
   * Usage:
   * {{ navigation:links group="header" }}
   * Optional:  divider="", group_segment=""
   *
   * @param  array
   * @return  array
   */
  public function links($ulClass = null)
  {
    $group         = $this->attribute('group');
    $group_segment = $this->attribute('group_segment');

    is_numeric($group_segment) and $group = $this->uri->segment($group_segment);

    // We must pass the user group from here so that we can cache the results and still always return the links with the proper permissions
    $params = array(
      $group,
      array(
        'user_group' => ($this->current_user and isset($this->current_user->group)) ? $this->current_user->group : false,
        'front_end'  => true,
        'is_secure'  => IS_SECURE,
      )
    );

    $links = $this->pyrocache->model('navigation_m', 'get_link_tree', $params, config_item('navigation_cache'));

    return $this->_build_links($links, $this->content(), $ulClass);
  }


  /**
   * Builds the Page Tree into HTML
   * 
   * @param array $links      Page Tree array from `navigation_m->get_link_tree`
   * @param bool  $return_arr Return as an Array instead of HTML
   * @return array|string
   */
  private function _build_links($links = array(), $return_arr = true, $ulClass = null, $level = 1)
  {
    static $current_link = false;
    static $active = array(); // we'll mark each item in the active tree

    $divider      = $this->attribute('divider', true);
    $output         = $return_arr ? array() : '';
    $max_depth      = $this->attribute('max_depth', 2);
    $i              = 1;
    $total          = count($links);
    $output = $return_arr ? array() : '';


    foreach($links as $k => $link) {
      // is this the current link?
      if (!isset($active[$level]) && preg_match('@^' . current_url() . '/?$@', $link['url']) or ($link['link_type'] == 'page' and $link['is_home']) and site_url() == current_url() )
        $active[$level] = $link['id'];

      // get children
      $children = $link['children'] && $max_depth > $level ? $this->_build_links($link['children'], $return_arr, null, $level+1) : null;

      // what are the classes of this link?
      $classes = array();
      if($link['class'])
        $classes[] = $link['class'];
      if($children)
        $classes[] = 'has-dropdown';
      if(isset($active[$level]) && $active[$level] == $link['id'])
        $classes[] = 'active';

      if($return_arr) {
        $output[$k] = array(
          'url' => $link['url'],
          'title' => $link['title'],
          'total' => $total,
          'target' => $link['target'],
          'children' => $children,
          'classes' => $classes,
          'active' => isset($active[$level]) && $active[$level] == $link['id']
        );

      } else {
        $output .= ($divider && $level == 1 ? '<li class="divider"></li>' : '') // only show divider on top level
                . '<li' . ($classes ? ' class="'.implode(' ', $classes).'"' : '') .'>'
                  . '<a href="' . $link['url'] . '">' . $link['title'] . '</a>'
                  . ($children ? '<ul class="dropdown">' . $children . '</ul>' : '')
                . '</li>';

      }

      // if this one's active, mark parent as active too
      // doing it here in case this is a parent of the active link and not the link itself
      if($level > 1 && isset($active[$level]) && $active[$level] == $link['id'])
        $active[$level-1] = $link['parent'];
    }

    if(!$return_arr && $ulClass)
      $output = '<ul class="'.$ulClass.'">' . $output . '</ul>';

    return $output;
  }
}

/* End of file navbar.php */
