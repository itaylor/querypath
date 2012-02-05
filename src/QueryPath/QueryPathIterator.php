<?php
/**
 * @file
 *
 * Utility iterator for QueryPath.
 */

namespace QueryPath;

/**
 * An iterator for QueryPath.
 *
 * This provides iterator support for QueryPath. You do not need to construct
 * a QueryPathIterator. QueryPath does this when its {@link QueryPath::getIterator()}
 * method is called.
 *
 * @ingroup querypath_util
 */
class QueryPathIterator extends \IteratorIterator {
  public $options = array();
  private $qp = NULL;

  public function current() {
    if (!isset($this->qp)) {
      $this->qp = qp(parent::current(), NULL, $this->options);
    }
    else {
      $splos = new SplObjectStorage();
      $splos->attach(parent::current());
      $this->qp->setMatches($splos);
    }
    return $this->qp;
  }
}