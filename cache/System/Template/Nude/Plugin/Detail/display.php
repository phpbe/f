<?php
namespace Be\Cache\System\Template\Nude\Plugin\Detail;

use Be\System\Be;

class display extends \Be\System\Template
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

    <be-head>
    </be-head>
</head>
<body>
    <be-body>
    <div class="be-body">

        
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
            if (isset($this->setting['headnote'])) {
                echo $this->setting['headnote'];
            }

            if (isset($this->setting['form']['items']) && count($this->setting['form']['items']) > 0) {
                foreach ($this->setting['form']['items'] as $item) {
                    $driver = null;
                    if (isset($item['driver'])) {
                        $driverName = $item['driver'];
                        $driver = new $driverName($item);
                    } else {
                        $driver = new \Be\Plugin\Detail\Item\DetailItemText($item);
                    }
                    echo $driver->getHtml();

                    $formData[$driver->name] = $driver->value;

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
                        if ($key == 'cancel') {
                            if ($item) {
                                if ($item === true) {
                                    echo '<el-button type="primary" @click="cancel">关闭</el-button> ';
                                    continue;
                                } elseif (is_string($item)) {
                                    echo '<el-button type="primary" @click="cancel">' . $item . '</el-button> ';
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
            <?php
            if (isset($this->setting['footnote'])) {
                echo $this->setting['footnote'];
            }
            ?>
        </el-form>
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
        var vueDetail = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>
                <?php
                if ($vueData) {
                    foreach ($vueData as $k => $v) {
                        echo ',' . $k . ':' . json_encode($v);
                    }
                }
                ?>
            },
            methods: {
                cancel: function () {
                    if(self.frameElement != null && (self.frameElement.tagName == "IFRAME" || self.frameElement.tagName == "iframe")){
                        parent.close();
                    } else {
                        window.close();
                    }
                },
                formAction: function (name, option) {
                    var data = {};
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

            if (isset($vueHooks['beformMount'])) {
                echo ',beformMount: function () {'.$vueHooks['beformMount'].'}';
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
    </be-body>
</body>
</html>
    <?php
  }
}

