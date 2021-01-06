<?php

namespace Be\Framework\Menu;

/**
 * 菜单基类
 */
abstract class Driver
{
    protected $menus = [];
    protected $menuTree = null;

    public function __construct()
    {

    }


    /**
     * 添加菜单项
     *
     * @param int $menuId 菜单编号
     * @param int $parentId 父级菜单编号， 等于0时为顶级菜单
     * @param string $icon 图标
     * @param string $label 中文名称
     * @param string $url 网址
     * @param string $target 打开方式
     */
    public function addMenu($menuId, $parentId, $icon, $label, $url, $target = '_self')
    {
        $menu = new \stdClass();
        $menu->id = $menuId;
        $menu->parentId = $parentId;
        $menu->icon = $icon;
        $menu->label = $label;
        $menu->url = $url;
        $menu->target = $target;

        $this->menus[$menuId] = $menu;
    }

    /**
     * 获取一项菜单 或 整个菜单
     *
     * @param int $menuId 菜单编号
     * @return object | false | array
     */
    public function getMenu($menuId = 0)
    {
        if ($menuId) {
            if (array_key_exists($menuId, $this->menus)) {
                return $this->menus[$menuId];
            } else {
                return false;
            }
        }
        return $this->menus;
    }

    /**
     * 获取菜单树
     *
     * @return array()
     */
    public function getMenuTree()
    {
        if (!is_array($this->menuTree)) {
            $this->menuTree = $this->createMenuTree();
        }
        return $this->menuTree;
    }

    /**
     * 获取当前位置
     *
     * @param string $url 网址
     * @return array
     */
    public function getRouteByUrl($url)
    {
        $menuId = null;
        foreach ($this->menus as $menu) {
            if ($menu->url == $url) {
                $menuId = $menu->id;
                break;
            }
        }

        if ($menuId === null) return [];
        return $this->getRoute($menuId);
    }

    /**
     * 获取当前位置
     *
     * @param int $menuId
     * @return array
     */
    public function getRoute($menuId = '0')
    {
        $route = array();
        if (array_key_exists($menuId, $this->menus)) {
            $route[] = $this->menus[$menuId];
            $parentId = $this->menus[$menuId]->parentId;
            while ($parentId) {
                if (array_key_exists($parentId, $this->menus)) {
                    $route[] = $this->menus[$parentId];
                    $parentId = $this->menus[$parentId]->parentId;
                } else {
                    $parentId = '0';
                }
            }
        }
        $route = array_reverse($route, true);
        return $route;
    }

    /**
     * 创建菜单树
     * @param int $menuId
     * @return array | false
     */
    protected function createMenuTree($menuId = '0')
    {
        $subMenus = array();
        foreach ($this->menus as $menu) {
            if ($menu->parentId == $menuId) {
                $menu->subMenu = $this->createMenuTree($menu->id);
                $subMenus[] = $menu;
            }
        }
        if (count($subMenus))
            return $subMenus;
        return false;
    }


}