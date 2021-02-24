<?php

namespace Be\F\Annotation;

/**
 * 驱动
 */
class Driver
{
    protected $value;

    /**
     * Driver constructor.
     * @param string | array $value
     */
    public function __construct($value = '')
    {
        if ($value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (substr($v, 0, 7) == 'return ') {
                        if (substr($v,-1) != ';') {
                            $v .= ';';
                        }

                        try {
                            $this->$k = eval($v);
                        } catch (\Throwable $e) {

                        }
                    } else {
                        $this->$k = $v;
                    }
                }
            } else {
                $this->value = $value;
            }
        }
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * 转成简单数组
     *
     * @return array
     */
    public function toArray()
    {
        $array = get_object_vars($this);
        return $array;
    }

    /**
     * 转成简单对象
     *
     * @return Object
     */
    public function toObject()
    {
        return (Object)$this->toArray();
    }

}