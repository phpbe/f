<?php
namespace Be\Cache\System\Template\Admin\App\System\System;

use Be\System\Be;
use Be\System\Session;

class dashboard extends \Be\System\Template
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
    
<link type="text/css" rel="stylesheet" href="<?php echo Be::getProperty('App.System')->url(); ?>/Template/System/css/dashboard.css">
<script type="text/javascript" language="javascript" src="<?php echo Be::getProperty('App.System')->url(); ?>/Template/System/js/dashboard.js"></script>

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
$my = Be::getUser();
$user = $this->user;

$configUser = Be::getConfig('System.User');
?>
<div id="app">

    <el-row :gutter="20">
        <el-col :span="12">

            <el-card shadow="hover" style="height: 180px;">
                <el-image src="<?php
                if ($this->user->avatar == '') {
                    echo Be::getProperty('App.System')->url().'/Template/User/images/avatar.png';
                } else {
                    echo Be::getRequest()->dataUrl().'/System/User/Avatar/'.$this->user->avatar;
                }
                ?>"></el-image>

                <div style="font-size:14px; font-weight: bold;"><?php echo $this->user->name; ?>（<?php echo $my->getRoleName(); ?>）</div>
                <div style="color: #999;font-size: 12px;">上次登陆时间：<?php echo $this->user->last_login_time; ?></div>
            </el-card>

        </el-col>

        <el-col :span="4">
            <el-card shadow="hover" style="height: 180px; text-align:center;">
                <div slot="header" class="clearfix">
                    <span>应用数</span>
                </div>

                <el-link href="<?php echo beUrl('System.App.apps'); ?>" style="font-size:36px; ">
                    <?php echo $this->appCount; ?>
                </el-link>
            </el-card>
        </el-col>


        <el-col :span="4">
            <el-card shadow="hover" style="height: 180px; text-align:center;">
                <div slot="header" class="clearfix">
                    <span>主题数</span>
                </div>

                <el-link href="<?php echo beUrl('System.Theme.themes'); ?>" style="font-size:36px; ">
                    <?php echo $this->themeCount;; ?>
                </el-link>
            </el-card>
        </el-col>


        <el-col :span="4">
            <el-card shadow="hover" style="height: 180px; text-align:center;">
                <div slot="header" class="clearfix">
                    <span>用户数</span>
                </div>

                <el-link href="<?php echo beUrl('System.User.users'); ?>" style="font-size:36px; ">
                    <?php echo $this->userCount; ?>
                </el-link>
            </el-card>
        </el-col>

    </el-row>




    <el-row :gutter="20" style="margin-top: 20px;">
        <el-col :span="12">

            <el-card shadow="hover">
                <div slot="header" class="clearfix">
                    <span>最近操作日志</span>
                    <el-button style="float: right; padding: 3px 0" type="text" @click="window.location.href='<?php echo beUrl('System.SystemLog.logs')?>'">更多..</el-button>
                </div>

                <el-table :data="recentLogs" stripe size="mini">
                    <el-table-column
                            prop="create_time"
                            label="时间"
                            width="180"
                            align="center">
                        <template slot-scope="scope">
                            <div v-html="scope.row.create_time"></div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="content"
                            label="操作">
                    </el-table-column>
                </el-table>

            </el-card>

        </el-col>

        <el-col :span="12">

            <el-card shadow="hover">
                <div slot="header" class="clearfix">
                    <span>最近登录日志</span>
                    <el-button style="float: right; padding: 3px 0" type="text" @click="window.location.href='<?php echo beUrl('System.UserLoginLog.logs')?>'">更多..</el-button>
                </div>

                <el-table :data="recentLoginLogs" stripe size="mini">
                    <el-table-column
                            prop="create_time"
                            label="时间"
                            width="180"
                            align="center">
                        <template slot-scope="scope">
                            <div v-html="scope.row.create_time"></div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="description"
                            label="操作">
                    </el-table-column>
                </el-table>

            </el-card>

        </el-col>
    </el-row>


</div>

<?php
foreach ($this->recentLogs as $log) {
    $log->create_time = date('Y-m-d H:i', strtotime($log->create_time));
}

foreach ($this->recentLoginLogs as $log) {
    $log->create_time = date('Y-m-d H:i', strtotime($log->create_time));
}
?>
<script>
    var vue = new Vue({
        el: '#app',
        data: {
            recentLogs : <?php echo json_encode($this->recentLogs); ?>,
            recentLoginLogs : <?php echo json_encode($this->recentLoginLogs); ?>
        },
        methods: {
        }
    });
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

