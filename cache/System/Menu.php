<?php
namespace Be\Cache\System;

class Menu extends \Be\System\Menu
{
  public function __construct()
  {
    $this->addMenu('System', '0', 'el-icon-s-tools','系统', '', '');
    $this->addMenu('System.UserLoginLog','System','el-icon-fa fa-user','用户', '', '');
    $this->addMenu('System.User.users', 'System.UserLoginLog', 'el-icon-fa fa-users', '用户管理', beUrl('System.User.users'), '');
    $this->addMenu('System.Role.roles', 'System.UserLoginLog', 'el-icon-fa fa-user-secret', '角色管理', beUrl('System.Role.roles'), '');
    $this->addMenu('System.UserLoginLog.logs', 'System.UserLoginLog', 'el-icon-fa fa-user-circle', '用户登录日志', beUrl('System.UserLoginLog.logs'), '');
    $this->addMenu('System.UserReport','System','el-icon-fa fa-user','用户报表', '', '');
    $this->addMenu('System.UserReport.users', 'System.UserReport', 'el-icon-fa fa-users', '用户报表', beUrl('System.UserReport.users'), '');
    $this->addMenu('System.Watermark','System','el-icon-setting','系统配置', '', '');
    $this->addMenu('System.Config.dashboard', 'System.Watermark', 'el-icon-setting', '系统配置', beUrl('System.Config.dashboard'), '');
    $this->addMenu('System.Mail.test', 'System.Watermark', 'el-icon-fa fa-envelope-o', '发送邮件测试', beUrl('System.Mail.test'), '');
    $this->addMenu('System.Watermark.test', 'System.Watermark', 'el-icon-fa fa-image', '水印测试', beUrl('System.Watermark.test'), '');
    $this->addMenu('System.Theme','System','el-icon-fa fa-cube','管理', '', '');
    $this->addMenu('System.App.apps', 'System.Theme', 'el-icon-fa fa-cubes', '应用', beUrl('System.App.apps'), '');
    $this->addMenu('System.Cache.index', 'System.Theme', 'el-icon-arrow-right', '缓存', beUrl('System.Cache.index'), '');
    $this->addMenu('System.Task.tasks', 'System.Theme', 'el-icon-arrow-right', '计划任务', beUrl('System.Task.tasks'), '');
    $this->addMenu('System.Theme.themes', 'System.Theme', 'el-icon-fa fa-cubes', '主题', beUrl('System.Theme.themes'), '');
    $this->addMenu('System.OpLog','System','el-icon-folder','日志', '', '');
    $this->addMenu('System.Log.lists', 'System.OpLog', 'el-icon-fa fa-video-camera', '系统日志', beUrl('System.Log.lists'), '');
    $this->addMenu('System.OpLog.logs', 'System.OpLog', 'el-icon-fa fa-video-camera', '操作日志', beUrl('System.OpLog.logs'), '');
  }
}
