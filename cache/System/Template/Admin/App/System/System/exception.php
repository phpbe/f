<?php
namespace Be\Cache\System\Template\Admin\App\System\System;

use Be\System\Be;
use Be\System\Session;

class exception extends \Be\System\Template
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
    
    <link type="text/css" rel="stylesheet"
          href="<?php echo \Be\System\Be::getProperty('App.System')->url(); ?>/Template/System/css/exception.css">
    <link rel="stylesheet"
          href="<?php echo \Be\System\Be::getProperty('App.System')->url(); ?>/Template/System/google-code-prettify/prettify.css"
          type="text/css"/>
    <script type="text/javascript" language="javascript"
            src="<?php echo \Be\System\Be::getProperty('App.System')->url(); ?>/Template/System/google-code-prettify/prettify.js"></script>
    <style type="text/css">
        pre.prettyprint {
            background-color: #fff;
            color: #000;
            white-space: pre-wrap;
            word-wrap: break-word;
            border-color: #ddd;
        }
    </style>

</head>
<body>
    

    <div id="app" v-cloak>
        <?php
        $configSystem = \Be\System\Be::getConfig('System.System');
        if ($configSystem->developer) {
            ?>
            <el-alert
                    title="<?php echo $this->e->getMessage(); ?>"
                    type="error"
                    description="<?php echo '#' . $this->logHash; ?>"
                    show-icon>
            </el-alert>

            <el-tabs v-model="activeTab" type="border-card">
                <el-tab-pane label="错误跟踪信息" name="tab-trace">
                    <pre class="prettyprint linenums"><?php print_r($this->e->getTrace()); ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_SERVER" name="tab-server">
                    <pre class="prettyprint linenums"><?php print_r($_SERVER) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_GET" name="tab-get">
                    <pre class="prettyprint linenums"><?php print_r($_GET) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_POST" name="tab-post">
                    <pre class="prettyprint linenums"><?php print_r($_POST) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_REQUEST" name="tab-request">
                    <pre class="prettyprint linenums"><?php print_r($_REQUEST) ?></pre>
                </el-tab-pane>
                <el-tab-pane label="$_COOKIE" name="tab-cookie">
                    <pre class="prettyprint linenums"><?php print_r($_COOKIE) ?></pre>
                </el-tab-pane>
            </el-tabs>
            <?php
        } else {
            ?>
            <div class="exception-icon">
                <i class="el-icon-warning"></i>
            </div>

            <div class="exception-message">
                <?php echo $this->e->getMessage(); ?>
            </div>
            <?php
        }
        ?>

    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                activeTab: 'tab-trace'
            },
            created: function () {
                prettyPrint();
            }
        });
    </script>


</body>
</html>
    <?php
  }
}

