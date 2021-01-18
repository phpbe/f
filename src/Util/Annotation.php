<?php
namespace Be\F\Util;


class Annotation
{

    /**
     * 解析文档注释
     *
     * @param string $docComment 文档注释
     * @return array
     */
    public static function parse($docComment)
    {
        $result = [];
        if (!$docComment) return $result;
        if (preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment) === false) return [];
        $comment = trim($comment[1]);

        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) return [];
        $lines = $lines[1];

        $currentAnnotation = null;
        $description = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line) {
                // 该行注释由 @ 开头
                if (strpos($line, '@') === 0) {

                    // 注解
                    if (preg_match("/^@[A-Za-z0-9_]+\s*$/", $line) || preg_match("/^@[A-Za-z0-9_]+\s*\(/", $line)) {
                        $currentAnnotation = $line;
                        if (substr($line, -1, 1) == ')') {
                            $parsedAnnotation = self::parseAnnotation($currentAnnotation);
                            if ($parsedAnnotation) {
                                $result[$parsedAnnotation['name']][] = $parsedAnnotation['keyValues'];
                            }
                            $currentAnnotation = null;
                        }
                        continue;
                    }

                    if (strpos($line, ' ') > 0) {
                        $param = substr($line, 1, strpos($line, ' ') - 1);
                        $value = substr($line, strlen($param) + 2);
                    } else {
                        $param = substr($line, 1);
                        $value = '';
                    }

                    if ($param == 'param' || $param == 'return') {
                        $pos = strpos($value, ' ');
                        $type = substr($value, 0, $pos);
                        $value = '(' . $type . ')' . substr($value, $pos + 1);
                    } elseif ($param == 'class') {
                        $r = preg_split("[|]", $value);
                        if (is_array($r)) {
                            $param = $r[0];
                            parse_str($r[1], $value);
                            foreach ($value as $key => $val) {
                                $val = explode(',', $val);
                                if (count($val) > 1)
                                    $value[$key] = $val;
                            }
                        } else {
                            $param = 'Unknown';
                        }
                    }

                    if (!isset($result[$param])) {
                        $result[$param] = [];
                    }

                    $result[$param][] = $value;
                } else {

                    if ($currentAnnotation !== null) {
                        $currentAnnotation .= $line;
                        if (substr($line, -1, 1) == ')') {
                            $parsedAnnotation = self::parseAnnotation($currentAnnotation);
                            if ($parsedAnnotation) {
                                $result[$parsedAnnotation['name']][] = $parsedAnnotation['keyValues'];
                            }
                            $currentAnnotation = null;
                        }
                    } else {
                        $description[] = $line;
                    }
                }
            } else {
                if (!isset($result['summary']) && count($description) > 0) {
                    $result['summary'] = implode(PHP_EOL, $description);
                    $description = [];
                }
            }
        }

        if (count($description) > 0) {
            $description = implode(' ', $description);
            $result['description'] = $description;
        }

        return $result;
    }

    /**
     * @param string $currentAnnotation
     * @return array
     */
    public static function parseAnnotation($currentAnnotation) {
        // @BeMenu
        $pattern = "/^@([A-Za-z0-9_]+)\s*$/";
        if (preg_match($pattern, $currentAnnotation, $matches)) {
            return [
                'name' => $matches[1],
                'keyValues' => []
            ];
        } else {
            // @BeMenu("用户管理", icon = "el-icon-user")
            $pattern = "/^@([A-Za-z0-9_]+)\s*\((.+)\)/";
            if (preg_match($pattern, $currentAnnotation, $matches)) {
                $return = [
                    'name' => $matches[1],
                    'keyValues' => []
                ];

                $param = $matches[2];
                // "用户管理"
                // "用户管理", icon = "el-icon-user"
                $pattern = "/^\s*\"([^\"]+)\"/";
                if (preg_match($pattern, $param, $matches)) {
                    $return['keyValues']['value'] = $matches[1];
                }

                // label = "用户管理", icon = "el-icon-user"
                $pattern = "/([A-Za-z0-9_]+)\s*=\s*\"([^\"]+)\"/";
                if (preg_match_all($pattern, $param, $matches)) {
                    $n = count($matches[1]);
                    for($i = 0; $i< $n; $i++) {
                        $return['keyValues'][$matches[1][$i]] = $matches[2][$i];
                    }
                }
                return $return;
            }
        }

        return [];
    }


}

