<?php
namespace Be\Cache\System\Template\Admin\Plugin\Lists;

use Be\System\Be;
use Be\System\Session;

class display extends \Be\System\Template
{

  public function display()
  {

    ?>
<?php
$config = Be::getConfig('System.System');
$my = Be::getUser();
$themeUrl = Be::getProperty('Theme.Admin')->url();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $this->title; ?></title>

    <script src="<?php echo $themeUrl; ?>/js/vue-2.6.11.min.js"></script>

    <script src="<?php echo $themeUrl; ?>/js/axios-0.19.0.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="<?php echo $themeUrl; ?>/js/vue-cookies-1.5.13.js"></script>

    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/css/element-ui-2.13.2.css">
    <script src="<?php echo $themeUrl; ?>/js/element-ui-2.13.2.js"></script>

    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/css/font-awesome-4.7.0.min.css" />

    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/css/theme.css" />
    
    <style type="text/css">
        .el-table__row .el-divider__text, .el-link {
            font-size: 12px;
            margin-left: 4px;
            margin-right: 4px;
        }

        .el-drawer__header span {
            outline: none;
        }
    </style>

</head>
<body>
    <be-body>
    <div class="be-body">

        <div id="app-west" :class="{'be-west': true, 'be-west-collapse': collapse}" v-cloak>
            <be-west>

            <div class="logo">
                <a href="<?php echo beUrl(); ?>"></a>
            </div>

            <div class="west-menu">
                <?php
                $menu = Be::getMenu();
                $menuTree = $menu->getMenuTree()
                ?>
                <el-menu
                        background-color="#001529"
                        text-color="#ccc"
                        active-text-color="#ffd04b"
                        :default-active="activeIndex"
                        :collapse="collapse"
                        :collapse-transition="false">
                    <?php
                    $appName = Be::getRequest()->app();
                    foreach ($menuTree as $menu) {

                        if ($menu->id == $appName) {
                            // 有子菜单
                            if ($menu->subMenu) {
                                foreach ($menu->subMenu as $subMenu) {
                                    echo '<el-submenu index="west-menu-'.$subMenu->id.'">';

                                    echo '<template slot="title">';
                                    echo '<i class="'.$subMenu->icon.'"></i>';
                                    echo '<span>'.$subMenu->label.'</span>';
                                    echo '</template>';

                                    if ($subMenu->subMenu) {
                                        foreach ($subMenu->subMenu as $subSubMenu) {
                                            echo '<el-menu-item index="west-menu-'.$subSubMenu->id.'">';
                                            echo '<template slot="title">';
                                            echo '<el-link href="'.$subSubMenu->url.'" icon="'.$subSubMenu->icon.'" :underline="false">';
                                            echo $subSubMenu->label;
                                            echo '</el-link>';
                                            echo '</template>';
                                            echo '</el-menu-item>';
                                        }
                                    }
                                    echo '</el-submenu>';
                                }
                            }
                            break;
                        }
                    }
                    ?>
                </el-menu>

            </div>

            <div class="toggle" @click="toggleMenu">
                <i :class="collapse ?'el-icon-s-unfold': 'el-icon-s-fold'"></i>
            </div>

            </be-west>
        </div>


        <div class="be-middle" id="be-middle">
            <be-middle>

            <div class="be-north" id="be-north">
                <be-north>

                <div class="menu">
                    <div v-cloak>
                        <?php
                        $menu = Be::getMenu();
                        $menuTree = $menu->getMenuTree();
                        ?>
                        <el-menu
                                mode="horizontal"
                                :default-active="defaultActive"
                                background-color="#eee"
                                text-color="#666"
                                active-text-color="#000">
                            <?php
                            foreach ($menuTree as $menu) {

                                // 有子菜单
                                if ($menu->subMenu) {
                                    echo '<el-submenu index="north-menu-'.$menu->id.'">';

                                    echo '<template slot="title">';
                                    echo '<i class="'.$menu->icon.'"></i>';
                                    echo '<span>'.$menu->label.'</span>';
                                    echo '</template>';

                                    foreach ($menu->subMenu as $subMenu) {
                                        echo '<el-submenu index="north-menu-'.$subMenu->id.'">';

                                        echo '<template slot="title">';
                                        echo '<i class="'.$subMenu->icon.'"></i>';
                                        echo '<span>'.$subMenu->label.'</span>';
                                        echo '</template>';

                                        if ($subMenu->subMenu) {
                                            foreach ($subMenu->subMenu as $subSubMenu) {
                                                echo '<el-menu-item index="north-menu-'.$subSubMenu->id.'">';
                                                echo '<el-link href="'.$subSubMenu->url.'" icon="'.$subSubMenu->icon.'" :underline="false">';
                                                echo $subSubMenu->label;
                                                echo '</el-link>';
                                                echo '</el-menu-item>';
                                            }
                                        }
                                        echo '</el-submenu>';
                                    }
                                    echo '</el-submenu>';
                                }
                            }
                            ?>

                            <el-submenu>
                                <template slot="title">
                                    <i class="el-icon-question"></i>
                                    <span slot="title">帮助</span>
                                </template>

                                <el-menu-item index="help-official">
                                    <el-link href="http://www.phpbe.com/" target="_blank" icon="el-icon-position" :underline="false">官方网站</el-link>
                                </el-menu-item>
                                <el-menu-item index="help-support">
                                    <el-link href="http://support.phpbe.com/" target="_blank" icon="el-icon-help" :underline="false">技术支持</el-link>
                                </el-menu-item>
                            </el-submenu>

                        </el-menu>

                    </div>

                </div>

                <div class="user">
                    <?php
                    $configUser = Be::getConfig('System.User');
                    ?>
                    您好：
                    <img src="<?php
                    if ($my->avatar == '') {
                        echo Be::getProperty('App.System')->url().'/Template/User/images/avatar.png';
                    } else {
                        echo Be::getRequest()->dataUrl().'/System/User/Avatar/'.$my->avatar;
                    }
                    ?>" style="max-width:24px;max-height:24px; vertical-align: middle;" />
                    <?php echo $my->name; ?>

                    <el-button type="danger" icon="el-icon-star-off" onclick="window.location.href='<?php echo beUrl('System.User.logout')?>';" size="mini">退出</el-button>

                </div>

                </be-north>
            </div>

            <div class="be-center">
                <div class="center-body">
                    
    <?php
    $js = [];
    $css = [];
    $formData = [];
    $vueData = [];
    $vueMethods = [];
    $vueHooks = [];

    $toolbarItemDriverNames = [];
    ?>
    <div id="app" v-cloak>

        <el-form<?php
        $formUi = [
            ':inline' => 'true',
            'size' => 'mini',
        ];
        if (isset($this->setting['form']['ui'])) {
            $formUi = array_merge($formUi, $this->setting['form']['ui']);
        }

        foreach ($formUi as $k => $v) {
            if ($v === null) {
                echo ' ' . $k;
            } else {
                echo ' ' . $k . '="' . $v . '"';
            }
        }
        ?>>
            <?php
            if (isset($this->setting['headnote'])) {
                echo $this->setting['headnote'];
            }

            $tabHtml = '';
            $tabPosition = 'beforeForm';
            if (isset($this->setting['tab'])) {
                $driver = new \Be\Plugin\Tab\Driver($this->setting['tab']);
                $tabHtml = $driver->getHtml();
                if (isset($this->setting['tab']['position'])) {
                    $tabPosition = $this->setting['tab']['position'];
                }

                $formData[$driver->name] = $driver->value;

                $vueDataX = $driver->getVueData();
                if ($vueDataX) {
                    $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                }

                $vueMethodsX = $driver->getVueMethods();
                if ($vueMethodsX) {
                    $vueMethods = array_merge($vueMethods, $vueMethodsX);
                }
            }

            if ($tabHtml && $tabPosition == 'beforeForm') {
                echo $tabHtml;
            }

            if (isset($this->setting['form']['items']) && count($this->setting['form']['items']) > 0) {
                ?>
                <el-row id="form-items" ref="formItemsRef">
                    <el-col :span="24">
                        <?php
                        foreach ($this->setting['form']['items'] as $item) {
                            $driver = null;
                            if (isset($item['driver'])) {
                                $driverName = $item['driver'];
                                $driver = new $driverName($item);
                            } else {
                                $driver = new \Be\Plugin\Form\Item\FormItemInput($item);
                            }
                            echo $driver->getHtml();

                            if ($driver->name !== null) {
                                $formData[$driver->name] = $driver->getValueString();
                            }

                            $jsX = $driver->getJs();
                            if ($jsX) {
                                $js = array_merge($js, $jsX);
                            }

                            $cssX = $driver->getCss();
                            if ($cssX) {
                                $css = array_merge($css, $cssX);
                            }

                            $vueDataX = $driver->getVueData();
                            if ($vueDataX) {
                                $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                            }

                            $vueMethodsX = $driver->getVueMethods();
                            if ($vueMethodsX) {
                                $vueMethods = array_merge($vueMethods, $vueMethodsX);
                            }

                            $vueHooksX = $driver->getVueHooks();
                            if ($vueHooksX) {
                                foreach ($vueHooksX as $k => $v) {
                                    if (isset($vueHooks[$k])) {
                                        $vueHooks[$k] .= "\r\n" . $v;
                                    } else {
                                        $vueHooks[$k] = $v;
                                    }
                                }
                            }
                        }

                        if (isset($this->setting['form']['actions']) && count($this->setting['form']['actions']) > 0) {
                            $html = '';
                            foreach ($this->setting['form']['actions'] as $key => $item) {
                                if ($key == 'submit') {
                                    if ($item) {
                                        if ($item === true) {
                                            $html .= '<el-button type="primary" icon="el-icon-search" @click="submit" :disabled="loading">查询</el-button> ';
                                            continue;
                                        } elseif (is_string($item)) {
                                            $html .= '<el-button type="primary" icon="el-icon-search" @click="submit" :disabled="loading">' . $item . '</el-button> ';
                                            continue;
                                        }
                                    } else {
                                        continue;
                                    }
                                }

                                $driver = null;
                                if (isset($item['driver'])) {
                                    $driverName = $item['driver'];
                                    $driver = new $driverName($item);
                                } else {
                                    $driver = new \Be\Plugin\Form\Action\FormActionButton($item);
                                }
                                $html .= $driver->getHtml() . ' ';

                                $vueDataX = $driver->getVueData();
                                if ($vueDataX) {
                                    $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                                }

                                $vueMethodsX = $driver->getVueMethods();
                                if ($vueMethodsX) {
                                    $vueMethods = array_merge($vueMethods, $vueMethodsX);
                                }
                            }

                            if ($html) {
                                echo '<el-form-item>' . $html . '</el-form-item>';
                            }
                        }
                        ?>
                    </el-col>
                </el-row>
                <?php
            }

            if ($tabHtml && $tabPosition == 'beforeToolbar') {
                echo $tabHtml;
            }

            if (isset($this->setting['toolbar']['items']) && count($this->setting['toolbar']['items']) > 0) {
                echo '<el-row id="toolbar-items" ref="toolbarItemsRef"><el-col :span="24">';
                foreach ($this->setting['toolbar']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Toolbar\Item\ToolbarItemButton($item);
                    }
                    $toolbarItemDriverNames[] = $driver->name;

                    echo '<el-form-item>';
                    echo $driver->getHtml() . "\r\n";
                    echo '</el-form-item>';

                    $vueDataX = $driver->getVueData();
                    if ($vueDataX) {
                        $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                    }

                    $vueMethodsX = $driver->getVueMethods();
                    if ($vueMethodsX) {
                        $vueMethods = array_merge($vueMethods, $vueMethodsX);
                    }
                }
                echo '</el-col></el-row>';
            }

            if ($tabHtml && $tabPosition == 'beforeTable') {
                echo $tabHtml;
            }
            ?>

            <el-table<?php
            $tableUi = [
                ':data' => 'tableData',
                'ref' => 'tableRef',
                'v-loading' => 'loading',
                'size' => 'mini',
                ':height' => 'tableHeight',
                ':default-sort' => '{prop:orderBy,order:orderByDir}',
                '@sort-change' => 'sort',
                '@selection-change' => 'selectionChange',
            ];
            if (isset($this->setting['table']['ui'])) {
                $tableUi = array_merge($tableUi, $this->setting['table']['ui']);
            }

            foreach ($tableUi as $k => $v) {
                if ($v === null) {
                    echo ' ' . $k;
                } else {
                    echo ' ' . $k . '="' . $v . '"';
                }
            }
            ?>>
                <?php
                $opHtml = null;
                $opPosition = 'right';
                if (isset($this->setting['operation'])) {

                    $operationDriver = new \Be\Plugin\Operation\Wrap($this->setting['operation']);
                    $opHtml = $operationDriver->getHtmlBefore();

                    if (isset($this->setting['operation']['items'])) {
                        foreach ($this->setting['operation']['items'] as $item) {
                            $driver = null;
                            if (isset($item['driver'])) {
                                $driverName = $item['driver'];
                                $driver = new $driverName($item);
                            } else {
                                $driver = new \Be\Plugin\Operation\Item\OperationItemLink($item);
                            }
                            $opHtml .= $driver->getHtml();

                            $vueDataX = $driver->getVueData();
                            if ($vueDataX) {
                                $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                            }

                            $vueMethodsX = $driver->getVueMethods();
                            if ($vueMethodsX) {
                                $vueMethods = array_merge($vueMethods, $vueMethodsX);
                            }
                        }
                    }

                    $opHtml .= $operationDriver->getHtmlAfter();
                    $opPosition = $operationDriver->position;

                    if ($opPosition == 'left') {
                        echo $opHtml;
                    }
                }

                foreach ($this->setting['table']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Table\Item\TableItemText($item);
                    }
                    echo $driver->getHtml();

                    $vueDataX = $driver->getVueData();
                    if ($vueDataX) {
                        $vueData = \Be\Util\Arr::merge($vueData, $vueDataX);
                    }

                    $vueMethodsX = $driver->getVueMethods();
                    if ($vueMethodsX) {
                        $vueMethods = array_merge($vueMethods, $vueMethodsX);
                    }
                }

                if (isset($this->setting['operation']) && $opPosition == 'right') {
                    echo $opHtml;
                }
                ?>
            </el-table>

            <?php
            if (isset($this->setting['footnote'])) {
                echo $this->setting['footnote'];
            }
            ?>

            <div style="text-align: center; padding: 10px 10px 0 10px;" v-if="total > 0">
                <el-pagination
                        @size-change="changePageSize"
                        @current-change="gotoPage"
                        :current-page="page"
                        :page-sizes="[10, 15, 20, 25, 30, 50, 100, 200, 500]"
                        :page-size="pageSize"
                        layout="total, sizes, prev, pager, next, jumper"
                        :total="total">
                </el-pagination>
            </div>
        </el-form>

        <el-dialog
                :title="dialog.title"
                :visible.sync="dialog.visible"
                :width="dialog.width"
                :close-on-click-modal="false"
                :destroy-on-close="true">
            <iframe id="frame-dialog" name="frame-dialog" src="about:blank"
                    :style="{width:'100%',height:dialog.height,border:0}"></iframe>
        </el-dialog>

        <el-drawer
                :visible.sync="drawer.visible"
                :size="drawer.width"
                :title="drawer.title"
                :wrapper-closable="false"
                :destroy-on-close="true">
            <iframe id="frame-drawer" name="frame-drawer" src="about:blank"
                    style="width:100%;height:100%;border:0;margin:0 10px;"></iframe>
        </el-drawer>

    </div>

    <?php
    if (isset($this->setting['js'])) {
        $js = array_merge($js, $this->setting['js']);
    }

    if (isset($this->setting['css'])) {
        $css = array_merge($css, $this->setting['css']);
    }

    if (isset($this->setting['vueData'])) {
        $vueData = \Be\Util\Arr::merge($vueData, $this->setting['vueData']);
    }

    if (isset($this->setting['vueMethods'])) {
        $vueMethods = \Be\Util\Arr::merge($vueMethods, $this->setting['vueMethods']);
    }

    if (isset($this->setting['vueHooks'])) {
        foreach ($this->setting['vueHooks'] as $k => $v) {
            if (isset($vueHooks[$k])) {
                $vueHooks[$k] .= "\r\n" . $v;
            } else {
                $vueHooks[$k] = $v;
            }
        }
    }

    if (count($js) > 0) {
        $js = array_unique($js);
        foreach ($js as $x) {
            echo '<script src="' . $x . '"></script>';
        }
    }

    if (count($css) > 0) {
        $css = array_unique($css);
        foreach ($css as $x) {
            echo '<link rel="stylesheet" href="' . $x . '">';
        }
    }
    ?>

    <script>
        var pageSizeKey = "<?php echo $this->url; ?>:pageSize";
        var pageSize = localStorage.getItem(pageSizeKey);
        if (pageSize == undefined || isNaN(pageSize)) {
            pageSize = <?php echo $this->pageSize; ?>;
        } else {
            pageSize = Number(pageSize);
        }

        var vueLists = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                tableData: [],
                orderBy: "",
                orderByDir: "",
                pageSize: pageSize,
                page: 1,
                pages: 1,
                total: 0,
                selectedRows: [],
                loading: false,
                tableHeight: 500,
                dialog: {visible: false, width: "600px", height: "400px", title: ""},
                drawer: {visible: false, width: "40%", title: ""}<?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                submit: function () {
                    this.page = 1;
                    this.loadTableData();
                },
                loadTableData: function () {
                    this.loading = true;
                    var _this = this;
                    _this.$http.post("<?php echo $this->setting['form']['action']; ?>", {
                        formData: _this.formData,
                        orderBy: _this.orderBy,
                        orderByDir: _this.orderByDir,
                        page: _this.page,
                        pageSize: _this.pageSize
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status == 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.total = parseInt(responseData.data.total);
                                _this.tableData = responseData.data.tableData;
                                _this.pages = Math.floor(_this.total / _this.pageSize);
                            } else {
                                _this.total = 0;
                                _this.tableData = [];
                                _this.page = 1;
                                _this.pages = 1;

                                if (responseData.message) {
                                    _this.$message({
                                        showClose: true,
                                        message: responseData.message,
                                        type: 'error'
                                    });
                                }
                            }
                            _this.resize();
                            _this.updateToolbars();
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                reloadTableData: function () {
                    var _this = this;
                    _this.$http.post("<?php echo $this->setting['form']['action']; ?>", {
                        formData: _this.formData,
                        orderBy: _this.orderBy,
                        orderByDir: _this.orderByDir,
                        page: _this.page,
                        pageSize: _this.pageSize
                    }).then(function (response) {
                        if (response.status == 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.total = parseInt(responseData.data.total);
                                _this.tableData = responseData.data.tableData;
                                _this.pages = Math.floor(_this.total / _this.pageSize);
                            }
                            _this.resize();
                            _this.updateToolbars();
                        }
                    });
                },
                changePageSize: function (pageSize) {
                    this.pageSize = pageSize;
                    this.page = 1;
                    localStorage.setItem(pageSizeKey, pageSize);
                    this.loadTableData();
                },
                gotoPage: function (page) {
                    this.page = page;
                    this.loadTableData();
                },
                sort: function (option) {
                    if (option.order == "ascending" || option.order == "descending") {
                        this.orderBy = option.prop;
                        this.orderByDir = option.order == "ascending" ? "ASC" : "DESC";
                    } else {
                        this.orderBy = "";
                        this.orderByDir = "";
                    }
                    this.loadTableData();
                },
                formAction: function (name, option) {
                    var data = {
                        formData: this.formData,
                        orderBy: this.orderBy,
                        orderByDir: this.orderByDir,
                        page: this.page,
                        pageSize: this.pageSize
                    };

                    data.postData = option.postData;
                    data.selectedRows = this.selectedRows;
                    return this.action(option, data);
                },
                toolbarItemAction: function (name, option) {
                    var data = {
                        formData: this.formData,
                        orderBy: this.orderBy,
                        orderByDir: this.orderByDir,
                        page: this.page,
                        pageSize: this.pageSize
                    };

                    data.postData = option.postData;
                    data.selectedRows = this.selectedRows;
                    return this.action(option, data);
                },
                tableItemAction: function (name, option, row) {
                    switch (option.target) {
                        case "dialog":
                            option.dialog.title = row[name];
                            break;
                        case "drawer":
                            option.drawer.title = row[name];
                            break;
                    }

                    var data = {};
                    data.postData = option.postData;
                    data.row = row;
                    return this.action(option, data);
                },
                operationItemAction: function (name, option, row) {
                    var data = {};
                    data.postData = option.postData;
                    data.row = row;
                    return this.action(option, data);
                },
                action: function (option, data) {
                    if (option.target == 'ajax') {
                        var _this = this;
                        this.$http.post(option.url, data).then(function (response) {
                            if (response.status == 200) {
                                if (response.data.success) {
                                    _this.$message({
                                        showClose: true,
                                        message: response.data.message,
                                        type: 'success'
                                    });
                                } else {
                                    if (response.data.message) {
                                        _this.$message({
                                            showClose: true,
                                            message: response.data.message,
                                            type: 'error'
                                        });
                                    }
                                }
                                _this.loadTableData();
                            }
                        }).catch(function (error) {
                            _this.$message({
                                showClose: true,
                                message: error,
                                type: 'error'
                            });
                            _this.loadTableData();
                        });
                    } else {
                        var eForm = document.createElement("form");
                        eForm.action = option.url;
                        switch (option.target) {
                            case "self":
                            case "_self":
                                eForm.target = "_self";
                                break;
                            case "blank":
                            case "_blank":
                                eForm.target = "_blank";
                                break;
                            case "dialog":
                                eForm.target = "frame-dialog";
                                this.dialog.title = option.dialog.title;
                                this.dialog.width = option.dialog.width;
                                this.dialog.height = option.dialog.height;
                                this.dialog.visible = true;
                                break;
                            case "drawer":
                                eForm.target = "frame-drawer";
                                this.drawer.title = option.drawer.title;
                                this.drawer.width = option.drawer.width;
                                this.drawer.visible = true;
                                break;
                        }
                        eForm.method = "post";
                        eForm.style.display = "none";

                        var e = document.createElement("textarea");
                        e.name = 'data';
                        e.value = JSON.stringify(data);
                        eForm.appendChild(e);

                        document.body.appendChild(eForm);

                        setTimeout(function () {
                            eForm.submit();
                        }, 50);

                        setTimeout(function () {
                            document.body.removeChild(eForm);
                        }, 3000);
                    }

                    return false;
                },
                hideDialog: function () {
                    this.dialog.visible = false;
                },
                hideDrawer: function () {
                    this.drawer.visible = false;
                },
                selectionChange: function (rows) {
                    this.selectedRows = rows;
                    this.updateToolbars();
                },
                updateToolbars: function () {
                    var toolbarEnable;
                    <?php
                    if (isset($this->setting['toolbar']['items']) && count($this->setting['toolbar']['items']) > 0) {
                        $i = 0;
                        foreach ($this->setting['toolbar']['items'] as $item) {
                            if (isset($item['task']) && $item['task'] == 'fieldEdit' && isset($item['postData']['field']) && isset($item['postData']['value'])) {
                            ?>
                            if (this.selectedRows.length > 0) {
                                toolbarEnable = true;
                                for (var x in this.selectedRows) {
                                    if (this.selectedRows[x].<?php echo $item['postData']['field']; ?> == "<?php echo $item['postData']['value']; ?>") {
                                        toolbarEnable = false;
                                    }
                                }
                            } else {
                                toolbarEnable = false;
                            }
                            this.toolbarItems.<?php echo $toolbarItemDriverNames[$i]; ?>.enable = toolbarEnable;
                            <?php
                            }
                            $i++;
                        }
                    }
                    ?>
                },
                resize: function () {
                    var offset = this.total > 0 ? 55 : 15;
                    this.tableHeight = document.documentElement.clientHeight - this.$refs.tableRef.$el.offsetTop - offset;
                }
                <?php
                if ($vueMethods) {
                    foreach ($vueMethods as $k => $v) {
                        echo ',' . $k . ':' . $v;
                    }
                }
                ?>
            },
            created: function () {
                this.submit();
                <?php
                if (isset($this->setting['reload']) && is_numeric($this->setting['reload'])) {
                    echo 'var _this = this;';
                    echo 'setInterval(function () {_this.reloadTableData();}, ' . ($this->setting['reload'] * 1000) . ');';
                }

                if (isset($vueHooks['created'])) {
                    echo $vueHooks['created'];
                }
                ?>
            },
            mounted: function () {
                this.$nextTick(function () {
                    this.resize();
                    var _this = this;
                    window.onresize = function () {
                        _this.resize();
                    };
                });

                <?php
                if (isset($vueHooks['mounted'])) {
                    echo $vueHooks['mounted'];
                }
                ?>
            },
            updated: function () {
                this.$nextTick(function () {
                    this.$refs['tableRef'].doLayout();
                });
                <?php
                if (isset($vueHooks['updated'])) {
                    echo $vueHooks['updated'];
                }
                ?>
            }
            <?php
            if (isset($vueHooks['beforeCreate'])) {
                echo ',beforeCreate: function () {' . $vueHooks['beforeCreate'] . '}';
            }

            if (isset($vueHooks['beformMount'])) {
                echo ',beformMount: function () {' . $vueHooks['beformMount'] . '}';
            }

            if (isset($vueHooks['beforeUpdate'])) {
                echo ',beforeUpdate: function () {' . $vueHooks['beforeUpdate'] . '}';
            }


            if (isset($vueHooks['beforeDestroy'])) {
                echo ',beforeDestroy: function () {' . $vueHooks['beforeDestroy'] . '}';
            }

            if (isset($vueHooks['destroyed'])) {
                echo ',destroyed: function () {' . $vueHooks['destroyed'] . '}';
            }
            ?>
        });

        function reload() {
            vueLists.loadTableData();
        }

        function close() {
            vueLists.drawer.visible = false;
            vueLists.dialog.visible = false;
        }

        function closeDrawer() {
            vueLists.drawer.visible = false;
        }

        function closeDialog() {
            vueLists.dialog.visible = false;
        }

        function closeAndReload() {
            vueLists.drawer.visible = false;
            vueLists.dialog.visible = false;
            vueLists.loadTableData();
        }

        function closeDrawerAndReload() {
            vueLists.drawer.visible = false;
            vueLists.loadTableData();
        }

        function closeDialogAndReload() {
            vueLists.dialog.visible = false;
            vueLists.loadTableData();
        }

    </script>

                </div>
            </div>
            </be-middle>
        </div>

    </div>

    <script>
        <?php
        $menuKey = Be::getRequest()->route();
        ?>
        var vueNorth = new Vue({
            el: '#be-north',
            data: {
                defaultActive: "north-menu-<?php echo $menuKey; ?>",
                aboutModel: false
            },
            methods: {

            }
        });


        var sWestMenuCollapseKey = '_westMenuCollapse';
        var vueWestMenu = new Vue({
            el: '#app-west',
            data : {
                activeIndex: "west-menu-<?php echo $menuKey; ?>",
                collapse: this.$cookies.isKey(sWestMenuCollapseKey) && this.$cookies.get(sWestMenuCollapseKey) == '1'
            },
            methods: {
                toggleMenu: function (e) {
                    this.collapse = !this.collapse;
                    console.log(this.collapse);
                    document.getElementById("be-middle").style.left = this.collapse ? "48px" : "200px";
                    this.$cookies.set(sWestMenuCollapseKey, this.collapse ? '1' : '0', 86400 * 180);
                }
            },
            created: function () {
                if (this.collapse) {
                    document.getElementById("be-middle").style.left = "48px";
                }
            }
        });

    </script>

    </be-body>
</body>
</html>
    <?php
  }
}

