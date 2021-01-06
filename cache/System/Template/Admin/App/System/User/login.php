<?php
namespace Be\Cache\System\Template\Admin\App\System\User;

use Be\System\Be;
use Be\System\Session;

class login extends \Be\System\Template
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
    
<link type="text/css" rel="stylesheet" href="<?php echo Be::getProperty('App.System')->url(); ?>/Template/User/css/login.css" />

</head>
<body>
    
<?php
$config = Be::getConfig('System.System');
?>
<div id="app">

    <div class="logo"></div>

    <div class="login-box">
        <el-form size="small" layout="horizontal" ref="loginForm" :model="formData" label-width="80px">
            <el-form-item label="用户名" prop="username">
                <el-input v-model="formData.username" placeholder="用户名" prefix-icon="el-icon-user" clearable></el-input>
            </el-form-item>
            <el-form-item label="密码" prop="password">
                <el-input v-model="formData.password" placeholder="密码" prefix-icon="el-icon-lock" show-password clearable></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" native-type="submit" @click="login" :loading="loginLoading">
                    <el-icon type="unlock"></el-icon>登录
                </el-button>
                <el-button @click="resetForm">重置</el-button>
            </el-form-item>
        </el-form>
    </div>

</div>

<?php
$return = Be::getRequest()->get('return', '');
if ($return=='') {
    $return = beUrl('System.System.dashboard');
} else {
    $return = base64_decode($return);
}
?>
<script>
    new Vue({
        el: '#app',
        data: {
            formData: {
                username : "",
                password : ""
            },
            loginLoading: false
        },
        methods: {
            login: function() {
                var _this = this;
                _this.loginLoading = true;
                this.$http.post("<?php echo beUrl('System.User.login'); ?>", _this.formData)
                    .then(function (response) {
                        _this.loginLoading = false;
                        if (response.status == 200) {
                            if (response.data.success) {
                                window.location.href = "<?php echo $return; ?>";
                            } else {
                                _this.$message.error(response.data.message);
                            }
                        }
                    })
                    .catch(function (error) {
                        _this.loginLoading = false;
                        _this.$message.error(error);
                    });

            },
            resetForm: function () {
                this.$refs["loginForm"].resetFields();
            }
        }
    });
</script>


</body>
</html>
    <?php
  }
}

