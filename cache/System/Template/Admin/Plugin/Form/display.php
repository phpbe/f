<?php
namespace Be\Cache\System\Template\Admin\Plugin\Form;

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
    <be-head>
    </be-head>
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
    ?>
    <div id="app" v-cloak>

        <el-form<?php
        $formUi = [
            ':model' => 'formData',
            'ref' => 'formRef',
            'size' => 'mini',
            'label-width' => '150px',
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
            if (isset($this->setting['form']['items']) && count($this->setting['form']['items']) > 0) {
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
            }
            ?>

            <el-form-item>
                <?php
                if (isset($this->setting['form']['actions']) && count($this->setting['form']['actions']) > 0) {
                    foreach ($this->setting['form']['actions'] as $key => $item) {
                        if ($key == 'submit') {
                            if ($item) {
                                if ($item === true) {
                                    echo '<el-button type="primary" @click="submit" :disabled="loading" icon="el-icon-check">保存</el-button> ';
                                    continue;
                                } elseif (is_string($item)) {
                                    echo '<el-button type="primary" @click="submit" :disabled="loading" icon="el-icon-check">' . $item . '</el-button> ';
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        } elseif ($key == 'reset') {
                            if ($item) {
                                if ($item === true) {
                                    echo '<el-button type="warning" @click="reset" :disabled="loading" icon="el-icon-refresh-left">重置</el-button> ';
                                    continue;
                                } elseif (is_string($item)) {
                                    echo '<el-button type="warning" @click="reset" :disabled="loading" icon="el-icon-refresh-left">' . $item . '</el-button> ';
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        } elseif ($key == 'cancel') {
                            if ($item) {
                                if ($item === true) {
                                    echo '<el-button @click="cancel" :disabled="loading" icon="el-icon-close">取消</el-button> ';
                                    continue;
                                } elseif (is_string($item)) {
                                    echo '<el-button @click="cancel" :disabled="loading" icon="el-icon-close">' . $item . '</el-button> ';
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
                        echo $driver->getHtml() . ' ';

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
                ?>
            </el-form-item>
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
                    style="width:100%;height:100%;border:0;"></iframe>
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
            echo '<link rel="stylesheet" type="text/css" href="' . $x . '" />';
        }
    }
    ?>

    <script>
        var vueForm = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                loading: false,
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
                    var _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            _this.$http.post("<?php echo $this->setting['form']['action']; ?>", {
                                formData: _this.formData
                            }).then(function (response) {
                                _this.loading = false;
                                //console.log(response);
                                if (response.status == 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        var message;
                                        if (responseData.message) {
                                            message = responseData.message;
                                        } else {
                                            message = '保存成功';
                                        }

                                        alert(message);
                                        if (self.frameElement != null && (self.frameElement.tagName == "IFRAME" || self.frameElement.tagName == "iframe")) {
                                            parent.closeAndReload();
                                        } else {
                                            window.close();
                                        }

                                    } else {
                                        if (responseData.message) {
                                            _this.$message.error(responseData.message);
                                        }
                                    }
                                }
                            }).catch(function (error) {
                                _this.loading = false;
                                _this.$message.error(error);
                            });

                        } else {
                            return false;
                        }
                    });
                },
                formAction: function (name, option) {
                    var data = {};
                    data.formData = this.formData;
                    data.postData = option.postData;
                    return this.action(option, data);
                },
                action: function (option, data) {
                    if (option.target == 'ajax') {
                        var _this = this;
                        this.$http.post(option.url, data).then(function (response) {
                            if (response.status == 200) {
                                if (response.data.success) {
                                    _this.$message.success(response.data.message);
                                } else {
                                    if (response.data.message) {
                                        _this.$message.error(response.data.message);
                                    }
                                }
                            }
                        }).catch(function (error) {
                            _this.$message.error(error);
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
                reset: function () {
                    this.$refs["formRef"].resetFields();
                },
                cancel: function () {
                    if (self.frameElement != null && (self.frameElement.tagName == "IFRAME" || self.frameElement.tagName == "iframe")) {
                        parent.close();
                    } else {
                        window.close();
                    }
                }
                <?php
                if ($vueMethods) {
                    foreach ($vueMethods as $k => $v) {
                        echo ',' . $k . ':' . $v;
                    }
                }
                ?>
            }

            <?php
            if (isset($vueHooks['beforeCreate'])) {
                echo ',beforeCreate: function () {' . $vueHooks['beforeCreate'] . '}';
            }

            if (isset($vueHooks['created'])) {
                echo ',created: function () {' . $vueHooks['created'] . '}';
            }

            if (isset($vueHooks['beforeMount'])) {
                echo ',beforeMount: function () {' . $vueHooks['beforeMount'] . '}';
            }

            if (isset($vueHooks['mounted'])) {
                echo ',mounted: function () {' . $vueHooks['mounted'] . '}';
            }

            if (isset($vueHooks['beforeUpdate'])) {
                echo ',beforeUpdate: function () {' . $vueHooks['beforeUpdate'] . '}';
            }

            if (isset($vueHooks['updated'])) {
                echo ',updated: function () {' . $vueHooks['updated'] . '}';
            }

            if (isset($vueHooks['beforeDestroy'])) {
                echo ',beforeDestroy: function () {' . $vueHooks['beforeDestroy'] . '}';
            }

            if (isset($vueHooks['destroyed'])) {
                echo ',destroyed: function () {' . $vueHooks['destroyed'] . '}';
            }
            ?>
        });

        function close() {
            vueForm.drawer.visible = false;
            vueForm.dialog.visible = false;
        }

        function closeDrawer() {
            vueForm.drawer.visible = false;
        }

        function closeDialog() {
            vueForm.dialog.visible = false;
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

