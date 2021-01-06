<?php
namespace Be\Cache\System\Template\Nude\App\System\Log;

use Be\System\Be;

class detail extends \Be\System\Template
{

  public function display()
  {

    ?>
<?php
$adminThemeUrl = \Be\System\Be::getProperty('Theme.Admin')->url();
$themeUrl = Be::getProperty('Theme.Admin')->url();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $this->title; ?></title>

    <script src="<?php echo $adminThemeUrl; ?>/js/vue-2.6.11.min.js"></script>

    <script src="<?php echo $adminThemeUrl; ?>/js/axios-0.19.0.min.js"></script>
    <script>Vue.prototype.$http = axios;</script>

    <script src="<?php echo $adminThemeUrl; ?>/js/vue-cookies-1.5.13.js"></script>

    <link rel="stylesheet" href="<?php echo $adminThemeUrl; ?>/css/element-ui-2.13.2.css">
    <script src="<?php echo $adminThemeUrl; ?>/js/element-ui-2.13.2.js"></script>

    <link rel="stylesheet" href="<?php echo $adminThemeUrl; ?>/css/font-awesome-4.7.0.min.css" />

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
    

    <div id="app" v-cloak style="padding: 0 20px;">

        <el-tabs v-model="activeTab">
            <el-tab-pane label="错误基本信息" name="tab-base">
                <el-form label-width="120px" size="mini" style="padding-top: 12px;">
                    <el-form-item label="错误编号">
                        <?php echo $this->log['extra']['hash']; ?>
                    </el-form-item>
                    <el-form-item label="文件">
                        <?php echo $this->log['context']['file']; ?>
                    </el-form-item>
                    <el-form-item label="行号">
                        <?php echo $this->log['context']['line']; ?>
                    </el-form-item>
                    <el-form-item label="错误码">
                        <?php echo $this->log['context']['code']; ?>
                    </el-form-item>
                    <el-form-item label="错误信息">
                        <?php echo $this->log['message']; ?>
                    </el-form-item>
                </el-form>
            </el-tab-pane>

            <el-tab-pane label="错误跟踪信息" name="tab-trace">
                <pre class="prettyprint linenums"><?php print_r($this->log['context']['trace']); ?></pre>
            </el-tab-pane>

            <?php
            $configSystemLog = \Be\System\Be::getConfig('System.Log');

            if (isset($configSystemLog->server) && $configSystemLog->server) {
                ?>
                <el-tab-pane label="$_SERVER" name="tab-server">
                    <pre class="prettyprint linenums"><?php print_r($this->log['extra']['server']); ?></pre>
                </el-tab-pane>
                <?php
            }

            if (isset($configSystemLog->get) && $configSystemLog->get) {
                ?>
                <el-tab-pane label="$_GET" name="tab-get">
                    <pre class="prettyprint linenums"><?php print_r($this->log['extra']['get']); ?></pre>
                </el-tab-pane>
                <?php
            }

            if (isset($configSystemLog->post) && $configSystemLog->post) {
                ?>
                <el-tab-pane label="$_POST" name="tab-post">
                    <pre class="prettyprint linenums"><?php print_r($this->log['extra']['post']); ?></pre>
                </el-tab-pane>
                <?php
            }

            if (isset($configSystemLog->request) && $configSystemLog->request) {
                ?>
                <el-tab-pane label="$_REQUEST" name="tab-request">
                    <pre class="prettyprint linenums"><?php print_r($this->log['extra']['request']); ?></pre>
                </el-tab-pane>
                <?php
            }

            if (isset($configSystemLog->cookie) && $configSystemLog->cookie) {
                ?>
                <el-tab-pane label="$_COOKIE" name="tab-cookie">
                    <pre class="prettyprint linenums"><?php print_r($this->log['extra']['cookie']); ?></pre>
                </el-tab-pane>
                <?php
            }

            if (isset($configSystemLog->session) && $configSystemLog->session) {
                ?>
                <el-tab-pane label="$_SESSION" name="tab-session">
                    <pre class="prettyprint linenums"><?php print_r($this->log['extra']['session']); ?></pre>
                </el-tab-pane>
                <?php
            }
            ?>
        </el-tabs>

    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                activeTab: 'tab-base'
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

