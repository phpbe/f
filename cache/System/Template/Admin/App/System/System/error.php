<?php
namespace Be\Cache\System\Template\Admin\App\System\System;

use Be\System\Be;
use Be\System\Session;

class error extends \Be\System\Template
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
    
    <link type="text/css" rel="stylesheet" href="<?php echo \Be\System\Be::getProperty('App.System')->url(); ?>/Template/System/css/error.css">

</head>
<body>
    

    <div id="app" v-cloak>
        <div class="error-icon">
            <i class="el-icon-warning"></i>
        </div>

        <div class="error-message">
            <?php echo $this->message; ?>
        </div>

        <?php
        if (isset($this->redirectUrl) && isset($this->redirectTimeout) && $this->redirectTimeout > 0 )
        {
            ?>
            <div class="error-timer">
                <span>{{timer}}</span> 秒后跳转到：<el-link type="primary" href="<?php echo $this->redirectUrl; ?>"><?php echo $this->redirectUrl; ?></el-link>
            </div>
            <?php
        }
        ?>
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                timer: <?php echo isset($this->redirectTimeout) ? $this->redirectTimeout : 0; ?>
            },
            created: function () {
                <?php
                if (isset($this->redirectUrl)) {
                if (isset($this->redirectTimeout) && $this->redirectTimeout > 0) {
                ?>
                var _this = this;
                setInterval(function () {
                    _this.timer--;
                    if (_this.timer <= 0) {
                        window.location.href = "<?php echo $this->redirectUrl; ?>";
                    }
                }, 1000);
                <?php
                } else {
                ?>
                window.location.href = "<?php echo $this->redirectUrl; ?>";
                <?php
                }
                }
                ?>
            }
        });
    </script>


</body>
</html>
    <?php
  }
}

