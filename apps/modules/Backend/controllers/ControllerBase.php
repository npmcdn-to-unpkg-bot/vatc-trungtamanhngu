<?php

namespace Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher as Dispatcher;

abstract class ControllerBase extends Controller {

    public $urlModule = '';

    protected function beforeExecuteRoute() {
        $this->queryUrl = $this->view->queryUrl = $this->config['rootUrl'] . $this->module_config_backend->main_route . "/".$this->dispatcher->getControllerName();
        $this->urlModule = $this->view->urlModule = $this->config['rootUrl'] . $this->module_config_backend->main_route . "/";
        $this->user = $this->view->user = $this->getUser();
        if (empty($this->user)) {
            if ($this->queryUrl != $this->urlModule . 'access' && $this->queryUrl != $this->urlModule . 'access/handle') {

                $this->response->redirect($this->urlModule . "access");
                return false;
            }
        }
    }

    public function initialize() {
        $this->userRole = $this->view->userRole = $this->getUserAdminRole();
    }

    function minify_css($text) {
        $from = array(
            //                  '%(#|;|(//)).*%',               // comments:  # or //
            '%/\*(?:(?!\*/).)*\*/%s', // comments:  /*...*/
            '/\s{2,}/', // extra spaces
            "/\s*([;{}])[\r\n\t\s]/", // new lines
            '/\\s*;\\s*/', // white space (ws) between ;
            '/\\s*{\\s*/', // remove ws around {
            '/;?\\s*}\\s*/', // remove ws around } and last semicolon in declaration block
                //                  '/:first-l(etter|ine)\\{/',     // prevent triggering IE6 bug: http://www.crankygeek.com/ie6pebug/
                //                  '/((?:padding|margin|border|outline):\\d+(?:px|em)?) # 1 = prop : 1st numeric value\\s+/x',     // Use newline after 1st numeric value (to limit line lengths).
                //                  '/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i',
        );
        $to = array(
            //                  '',
            '',
            ' ',
            '$1',
            ';',
            '{',
            '}',
                //                  ':first-l$1 {',
                //                  "$1\n",
                //                  '$1#$2$3$4$5',
        );
        $text = preg_replace($from, $to, $text);
        return $text;
    }

    function minify_js($text) {
        if (strlen($text) <= 100) {
            $contents = $text;
        } else {
            $contents = '';
            $post_text = http_build_query(array(
                'js_code' => $text,
                'output_info' => 'compiled_code', //($returnErrors ? 'errors' : 'compiled_code'),
                'output_format' => 'text',
                'compilation_level' => 'SIMPLE_OPTIMIZATIONS', //'ADVANCED_OPTIMIZATIONS',//'SIMPLE_OPTIMIZATIONS'
                    ), null, '&');
            $URL = 'http://closure-compiler.appspot.com/compile';
            $allowUrlFopen = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
            if ($allowUrlFopen) {
                $contents = file_get_contents($URL, false, stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $post_text,
                        'max_redirects' => 0,
                        'timeout' => 15,
                    )
                )));
            } elseif (defined('CURLOPT_POST')) {
                $ch = curl_init($URL);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_text);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
                $contents = curl_exec($ch);
                curl_close($ch);
            } else {
                //"Could not make HTTP request: allow_url_open is false and cURL not available"
                $contents = $text;
            }
            if ($contents == false || (trim($contents) == '' && $text != '') || strtolower(substr(trim($contents), 0, 5)) == 'error' || strlen($contents) <= 50) {
                //No HTTP response from server or empty response or error
                $contents = $text;
            }
        }
        return $contents;
    }

    function minify_html($text) {

        //get CSS and save it
        $search_css = '/<\s*style\b[^>]*>(.*?)<\s*\/style>/is';
        $ret = preg_match_all($search_css, $text, $tmps);
        $t_css = array();
        if ($ret !== false && $ret > 0) {
            foreach ($tmps as $k => $v) {
                if ($k > 0) {
                    foreach ($v as $kk => $vv) {
                        $t_css[] = $vv;
                    }
                }
            }
        }

        $css = $this->minify_css(implode('\n', $t_css));
        /*
          //get external JS and save it
          $search_js_ext = '/<\s*script\b.*?src=\s*[\'|"]([^\'|"]*)[^>]*>\s*<\s*\/script>/i';
          $ret = preg_match_all($search_js_ext, $text, $tmps);
          $t_js = array();
          if($ret!==false && $ret>0){
          foreach($tmps as $k=>$v){
          if($k>0){
          foreach($v as $kk=>$vv){
          $t_js[] = $vv;
          }
          }
          }
          }
          $js_ext = $t_js;
         */
        //get inline JS and save it
        $search_js_ext = '/<\s*script\b.*?src=\s*[\'|"]([^\'|"]*)[^>]*>\s*<\s*\/script>/i';
        $search_js = '/<\s*script\b[^>]*>(.*?)<\s*\/script>/is';
        $ret = preg_match_all($search_js, $text, $tmps);

        $t_js = array();
        $js_ext = array();
        if ($ret !== false && $ret > 0) {
            foreach ($tmps as $k => $v) {
                if ($k == 0) {
                    //let's check if we have a souce (src="")
                    foreach ($v as $kk => $vv) {
                        if ($vv != '') {
                            $ret = preg_match_all($search_js_ext, $vv, $ttmps);
                            if ($ret !== false && $ret > 0) {
                                foreach ($ttmps[1] as $kkk => $vvv) {
                                    $js_ext[] = $vvv;
                                }
                            }
                        }
                    }
                } else {
                    foreach ($v as $kk => $vv) {
                        if ($vv != '') {
                            $t_js[] = $vv;
                        }
                    }
                }
            }
        }

        $js = $this->minify_js(implode('\n', $t_js));

        //get inline noscript and save it
        $search_no_js = '/<\s*noscript\b[^>]*>(.*?)<\s*\/noscript>/is';
        $ret = preg_match_all($search_no_js, $text, $tmps);
        $t_js = array();
        if ($ret !== false && $ret > 0) {
            foreach ($tmps as $k => $v) {
                if ($k > 0) {
                    foreach ($v as $kk => $vv) {
                        $t_js[] = $vv;
                    }
                }
            }
        }
        $no_js = implode('\n', $t_js);

        //remove CSS and JS
        $search = array(
            $search_js_ext,
            $search_css,
            $search_js,
            $search_no_js,
            '/\>[^\S ]+/s', //strip whitespaces after tags, except space
            '/[^\S ]+\</s', //strip whitespaces before tags, except space
            '/(\s)+/s', // shorten multiple whitespace sequences
        );
        $replace = array(
            '',
            '',
            '',
            '',
            '>',
            '<',
            '\\1',
        );
        $buffer = preg_replace($search, $replace, $text);

        $append = '';
        //add CSS and JS at the bottom
        if (is_array($js_ext) && count($js_ext) > 0) {
            foreach ($js_ext as $k => $v) {
                $append .= '<script type="text/javascript" language="javascript" src="' . $v . '" ></script>';
            }
        }
        if ($css != '')
            $append .= '<style>' . $css . '</style>';
        if ($js != '') {
            //remove weird '\n' strings
            $js = preg_replace('/[\s]*\\\n/', "\n", $js);
            $append .= '<script>' . $js . '</script>';
        }
        if ($no_js != '')
            $append .= '<noscript>' . $no_js . '</noscript>';
        $buffer = preg_replace('/(.*)(<\s*\/\s*svg\s*>)(.*)/', '\\1' . $append . '\\2\\3', $buffer);
        return $buffer;
    }

    public function createSession($user) {
        $this->kichOut($user['usa_id']);
        $this->memsession->set('USER', $user);
        $log_sess = $this->memcache->get('login_session');
        $log_sess[$user['usa_id']] = $this->memsession->getId();
        $this->memcache->save('login_session', $log_sess);
    }

    public function getUser() {
        $user_info = $this->memsession->get('USER', null);
        $log_sess = $this->memcache->get('login_session');

        if (!isset($user_info) || $log_sess[$user_info['usa_id']] != $this->memsession->getId()) {
            $user_info = array();
        }
        return $user_info;
    }

    public function destroySession() {
        $this->memsession->destroy('USER');
    }

    public function kichOut($user_id) {
        $log_sess = $this->memcache->get('login_session');
        if (isset($log_sess[$user_id])) {
            $this->memsession->destroy($log_sess[$user_id]);
        }
    }

    public function getUserAdminRole() {
        $adminRoleModel = new \Backend\Models\AdminRoleModel();
        $listRole = $adminRoleModel::find(array("columns" => "role_name,role_ar_user"));
        $tempRole = array();
        foreach ($listRole as $role) {
            $tempRole[$role->role_name] = explode(',', $role->role_ar_user);
        }
        return $tempRole;
    }

    public static function vn_str_filter($str) {
        $str = str_replace(' ', '-', $str); // Replaces all spaces with hyphens.
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị', 'O' =>
            'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
        );
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = preg_replace('/[^A-Za-z0-9\-]/', '', $str); // Removes special chars.
        return $str;
    }

    public static function get_seo($str) {
        $str = self::vn_str_filter($str);
        $str = str_replace("--", "-", $str);
        return strtolower($str);
    }

}
