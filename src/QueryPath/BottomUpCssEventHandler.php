<?php
/**
 * Use a bottom-up searching algorithm.
 *
 * This collects all of the events from a {@link CssEventHandler}, and then searches
 * in reverse order, narrowing as it goes. Searching this way eliminates the need
 * for duplicate checks.
 *
 * This code is not stable, and cannot be used. If you would like to contribute code,
 * please jump right in.
 *
 * @package QueryPath
 * @subpackage CSSParser
 * @author M Butcher <matt@aleph-null.tv>
 * @license http://opensource.org/licenses/lgpl-2.1.php LGPL (The GNU Lesser GPL) or an MIT-like license. 
 * @since 2.0
 */
 
class BottomUpCssEventHandler implements CssEventHandler {
  protected $events = array();
  protected $dom = NULL; // Always points to the top level.
  protected $matches = NULL; // The matches
  protected $alreadyMatched = array(); // Matches found before current selector.
  protected $findAnyElement = TRUE;
  
  public function elementID($id) {
    $this->events['elementID'] = array('id' => $id);
  }
  public function element($name) {
    $this->events['element'] = array('name' => $name);
  }
  public function elementNS($name, $namespace = NULL) {
    $this->events['elementNS'] = array('name' => $name, 'namespace' => $namespace);
  }
  public function anyElement() {
    $this->events['anyElement'] = array();
  }
  public function anyElementInNS($ns) {
    $this->events['anyElementInNS'] = array('ns' => $ns);
  }
  public function elementClass($name) {
    $this->events['elementClass'] = array('ns' => $ns);
  }
  public function attribute($name, $value = NULL, $operation = CssEventHandler::isExactly) {
    $this->events['attribute'] = array('name' => $name, 'value' => $value, 'operation' => $operation);
  }
  public function attributeNS($name, $ns, $value = NULL, $operation = CssEventHandler::isExactly) {
    $this->events['attributeNS'] = array('name' => $name, 'value' => $value, 'operation' => $operation, 'ns' => $ns);
  }
  public function pseudoClass($name, $value = NULL) {
    $this->events['pseudoClass'] = array('name' => $name, 'value' => $value);
  }
  public function pseudoElement($name) {
    $this->events['pseudoElement'] = array('name' => $name);
  }
  public function directDescendant() {
    $this->events['directDescendant'] = array();
  }
  public function adjacent() {
    $this->events['adjacent'] = array();
  }
  public function anotherSelector() {
    $this->events['anotherSelector'] = array();
  }
  public function sibling() {
    $this->events['sibling'] = array();
  }
  public function anyDescendant() {
    $this->events['anyDescendant'] = array();
  }
  
  /**
   * Create a new event handler.
   */
  public function __construct($dom) {
    // Array of DOMElements
    if (is_array($dom)) {
      $matches = array();
      foreach($dom as $item) {
        if ($item instanceof DOMNode && $item->nodeType == XML_ELEMENT_NODE) {
          $matches[] = $item;
        }
      }
      $this->dom = count($matches) > 0 ? $matches[0] : NULL;
      $this->matches = $matches;
    }
    // DOM Document -- we get the root element.
    elseif ($dom instanceof DOMDocument) {
      $this->dom = $dom->documentElement;
      $this->matches = array($dom->documentElement);
    }
    // DOM Element -- we use this directly
    elseif ($dom instanceof DOMElement) {
      $this->dom = $dom;
      $this->matches = array($dom);
    }
    // NodeList -- We turn this into an array
    elseif ($dom instanceof DOMNodeList) {
      $matches = array();
      foreach ($dom as $item) {
        if ($item->nodeType == XML_ELEMENT_NODE) {
          $matches[] = $item;
        }
      }
      $this->dom = $matches;
      $this->matches = $matches;
    }
    // FIXME: Handle SimpleXML!
    // Uh-oh... we don't support anything else.
    else {
      throw new Exception("Unhandled type: " . get_class($dom));
    }
  }
  
  public function find($filter) {
    $parser = new CssParser($filter, $this);
    $parser->parse();
    
    // Now we have a chain. Begin bottom-up finding.
    $events = array_reverse($this->events, TRUE);
    foreach ($events as $event => $params) {
      $function = $event . 'Handler';
      //call_user_func_array() is slow, so we pass array this way.
      $this->$function($params);
    }
    
    return $this;
  }
  
  public function getMatches() {
    return $this->matches;
  }
  
  /**
   * Get the collected events as an associative array.
   *
   * @return array
   *  Associative array of event details.
   */
  public function getEvents() {
    return $this->events;
  }
  
  //////////////////////
  // HANDLERS //////////
  //////////////////////
  public function elementHandler($args) {
    $found = array();
    foreach ($this->matches as $item) {
      $tags = $item->getElementsByTagName();
      foreach($tags as $tag) $found[] = $tag;
    }
  }
  
  public function elementNSHandler($args) {
    
  }
}