<?php
/**
 * ASMR.one API 客户端 + 工具函数
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/* ===================== 缓存层 ===================== */

function _asmr_cache_dir()
{
    static $dir = null;
    if ($dir === null) {
        if (function_exists('Typecho_Widget')) {
            $dir = Typecho_Widget::widget('Widget_Options')->themeFile('tmp');
            if (!$dir) $dir = __DIR__ . '/../tmp';
        } else {
            $dir = __DIR__ . '/../tmp';
        }
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
            @file_put_contents(rtrim($dir, '/') . '/.htaccess', "Deny from all\n");
        }
    }
    return rtrim($dir, '/');
}
function _asmr_cache_read($key, $ttl)
{
    $f = _asmr_cache_dir() . '/asmr_' . md5($key) . '.cache';
    if (!is_file($f)) return null;
    if ((time() - filemtime($f)) > $ttl) { @unlink($f); return null; }
    $data = @file_get_contents($f);
    if (!$data) return null;
    return @unserialize($data);
}
function _asmr_cache_write($key, $data)
{
    if ($data === null || $data === false) return;
    $f = _asmr_cache_dir() . '/asmr_' . md5($key) . '.cache';
    @file_put_contents($f, serialize($data), LOCK_EX);
}

/* ===================== API 客户端 ===================== */

class AsmrClient
{
    private $baseUrl = 'https://api.asmr-200.com/api/';
    private $username;
    private $password;
    private $timeout;
    private $headers = array();
    private $logined = false;
    private $tokenFile;

    public function __construct($username = '', $password = '', $timeout = 10)
    {
        $this->username = trim((string)$username);
        $this->password = trim((string)$password);
        $this->timeout  = max(3, intval($timeout));
        $this->tokenFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . 'asmr_token_' . md5((string)$this->username) . '.txt';

        // 模拟真实浏览器 UA 及常用头（移除 Accept-Encoding，让 curl 自动处理）
        $this->headers = array(
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Referer'         => 'https://asmr.one/',
            'Origin'          => 'https://asmr.one',
            'Connection'      => 'keep-alive',
            'Sec-Fetch-Dest'  => 'document',
            'Sec-Fetch-Mode'  => 'navigate',
            'Sec-Fetch-Site'  => 'same-origin',
            'Upgrade-Insecure-Requests' => '1',
        );
    }

    public function ensureLogin($force = false)
    {
        if (!$this->username || !$this->password) return false;
        if (!$force && $this->logined) return true;

        if (!$force) {
            if (is_file($this->tokenFile) && (time() - filemtime($this->tokenFile) < 21600)) {
                $token = trim(@file_get_contents($this->tokenFile));
                if ($token) {
                    $this->headers['Authorization'] = 'Bearer ' . $token;
                    $this->logined = true;
                    return true;
                }
            }
        }

        $resp = $this->httpRequest('POST', 'auth/me', array(
            'name'     => $this->username,
            'password' => $this->password,
        ), false);
        if (!$resp || !is_array($resp)) return false;

        $token = null;
        if (isset($resp['token'])) $token = $resp['token'];
        elseif (isset($resp['user']['token'])) $token = $resp['user']['token'];

        if ($token) {
            $this->headers['Authorization'] = 'Bearer ' . $token;
            $this->logined = true;
            @file_put_contents($this->tokenFile, $token, LOCK_EX);
            @chmod($this->tokenFile, 0600);
            return true;
        }
        if (isset($resp['user']['loggedIn']) && $resp['user']['loggedIn']) {
            $this->logined = true;
            return true;
        }
        return false;
    }

    public function invalidateToken()
    {
        $this->logined = false;
        unset($this->headers['Authorization']);
        if (is_file($this->tokenFile)) @unlink($this->tokenFile);
    }

    public function isLogined() { return $this->logined; }

    /* ---------- 业务接口（带缓存） ---------- */

    public function searchWorks($query, $page = 1, $pageSize = 24)
    {
        $key = 'search_v4_' . $query . '_' . intval($page) . '_' . intval($pageSize);
        $cached = _asmr_cache_read($key, 7200);
        if (is_array($cached)) return $cached;

        $encoded = rawurlencode($query);
        $r = $this->get('search/' . $encoded, array(
            'page'     => max(1, intval($page)),
            'pageSize' => max(1, intval($pageSize)),
        ));

        if (is_array($r) && !isset($r['error'])) {
            if (!isset($r['works']) && isset($r['items'])) $r['works'] = $r['items'];
            if (!isset($r['works']) && isset($r['data']))  $r['works'] = $r['data'];
            if (!isset($r['works']) && isset($r['results'])) $r['works'] = $r['results'];
            if (!isset($r['works'])) $r['works'] = array();
            _asmr_cache_write($key, $r);
            return $r;
        }

        $r2 = $this->get('works', array(
            'order'    => 'create_date',
            'sort'     => 'desc',
            'page'     => max(1, intval($page)),
            'pageSize' => max(1, intval($pageSize)),
        ));
        if (is_array($r2) && isset($r2['works'])) $r = $r2;
        if (!is_array($r)) return null;
        if (!isset($r['works']) && isset($r['items'])) $r['works'] = $r['items'];
        if (!isset($r['works']) && isset($r['data']))  $r['works'] = $r['data'];
        if (!isset($r['works']) && isset($r['results'])) $r['works'] = $r['results'];
        if (!isset($r['works'])) $r['works'] = array();

        _asmr_cache_write($key, $r);
        return $r;
    }

    public function listWorks($page = 1, $pageSize = 24, $order = 'create_date', $sort = 'desc')
    {
        $key = 'list_v6_' . intval($page) . '_' . intval($pageSize) . '_' . $order . '_' . $sort;
        $cached = _asmr_cache_read($key, 7200);
        if (is_array($cached)) return $cached;

        $params = array(
            'order'    => $order,
            'sort'     => $sort,
            'page'     => max(1, intval($page)),
            'pageSize' => max(1, intval($pageSize)),
            'subtitle' => 0,
        );
        $r = $this->get('works', $params);
        if (!is_array($r)) return null;
        if (!isset($r['works'])) $r['works'] = array();
        _asmr_cache_write($key, $r);
        return $r;
    }

    public function getWorkInfo($workCode)
    {
        $code = strtoupper(trim($workCode));
        $key = 'info_v5_' . $code;
        $cached = _asmr_cache_read($key, 21600);
        if (is_array($cached)) return $cached;

        $r = $this->get('workInfo/' . $code);
        if (is_array($r) && !isset($r['error'])) {
            _asmr_cache_write($key, $r);
            return $r;
        }
        if (preg_match('/^(RJ|VJ|BJ)(\d+)$/i', $code, $m)) {
            $r2 = $this->get('workInfo/' . $m[2]);
            if (is_array($r2) && !isset($r2['error'])) {
                _asmr_cache_write($key, $r2);
                return $r2;
            }
        }
        if (preg_match('/^\d+$/', $code)) {
            $r3 = $this->get('workInfo/' . $code);
            if (is_array($r3) && !isset($r3['error'])) {
                _asmr_cache_write($key, $r3);
                return $r3;
            }
        }
        return null;
    }

    public function getWorkTracks($internalId)
    {
        $num = intval($internalId);
        if ($num < 1) return null;
        $key = 'tracks_v5_' . $num;
        $cached = _asmr_cache_read($key, 43200);
        if (is_array($cached)) return $cached;

        $r = $this->get('tracks/' . $num, array('v' => 2));
        if (is_array($r)) {
            _asmr_cache_write($key, $r);
            return $r;
        }
        return null;
    }

    public function getWorkBundle($workCode, $iid = null)
    {
        $code = strtoupper(trim($workCode));
        $numIid = $iid ? intval($iid) : 0;

        $infoKey = 'info_v5_' . $code;
        $cachedInfo = _asmr_cache_read($infoKey, 21600);
        $tracks = null;

        if ($cachedInfo && !$numIid) {
            $numIid = isset($cachedInfo['id']) ? intval($cachedInfo['id']) : 0;
        }
        if ($numIid) {
            $tracksKey = 'tracks_v5_' . $numIid;
            $cachedTracks = _asmr_cache_read($tracksKey, 43200);
            if ($cachedTracks) $tracks = $cachedTracks;
        }

        if ($cachedInfo && $tracks) return array($cachedInfo, $tracks);

        $jobs = array();
        if (!$cachedInfo) {
            $jobs['info'] = array(
                'url' => $this->baseUrl . 'workInfo/' . $code,
                'cacheKey' => $infoKey,
                'ttl' => 21600,
            );
        }
        if (!$tracks && $numIid) {
            $jobs['tracks'] = array(
                'url' => $this->baseUrl . 'tracks/' . $numIid . '?v=2',
                'cacheKey' => 'tracks_v5_' . $numIid,
                'ttl' => 43200,
            );
        }

        if (!empty($jobs)) {
            $results = $this->curlMultiGet($jobs);
            if (!$cachedInfo && isset($results['info']) && is_array($results['info'])) {
                $cachedInfo = $results['info'];
            }
            if (!$tracks && isset($results['tracks']) && is_array($results['tracks'])) {
                $tracks = $results['tracks'];
            }
        }

        if (!$tracks && $cachedInfo && isset($cachedInfo['id'])) {
            $tracks = $this->getWorkTracks($cachedInfo['id']);
        }
        return array($cachedInfo, $tracks);
    }

    private function curlMultiGet($jobs)
    {
        if (!function_exists('curl_multi_init') || empty($jobs)) return array();
        $mh = curl_multi_init();
        $handles = array();
        $out = array();

        // 构造浏览器头（不含 Accept-Encoding，由 CURLOPT_ENCODING 自动处理）
        $headerLines = array();
        foreach ($this->headers as $k => $v) {
            $headerLines[] = $k . ': ' . $v;
        }

        foreach ($jobs as $k => $job) {
            $ch = curl_init($job['url']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(4, $this->timeout));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, ''); // 自动处理 gzip/deflate/br
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600);
            curl_multi_add_handle($mh, $ch);
            $handles[$k] = $ch;
        }

        $active = 0;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc === CURLM_OK) {
            if (curl_multi_select($mh) === -1) { usleep(10000); }
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($handles as $k => $ch) {
            $raw = curl_multi_getcontent($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            if ($raw !== false && $code >= 200 && $code < 300) {
                $data = json_decode($raw, true);
                if (is_array($data) && !isset($data['error'])) {
                    $out[$k] = $data;
                    if (!empty($jobs[$k]['cacheKey'])) {
                        _asmr_cache_write($jobs[$k]['cacheKey'], $data, $jobs[$k]['ttl']);
                    }
                }
            }
        }
        curl_multi_close($mh);
        return $out;
    }

    public function get($route, $params = null)
    {
        $resp = $this->httpRequest('GET', $route, $params, false);
        if (is_array($resp)) return $resp;

        if ($resp === 401 && $this->username && $this->password) {
            $this->ensureLogin(true);
            if ($this->logined) {
                $resp2 = $this->httpRequest('GET', $route, $params, true);
                if ($resp2 === 401) {
                    $this->invalidateToken();
                    if ($this->ensureLogin(true)) {
                        $resp2 = $this->httpRequest('GET', $route, $params, true);
                    }
                }
                if (is_array($resp2)) return $resp2;
            }
        }
        return (is_array($resp) || $resp === null) ? $resp : null;
    }

    private function httpRequest($method, $route, $body = null, $sendAuth = true)
    {
        if (!function_exists('curl_init')) return null;
        $url = $this->baseUrl . $route;
        $isGet = ($method === 'GET');
        if ($isGet && $body) {
            $url .= '?' . http_build_query($body);
            $body = null;
        }
        $ch = curl_init($url);

        // 基础头（不含 Accept-Encoding，由 CURLOPT_ENCODING 处理）
        $headers = array();
        foreach ($this->headers as $k => $v) {
            if ($k === 'Authorization') continue;  // 单独处理
            $headers[] = $k . ': ' . $v;
        }
        if ($sendAuth && !empty($this->headers['Authorization'])) {
            $headers[] = 'Authorization: ' . $this->headers['Authorization'];
        }
        if (!$isGet) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(5, $this->timeout));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // 自动处理 gzip/deflate/br
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600);

        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) return null;
        if ($code === 401) return 401;
        if ($code >= 300) return null;
        $data = json_decode($raw, true);
        return $data;
    }

    public function proxyStream($url, $downloadName = '')
    {
        if (!$url) { header('HTTP/1.1 400 Bad Request'); echo 'empty url'; return; }
        if (!function_exists('curl_init')) { header('HTTP/1.1 500 Internal Server Error'); echo 'curl missing'; return; }

        $sendAuth = !empty($this->headers['Authorization']);
        $ch = curl_init($url);
        $headers = array(
            'User-Agent: ' . $this->headers['User-Agent'],
            'Referer: https://asmr.one/',
            'Origin: https://asmr.one',
            'Accept: */*',
            'Connection: keep-alive',
        );
        if ($sendAuth) {
            $headers[] = 'Authorization: ' . $this->headers['Authorization'];
        }
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        // ★ 让 curl 自动解压 gzip/deflate/br 内容 ★
        curl_setopt($ch, CURLOPT_ENCODING, '');

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            echo $data;
            flush();
            return strlen($data);
        });
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) {
            $len = strlen($header);
            $trimmed = trim($header);
            if (!$trimmed) return $len;
            list($name) = explode(':', $trimmed, 2) + array('', '');
            $lower = strtolower($name);
            if (in_array($lower, array('content-type', 'content-length', 'content-range', 'accept-ranges', 'content-disposition', 'last-modified', 'etag', 'cache-control', 'cross-origin-resource-policy'))) {
                if ($lower === 'cross-origin-resource-policy') return $len;
                header($trimmed);
            }
            return $len;
        });

        header('Accept-Ranges: bytes');
        header('Access-Control-Allow-Origin: *');
        if ($downloadName) {
            header('Content-Disposition: inline; filename="' . rawurlencode($downloadName) . '"');
        }

        @set_time_limit(0);
        @ini_set('zlib.output_compression', 'Off');
        while (ob_get_level() > 0) @ob_end_flush();

        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || !$code) {
            if (!headers_sent()) header('HTTP/1.1 502 Bad Gateway');
            echo 'proxy error: ' . htmlspecialchars($err);
        } elseif ($code >= 400 && !headers_sent()) {
            header(' ', true, $code);
        }
        exit;
    }
}

/* ===================== 工具函数（保持不变） ===================== */

function asmr_parse_work_code($code)
{
    $code = strtoupper(trim($code));
    if (!preg_match('/^(RJ|VJ|BJ)(\d+)$/', $code, $m)) {
        if (preg_match('/^\d+$/', $code)) return intval($code);
        return null;
    }
    $num = intval($m[2]);
    if ($m[1] === 'RJ') return $num;
    if ($m[1] === 'VJ') return $num + 3 * 100000000;
    if ($m[1] === 'BJ') return $num + 4 * 100000000;
    return null;
}

function asmr_code_from_id($id, $rawCode = '')
{
    if ($rawCode) return strtoupper($rawCode);
    return 'RJ' . intval($id);
}

function asmr_cover_url($workId)
{
    return 'https://api.asmr-300.com/api/cover/' . intval($workId) . '.jpg?type=main';
}
function asmr_cover_thumb_url($workId)
{
    return 'https://api.asmr-300.com/api/cover/' . intval($workId) . '.jpg?type=240x240';
}

function asmr_extract_tracks($tracks)
{
    $audio = array(); $video = array(); $sub = array();
    $walk = function ($list, $parentPath = '') use (&$walk, &$audio, &$video, &$sub) {
        if (!is_array($list)) return;
        foreach ($list as $track) {
            if (!is_array($track)) continue;
            $type = isset($track['type']) ? $track['type'] : '';
            $title = isset($track['title']) ? $track['title'] : '';
            if ($type === 'folder') {
                $folderName = $title ? $title : '未命名文件夹';
                $currentPath = $parentPath ? $parentPath . '/' . $folderName : $folderName;
                if (isset($track['children']) && is_array($track['children'])) {
                    $walk($track['children'], $currentPath);
                }
            } else {
                $track['full_path'] = $parentPath;
                if (!empty($track['streamLowQualityUrl'])) {
                    $finalUrl = $track['streamLowQualityUrl'];
                } elseif (!empty($track['mediaDownloadUrl'])) {
                    $finalUrl = $track['mediaDownloadUrl'];
                } else {
                    $finalUrl = isset($track['mediaStreamUrl']) ? $track['mediaStreamUrl'] : '';
                }
                $track['_playUrl'] = $finalUrl;

                $low = strtolower($title);
                if (substr($low, -4) === '.mp3')  $audio[] = $track;
                elseif (substr($low, -4) === '.wav')  $audio[] = $track;
                elseif (substr($low, -5) === '.flac') $audio[] = $track;
                elseif (substr($low, -4) === '.m4a')  $audio[] = $track;
                elseif (substr($low, -4) === '.aac')  $audio[] = $track;
                elseif (substr($low, -4) === '.ogg')  $audio[] = $track;
                elseif (substr($low, -4) === '.mp4')  { $track['_isVideo'] = true; $video[] = $track; }
                elseif (substr($low, -5) === '.webm') { $track['_isVideo'] = true; $video[] = $track; }
                elseif (substr($low, -4) === '.avi')  { $track['_isVideo'] = true; $video[] = $track; }
                elseif (substr($low, -4) === '.lrc' || substr($low, -4) === '.vtt') $sub[] = $track;
            }
        }
    };
    $walk($tracks);
    return array($audio, $video, $sub);
}

function asmr_match_subtitles($audioFiles, $subtitleFiles)
{
    $map = array(); $prefixes = array();
    foreach ($subtitleFiles as $s) {
        $name = isset($s['title']) ? $s['title'] : '';
        $nameNoExt = strtolower(pathinfo($name, PATHINFO_FILENAME));
        $url = isset($s['_playUrl']) ? $s['_playUrl']
            : (isset($s['mediaDownloadUrl']) ? $s['mediaDownloadUrl']
            : (isset($s['mediaStreamUrl']) ? $s['mediaStreamUrl'] : null));
        $prefixes[$nameNoExt] = $url;
    }
    foreach ($audioFiles as $audio) {
        $title = isset($audio['title']) ? $audio['title'] : '';
        $nameNoExt = strtolower(pathinfo($title, PATHINFO_FILENAME));
        if (isset($prefixes[$nameNoExt])) {
            $map[$title] = $prefixes[$nameNoExt];
            continue;
        }
        $found = false;
        foreach ($prefixes as $p => $url) {
            if ($p && (strpos($nameNoExt, $p) !== false || strpos($p, $nameNoExt) !== false)) {
                $map[$title] = $url;
                $found = true; break;
            }
        }
        if (!$found) $map[$title] = null;
    }
    return $map;
}

function asmr_build_mp4_field($mediaFiles, $subtitleMap, $workCode)
{
    if (empty($mediaFiles)) return '';
    $albumDict = array();
    foreach ($mediaFiles as $file) {
        $songName = isset($file['title']) ? $file['title'] : '未知文件';
        $cleanName = $songName;
        $isVideo = !empty($file['_isVideo']);
        $ext = strtolower(substr($cleanName, -4));
        $ext5 = strtolower(substr($cleanName, -5));
        if (in_array($ext, array('.mp3','.wav','.flac','.aac','.ogg','.m4a','.mp4','.avi')) || $ext5 === '.webm') {
            $cleanName = substr($cleanName, 0, ($isVideo && $ext5==='.webm') ? -5 : -4);
        }
        $songUrl = isset($file['_playUrl']) ? $file['_playUrl']
            : (isset($file['mediaDownloadUrl']) ? $file['mediaDownloadUrl'] : '');
        $lyricUrl = isset($subtitleMap[$songName]) ? $subtitleMap[$songName] : '';

        $origDownload = isset($file['mediaDownloadUrl']) ? $file['mediaDownloadUrl'] : $songUrl;
        $filePath = isset($file['full_path']) ? $file['full_path'] : '';

        $albumName = $workCode;
        if ($filePath) {
            $parts = explode('/', $filePath);
            $discName = '';
            for ($i = count($parts) - 1; $i >= 0; $i--) {
                $part = trim($parts[$i]);
                if ($part === '' || $part === $workCode) continue;
                $low = strtolower($part);
                if (strpos($low, 'mp3') !== false) continue;
                if ($low === 'main' || $low === 'main-songs') continue;
                $discName = $part; break;
            }
            if (!$discName && count($parts) > 1) {
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part === '' || $part === $workCode) continue;
                    $low = strtolower($part);
                    if (strpos($low, 'mp3') !== false) continue;
                    if ($low === 'main' || $low === 'main-songs') continue;
                    $discName = $part; break;
                }
            }
            if ($discName) {
                $discName = preg_replace('/^mp3[_\-]/i', '', $discName);
                $discName = preg_replace('/[_\-]mp3$/i', '', $discName);
                $discName = preg_replace('/[_\-]mp3[_\-]/i', '_', $discName);
                $discName = trim($discName, '_- ');
            }
            if ($discName && $discName !== $workCode) {
                $albumName = $workCode . '·' . $discName;
            }
        }
        if (strtolower(trim($albumName)) === 'mp3') $albumName = $workCode;
        elseif (strpos(strtolower($albumName), 'mp3') !== false && strpos($albumName, '·') !== false) {
            $ps = explode('·', $albumName, 2);
            if (count($ps) > 1 && strtolower(trim($ps[0])) === 'mp3') {
                $albumName = $workCode . '·' . $ps[1];
            }
        }
        if (!isset($albumDict[$albumName])) $albumDict[$albumName] = array();
        $albumDict[$albumName][] = array(
            'name'    => $cleanName,
            'url'     => $songUrl,
            'orig'    => $origDownload,
            'lyric'   => $lyricUrl ? $lyricUrl : '',
            'isVideo' => $isVideo,
        );
    }

    $keys = array_keys($albumDict);
    usort($keys, function ($a, $b) {
        $hasA = strpos($a, '·'); $hasB = strpos($b, '·');
        if ($hasA === false && $hasB === false) return 0;
        if ($hasA === false) return -1;
        if ($hasB === false) return 1;
        $mA = $mB = array();
        preg_match('/·.*?(\d+)/', $a, $mA);
        preg_match('/·.*?(\d+)/', $b, $mB);
        $numA = $mA ? intval($mA[1]) : 0;
        $numB = $mB ? intval($mB[1]) : 0;
        if ($numA && $numB && $numA !== $numB) return $numA - $numB;
        if ($numA && !$numB) return -1;
        if (!$numA && $numB) return 1;
        return strlen($a) - strlen($b);
    });

    $lines = array();
    foreach ($keys as $albumName) {
        $lines[] = '#' . $albumName;
        foreach ($albumDict[$albumName] as $song) {
            $n = str_replace(array("\n", '$', '#'), array(' ', '', ''), $song['name']);
            $u = trim(str_replace("\n", '', $song['url']));
            $orig = trim(str_replace("\n", '', $song['orig']));
            $l = trim(str_replace("\n", '', $song['lyric']));
            $tp = !empty($song['isVideo']) ? 'video' : 'audio';
            $lines[] = $n . '$' . $u . '$' . $l . '$' . $orig . '$' . $tp;
        }
    }
    return implode("\n", $lines);
}

function asmr_is_r18($workInfo)
{
    if (!is_array($workInfo)) return false;
    if (isset($workInfo['nsfw']) && $workInfo['nsfw'] === true) return true;
    if (isset($workInfo['age_category_string']) && strtolower($workInfo['age_category_string']) === 'adult') return true;
    if (isset($workInfo['age']) && strtolower($workInfo['age']) === 'adult') return true;
    if (isset($workInfo['works']) && is_array($workInfo['works'])) {
        foreach ($workInfo['works'] as $w) {
            if (isset($w['nsfw']) && $w['nsfw'] === true) return true;
            if (isset($w['age_category_string']) && strtolower($w['age_category_string']) === 'adult') return true;
        }
    }
    return false;
}

function asmr_get_age_badge($workInfo)
{
    if (!is_array($workInfo)) return '';
    if (asmr_is_r18($workInfo)) return 'R18';
    if (isset($workInfo['age_category_string']) && strtolower($workInfo['age_category_string']) === 'r15') return 'R15';
    if (isset($workInfo['age']) && strtolower($workInfo['age']) === 'r15') return 'R15';
    if (isset($workInfo['works']) && is_array($workInfo['works'])) {
        foreach ($workInfo['works'] as $w) {
            if (isset($w['age_category_string']) && strtolower($w['age_category_string']) === 'r15') return 'R15';
        }
    }
    return '';
}

function asmr_get_vas($workInfo)
{
    $vas = array();
    foreach (array('vas','voiceActors','cv','vAs','actors','voice_actors') as $k) {
        if (!isset($workInfo[$k])) continue;
        $data = $workInfo[$k];
        if (is_array($data)) {
            foreach ($data as $va) {
                if (is_string($va)) $vas[] = trim($va);
                elseif (is_array($va) && isset($va['name'])) $vas[] = trim($va['name']);
            }
            if ($vas) break;
        } elseif (is_string($data)) {
            foreach (explode(',', $data) as $va) $vas[] = trim($va);
            if ($vas) break;
        }
    }
    if (!$vas && isset($workInfo['circle']['name'])) {
        $vas[] = $workInfo['circle']['name'];
    }
    return $vas;
}

function asmr_build_search_query($keyword = null, $tags = array(), $age = null, $va = null)
{
    $q = '';
    if (!empty($tags)) {
        if (is_string($tags)) $tags = explode(',', $tags);
        foreach ($tags as $t) {
            $t = trim($t);
            if ($t) $q .= '$tag:' . $t . '$';
        }
    }
    if ($age) {
        $ageMap = array('general' => 'general', 'r15' => 'r15', 'adult' => 'adult', 'r18' => 'adult');
        if (isset($ageMap[$age])) $q .= '$age:' . $ageMap[$age] . '$';
    }
    if ($va) $q .= $va;
    if ($keyword) $q .= $keyword;
    if (!$q) $q = '$all$';
    return $q;
}

function asmr_extract_vas_from_works($works, $limit = 100)
{
    $cachedVas = _asmr_cache_read('all_known_vas', 604800);
    if (!is_array($cachedVas)) $cachedVas = array();

    $countMap = array();
    $newFound = false;
    if (is_array($works)) {
        foreach ($works as $w) {
            if (empty($w['vas']) || !is_array($w['vas'])) continue;
            foreach ($w['vas'] as $v) {
                $name = is_array($v) && isset($v['name']) ? trim($v['name']) : (is_string($v) ? trim($v) : '');
                if ($name) {
                    $countMap[$name] = isset($countMap[$name]) ? $countMap[$name] + 1 : 1;
                    if (!in_array($name, $cachedVas)) {
                        $cachedVas[] = $name;
                        $newFound = true;
                    }
                }
            }
        }
    }

    $popular = array(
        '藤田茜','陽向葵ゅか','涼花みなせ','柚木つばめ','大山チロル','伊倉える',
        '沢野ぽぷら','逢坂成美','秋野かえで','北見六花','香山いちご','水野七海',
        '御子柴泉','山田じぇみ子','縁側こより',
    );
    foreach ($popular as $n) {
        if (!in_array($n, $cachedVas)) {
            $cachedVas[] = $n;
            $newFound = true;
        }
    }

    if ($newFound) {
        _asmr_cache_write('all_known_vas', $cachedVas);
    }

    $allVas = array_unique($cachedVas);
    $sortedVas = array();
    foreach ($allVas as $name) {
        $count = isset($countMap[$name]) ? $countMap[$name] : 0;
        $sortedVas[$name] = $count;
    }
    arsort($sortedVas, SORT_NUMERIC);
    $result = array_keys($sortedVas);
    return array_slice($result, 0, $limit);
}

function asmr_extract_tags_from_works($works, $limit = 150)
{
    $out = array(); $seen = array();
    
    $cachedTags = _asmr_cache_read('all_known_tags', 604800);
    if (is_array($cachedTags)) {
        foreach ($cachedTags as $t) {
            if ($t && !isset($seen[$t])) { $seen[$t] = true; $out[] = $t; }
        }
    }

    $newFound = false;
    if (is_array($works)) {
        foreach ($works as $w) {
            if (empty($w['tags']) || !is_array($w['tags'])) continue;
            foreach ($w['tags'] as $t) {
                $name = is_array($t) && isset($t['name']) ? trim($t['name']) : (is_string($t) ? trim($t) : '');
                if ($name && !isset($seen[$name])) { 
                    $seen[$name] = true; 
                    $out[] = $name; 
                    $newFound = true;
                }
            }
        }
    }
    
    $popular = array(
        '舔耳','低语','环绕音','ASMR','双声道立体声/人头麦','催眠','暗示','自慰辅助',
        '耳舐め','ささやき','密着','淫语','言语刺激','角色扮演','洗脑','精神支配',
        '掏耳朵','梵天','棉花棒','耳搔','呼吸','吐息','心跳','环境音','雨音',
    );
    foreach ($popular as $n) { 
        if (!isset($seen[$n])) { 
            $seen[$n] = true; 
            $out[] = $n; 
            $newFound = true;
        } 
    }

    if ($newFound) {
        _asmr_cache_write('all_known_tags', $out);
    }

    sort($out, SORT_STRING | SORT_FLAG_CASE);
    return array_slice($out, 0, $limit);
}

function asmr_parse_mp4_field($mp4Field)
{
    $mp4Field = str_replace(array("\r\n", "\r"), "\n", $mp4Field);
    $rawLines = explode("\n", $mp4Field);
    $albumData = array(); $flatList = array(); $currentGroup = '曲目列表';
    foreach ($rawLines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (strpos($line, '#') === 0) { $currentGroup = substr($line, 1); continue; }
        if (strpos($line, '$') === false) continue;
        $parts = explode('$', $line);
        if (count($parts) < 2) continue;
        $typeRaw = isset($parts[4]) ? trim($parts[4]) : '';
        $songInfo = array(
            'title'   => isset($parts[0]) ? trim($parts[0]) : '未知标题',
            'url'     => isset($parts[1]) ? trim($parts[1]) : '',
            'lrc'     => isset($parts[2]) ? trim($parts[2]) : '',
            'orig'    => isset($parts[3]) ? trim($parts[3]) : (isset($parts[1]) ? trim($parts[1]) : ''),
            'isVideo' => ($typeRaw === 'video'),
        );
        $songInfo['global_index'] = count($flatList) + 1;
        $albumData[$currentGroup][] = $songInfo;
        $flatList[] = $songInfo;
    }
    return array($albumData, $flatList);
}

function asmr_lazy_proxy_key($realUrl, $fn = '')
{
    if (!$realUrl) return '';
    $b64 = rtrim(strtr(base64_encode($realUrl), '+/', '-_'), '=');
    return $b64 . '|' . rawurlencode($fn ? $fn : '');
}

function asmr_resolve_proxy_key($key)
{
    if (strpos($key, '|') === false && strlen($key) <= 32) {
        $dir = _asmr_cache_dir();
        $safe = preg_replace('/[^A-Za-z0-9_]/', '', $key);
        $f = $dir . '/px_' . $safe . '.dat';
        if (is_file($f)) {
            $payload = @json_decode(@file_get_contents($f), true);
            if (is_array($payload) && !empty($payload['url']) && time() - filemtime($f) <= 12 * 3600) {
                return $payload;
            }
            @unlink($f);
        }
        return null;
    }
    $parts = explode('|', $key, 2);
    if (count($parts) < 2) return null;
    $url = base64_decode(strtr($parts[0], '-_', '+/'));
    if (!$url) return null;
    return array('url' => $url, 'fn' => isset($parts[1]) ? rawurldecode($parts[1]) : '');
}

function asmr_proxy_url($realUrl, $fn = '')
{
    if (!$realUrl) return '';
    $k = asmr_lazy_proxy_key($realUrl, $fn);
    return '?asmr_stream=1&k=' . $k . '&_=' . time();
}

function asmr_stream_proxy(AsmrClient $client, $marker)
{
    $payload = asmr_resolve_proxy_key($marker);
    if (!is_array($payload) || empty($payload['url'])) {
        header('HTTP/1.1 410 Gone');
        echo 'link expired or invalid';
        return;
    }
    if (empty($payload['fn'])) {
        $path = parse_url($payload['url'], PHP_URL_PATH);
        $payload['fn'] = $path ? basename($path) : 'media';
    }
    $client->ensureLogin();
    $client->proxyStream($payload['url'], $payload['fn']);
}