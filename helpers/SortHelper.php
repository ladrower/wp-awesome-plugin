<?php namespace WPAwesomePlugin;

class SortHelper {

    const ASC = "ASC";
    const DESC = "DESC";

    private $cookieKey;
    private $fields;
    private $orderby;
    private $order;
    private $defaults = array(
        'orderby' => null,
        'order' => null
    );
    private $cookie;
    private $metaMap = array();

    private function __construct ($sortableName, $fields = array())
    {
        $this->cookieKey = __NAMESPACE__ . "_sort_" . $sortableName;
        $this->cookie = isset($_COOKIE[$this->cookieKey]) ? json_decode(stripslashes($_COOKIE[$this->cookieKey]), 1) : array();
        $this->fields = $fields;
        $this->setOrder(@$this->cookie['orderby'], @$this->cookie['order']);
    }

    public static function createSortable ($sortableName, $fields)
    {
        return new static($sortableName, $fields);
    }

    public static function generateJSON ($orderby, $order)
    {
        return json_encode(array(
            'orderby' => $orderby,
            'order' => $order
        ));
    }

    private function persist ()
    {
        $this->cookie['orderby'] = $this->orderby;
        $this->cookie['order'] = $this->order;
        setcookie($this->cookieKey, json_encode($this->cookie), null, '/');
    }

    private function parseOrder ($order)
    {
        return $order === static::ASC ? static::ASC : static::DESC;
    }

    private function setDefaults ()
    {
        $this->orderby = $this->defaults['orderby'];
        $this->order = $this->defaults['order'];
    }

    public function setOrder ($field, $order)
    {
        if (is_string($field) && in_array($field, $this->fields)) {
            $this->orderby = $field;
            $this->order = $this->parseOrder($order);
            $this->persist();
        } else {
            $this->setDefaults();
        }
    }

    public function setMetaMap ($map)
    {
        if (is_array($map)) {
            foreach ($map as $key => $value) {
                if (in_array($key, $this->fields)) {
                    $this->metaMap[$key] = $value;
                }
            }
        }
    }

    public function setDefaultSort ($field, $order)
    {
        if (!in_array($field, $this->fields)) {
            throw new \Exception("Unknown field passed");
        }
        $this->defaults['orderby'] = $field;
        $this->defaults['order'] = $this->parseOrder($order);
        if (!$this->orderby) {
            $this->setDefaults();
        }
    }

    public function toArray ()
    {
        if (array_key_exists($this->orderby, $this->metaMap)) {
            return array(
                'meta_key' => $this->metaMap[$this->orderby]['meta_key'],
                'orderby' => 'meta_value',
                'meta_type' => isset($this->metaMap[$this->orderby]['meta_type'])
                    ? $this->metaMap[$this->orderby]['meta_type']
                    : null,
                'order' => $this->order
            );
        }
        return array(
            'orderby' => $this->orderby,
            'order' => $this->order
        );
    }

    public function getFields ()
    {
        return $this->fields;
    }

    public function getCookieKey ()
    {
        return $this->cookieKey;
    }

    public function isSortedBy ($field)
    {
        return $this->orderby === $field;
    }

    public function isAsc ()
    {
        return $this->order === static::ASC;
    }

}
