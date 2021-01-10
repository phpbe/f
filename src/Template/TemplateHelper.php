<?php

namespace Be\Framework\Template;

use Be\Framework\Property\PropertyFactory;
use Be\Framework\Runtime\RuntimeException;
use Be\Framework\Runtime\RuntimeFactory;


class TemplateHelper
{

    /**
     * 更新模板
     *
     * @param string $template 模析名
     * @param string $theme 主题名
     * @throws \Exception
     */
    public static function update($template, $theme)
    {
        $themeProperty = PropertyFactory::getInstance('Theme.' . $theme);
        $runtime = RuntimeFactory::getInstance();

        $fileTheme = $runtime->getRootPath() . $themeProperty->getPath() . '/' . $theme . '.php';
        if (!file_exists($fileTheme)) {
            throw new RuntimeException('主题 ' . $theme . ' 不存在！');
        }

        $parts = explode('.', $template);
        $type = array_shift($parts);
        $name = array_shift($parts);

        $property = PropertyFactory::getInstance($type . '.' . $name);
        $fileTemplate = $runtime->getRootPath() . $property->getPath() . '/Template/' . implode('/', $parts) . '.php';

        if (!file_exists($fileTemplate)) {
            throw new RuntimeException('模板 ' . $template . ' 不存在！');
        }

        $path = $runtime->getCachePath() . '/System/Template/' . $theme . '/' . $type . '/' . $name . '/' . implode('/', $parts) . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $contentTheme = file_get_contents($fileTheme);
        $contentTemplate = file_get_contents($fileTemplate);

        $extends = '\\Be\\Framework\\Template';
        if (preg_match('/<be-extends\s+(.*?)\s*>/s', $contentTemplate, $matches)) {
            $extends = trim($matches[1]);
            self::update($extends, $theme);
            $contentTemplate = preg_replace($matches[0], '', $contentTemplate);
        }

        if (preg_match('/<be-include\s+(.*?)\s*>/s', $contentTemplate, $matches)) {
            $i = 0;
            foreach ($matches[1] as $m) {
                $includes = explode('.', $m);
                if (count($includes) > 2) {
                    $tmpType = array_shift($includes);
                    $tmpName = array_shift($includes);

                    $tmpProperty = PropertyFactory::getInstance($tmpType . '.' . $tmpName);
                    $fileInclude = $runtime->getRootPath() . $tmpProperty->getPath() . '/Template/' . implode('/', $includes) . '.php';
                    if (!file_exists($fileInclude)) {
                        throw new RuntimeException('模板中包含的文件 ' . $m . ' 不存在！');
                    }

                    $contentInclude = file_get_contents($fileInclude);
                    $contentTemplate = preg_replace($matches[0][$i], $contentInclude, $contentTemplate);
                }
                $i++;
            }
        }

        $codePre = '';
        $codeUse = '';
        $codeHtml = '';
        $pattern = '/<be-html>(.*?)<\/be-html>/s';
        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 html
            $codeHtml = trim($matches[1]);

            if (preg_match_all('/use\s+(.+);/', $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            $pattern = '/<\?php(.*?)\?>\s*<be-html>/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePre = trim($matches[1]);
                $codePre = preg_replace('/use\s+(.+);/', '', $codePre);
                $codePre = preg_replace('/\s+$/m', '', $codePre);
            }

        } else {

            if (preg_match($pattern, $contentTheme, $matches)) {
                $codeHtml = trim($matches[1]);

                $templateNoTags = true;
                $pattern = '/<be-head>(.*?)<\/be-head>/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 head
                    $codeHead = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeHead, $codeHtml);
                    $templateNoTags = false;
                }

                $pattern = '/<be-body>(.*?)<\/be-body>/s';
                if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 body
                    $codeBody = $matches[1];
                    $codeHtml = preg_replace($pattern, $codeBody, $codeHtml);
                    $templateNoTags = false;
                } else {

                    $pattern = '/<be-north>(.*?)<\/be-north>/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeNorth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeNorth, $codeHtml);
                        $templateNoTags = false;
                    }

                    $pattern = '/<be-middle>(.*?)<\/be-middle>/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeMiddle = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeMiddle, $codeHtml);
                        $templateNoTags = false;
                    } else {
                        $pattern = '/<be-west>(.*?)<\/be-west>/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 west
                            $codeWest = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeWest, $codeHtml);
                            $templateNoTags = false;
                        }

                        $pattern = '/<be-center>(.*?)<\/be-center>/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 center
                            $codeCenter = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeCenter, $codeHtml);
                            $templateNoTags = false;
                        }

                        $pattern = '/<be-east>(.*?)<\/be-east>/s';
                        if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 east
                            $codeEast = $matches[1];
                            $codeHtml = preg_replace($pattern, $codeEast, $codeHtml);
                            $templateNoTags = false;
                        }
                    }

                    $pattern = '/<be-south>(.*?)<\/be-south>/s';
                    if (preg_match($pattern, $contentTemplate, $matches)) { // 查找替换 north
                        $codeSouth = $matches[1];
                        $codeHtml = preg_replace($pattern, $codeSouth, $codeHtml);
                        $templateNoTags = false;
                    }
                }

                // 没有指定标签，所有内容放放 center
                if ($templateNoTags) {
                    $pattern = '/<be-center>(.*?)<\/be-center>/s';
                    $codeHtml = preg_replace($pattern, $contentTemplate, $codeHtml);
                }
            }

            $pattern = '/use\s+(.+);/';
            $uses = null;
            if (preg_match_all($pattern, $contentTheme, $matches)) {
                $uses = $matches[1];
                foreach ($matches[1] as $m) {
                    $codeUse .= 'use ' . $m . ';' . "\n";
                }
            }

            if (preg_match_all($pattern, $contentTemplate, $matches)) {
                foreach ($matches[1] as $m) {
                    if ($uses !== null && !in_array($m, $uses)) {
                        $codeUse .= 'use ' . $m . ';' . "\n";
                    }
                }
            }

            $pattern = '/<\?php(.*?)\?>\s+<be-html>/s';
            if (preg_match($pattern, $contentTheme, $matches)) {
                $codePreTheme = trim($matches[1]);
                $codePreTheme = preg_replace('/use\s+(.+);/', '', $codePreTheme);
                $codePreTheme = preg_replace('/\s+$/m', '', $codePreTheme);
                $codePre = $codePreTheme . "\n";
            }

            $pattern = '/<\?php(.*?)\?>\s+<be-(?:html|head|body|north|middle|west|center|east|south)>/s';
            if (preg_match($pattern, $contentTemplate, $matches)) {
                $codePreTemplate = trim($matches[1]);
                $codePreTemplate = preg_replace('/use\s+(.+);/', '', $codePreTemplate);
                $codePreTemplate = preg_replace('/\s+$/m', '', $codePreTemplate);

                $codePre .= $codePreTemplate . "\n";
            }
        }

        $codeVars = '';

        if (isset($themeProperty->colors) && is_array($themeProperty->colors)) {
            $codeVars .= '  public $colors = [\'' . implode('\',\'', $themeProperty->colors) . '\'];' . "\n";
        }

        $className = array_pop($parts);

        $codePhp = '<?php' . "\n";
        $codePhp .= 'namespace Be\\Cache\\Framework\\Template\\' . $theme . '\\' . $type . '\\' . $name;
        if (count($parts) > 0) {
            $codePhp .=  '\\' . implode('\\', $parts);
        }
        $codePhp .=  ';' . "\n";
        $codePhp .= "\n";
        $codePhp .= $codeUse;
        $codePhp .= "\n";
        $codePhp .= 'class ' . $className . ' extends ' . $extends . "\n";
        $codePhp .= '{' . "\n";
        $codePhp .= $codeVars;
        $codePhp .= "\n";
        $codePhp .= '  public function display()' . "\n";
        $codePhp .= '  {' . "\n";
        $codePhp .= $codePre;
        $codePhp .= '    ?>' . "\n";
        $codePhp .= $codeHtml . "\n";
        $codePhp .= '    <?php' . "\n";
        $codePhp .= '  }' . "\n";
        $codePhp .= '}' . "\n";
        $codePhp .= "\n";

        file_put_contents($path, $codePhp, LOCK_EX);
        chmod($path, 0755);
    }



}


