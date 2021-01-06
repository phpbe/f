<?php
namespace Be\Cache\System\Template\Admin\Plugin\Config;

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

        <el-tabs tab-position="left" value="<?php echo $this->configName; ?>" @tab-click="goto">
            <?php
            foreach ($this->configs as $config) {
                ?>
                <el-tab-pane name="<?php echo $config['name']; ?>" label="<?php echo $config['label']; ?>">
                    <?php
                    if ($config['name'] == $this->configName) {
                        if (count($this->configItemDrivers)) {
                            ?>
                            <div style="max-width: 800px;">
                                <el-form size="small" label-width="200px" :disabled="loading">
                                    <?php
                                    foreach ($this->configItemDrivers as $driver) {

                                        echo $driver->getHtml();

                                        if (is_array($driver->value) || is_object($driver->value)) {
                                            $formData[$driver->name] =  json_encode($driver->value, JSON_PRETTY_PRINT);
                                        } else {
                                            $formData[$driver->name] = $driver->value;
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
                                    ?>
                                    <el-form-item>
                                        <el-button type="success" icon="el-icon-check" @click="saveConfig">保存</el-button>
                                        <el-button type="danger" icon="el-icon-close" @click="resetConfig">恢复默认值</el-button>
                                        <?php if (isset($config['test'])) { ?>
                                            <el-button icon="el-icon-view" @click="window.open('<?php echo $config['test']; ?>');">测试</el-button>
                                        <?php } ?>
                                    </el-form-item>
                                </el-form>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </el-tab-pane>
                <?php
            }
            ?>
        </el-tabs>
    </div>

    <?php
    if (count($js) > 0) {
        $js = array_unique($js);
        foreach ($js as $x) {
            echo '<script src="'.$x.'"></script>';
        }
    }

    if (count($css) > 0) {
        $css = array_unique($css);
        foreach ($css as $x) {
            echo '<link rel="stylesheet" href="'.$x.'">';
        }
    }
    ?>

    <script>
        var app = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,
                loading: false<?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                saveConfig: function () {
                    this.loading = true;
                    var _this = this;
                    _this.$http.post("<?php echo beUrl(null, ['task' => 'saveConfig', 'configName' => $this->configName]); ?>", {
                        formData: _this.formData
                    }).then(function (response) {
                            _this.loading = false;
                            if (response.status == 200) {
                                if (response.data.success) {
                                    _this.$message.success(response.data.message);
                                } else {
                                    _this.$message.error(response.data.message);
                                }
                            }
                        }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },

                resetConfig: function () {
                    var _this = this;
                    this.$confirm('该操作不可恢复，确认恢复默认值吗？', '确认恢复默认值吗', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(function () {
                        _this.loading = true;
                        _this.$http.get("<?php echo beUrl(null, ['task' => 'resetConfig', 'configName' => $this->configName]); ?>")
                            .then(function (response) {
                                _this.loading = false;
                                if (response.status == 200) {
                                    if (response.data.success) {
                                        _this.$message.success(response.data.message);
                                        window.location.reload();
                                    } else {
                                        _this.$message.error(response.data.message);
                                    }
                                }
                            })
                            .catch(function (error) {
                                _this.loading = false;
                                _this.$message.error(error);
                            });
                    }).catch(function () {
                        _this.loading = false;
                    });
                },
                goto: function (tab) {
                    var sUrl = "<?php echo beUrl(\Be\System\Be::getRequest()->route()); ?>";
                    sUrl += sUrl.indexOf("?") >= 0 ? "&" : "?";
                    sUrl += "configName=" + tab.name;
                    window.location.href = sUrl;
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
                echo ',beforeCreate: function () {'.$vueHooks['beforeCreate'].'}';
            }

            if (isset($vueHooks['created'])) {
                echo ',created: function () {'.$vueHooks['created'].'}';
            }

            if (isset($vueHooks['beforeMount'])) {
                echo ',beforeMount: function () {'.$vueHooks['beforeMount'].'}';
            }

            if (isset($vueHooks['mounted'])) {
                echo ',mounted: function () {'.$vueHooks['mounted'].'}';
            }

            if (isset($vueHooks['beforeUpdate'])) {
                echo ',beforeUpdate: function () {'.$vueHooks['beforeUpdate'].'}';
            }

            if (isset($vueHooks['updated'])) {
                echo ',updated: function () {'.$vueHooks['updated'].'}';
            }

            if (isset($vueHooks['beforeDestroy'])) {
                echo ',beforeDestroy: function () {'.$vueHooks['beforeDestroy'].'}';
            }

            if (isset($vueHooks['destroyed'])) {
                echo ',destroyed: function () {'.$vueHooks['destroyed'].'}';
            }
            ?>
        });
    </script>

                </div>
            </div>
            </be-middle>
        </div>

    </div>

    <script>
        <?php
        $request = Be::getRequest();
        $menuKey = $request->app() . '.' . $request->controller() . '.' . $request->action();
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

