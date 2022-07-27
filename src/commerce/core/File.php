<?php
namespace Commerce\Core;

class File
{
    const imageExtensions = [
        'png', 'jpg', 'gif'
    ];

    const movieExtensions = [
        'mov', 'mpeg', 'mp4', 'webm', 'ogv', 'avi'
    ];

    public static function list($dir)
    {
        $files = [];
        foreach (new \DirectoryIterator($dir) as $file) {
            if (!$file->isDot()) {
                if (!$file->isDir()) {
                    $files[] = $file->getFilename();
                }
            }
        }
        return $files;
    }

    public static function createTmpFile($data)
    {
        $file = Config::getTmpBase() . Crypter::getRandomHash();
        File::write($file, $data);
        return $file;
    }
    /**
     * If there is no file for writing, generate it and adjust the permissions
     * */ 
    public static function make($file, $s)
    {
        if (!file_exists($file)) {
            // 親ディレクトリが無ければ作る
            self::make_dir($file);
        }
        if (self::write($file, $s) === false) {
            die('cannot write file.');
        }
        touch($file);
        chmod($file, 0777);
        return true;
    }

    /**
     * If there is no parent directory, generate it and adjust the permissions
     * */ 
    public static function make_dir($path)
    {
        $dir_limit = 50; // 親階層を上る制限回数
        if (!$parentdir = dirname($path))
            die("cannot mkdir. ({$parentdir})");
        $i = 1;
        if (!is_dir($parentdir)) {
            if ($i > $dir_limit)
                die("limit exceed. ({$parentdir})");
            self::make_dir($parentdir);
            mkdir($parentdir, 0777) or die("cannot mkdir. ({$parentdir})");
            $i++;
        }
        return true;
    }
    /**
     * Write a string to a file
     *
     * @param string $filename
     * @param mixed $data
     * @param int $flags
     * @param resource $context
     * @return boolean
     */
    public static function write($filename, $data, $flags = 0, $context = null)
    {
        return file_put_contents($filename, $data, $flags | LOCK_EX, $context);
    }
    public static function read($filename, $flags = 0, $context = null, $offset = -1, $maxlen = -1)
    {
        if (!is_readable($filename)) {
            return false;
        }
        if ($maxlen < 0) {
            if ($offset < 0)
                return file_get_contents($filename, $flags, $context);
            return file_get_contents($filename, $flags, $context, $offset);
        }
        return file_get_contents($filename, $flags, $context, $offset, $maxlen);
    }
    /**
     * @param $url
     * @return bool|false|string
     */
    public static function getExternal($url)
    {
        try {
            $contextOptions = [
                'http' => ["ignore_errors" => true]
            ];
            $context = stream_context_create($contextOptions);

            $http_response_header = [];
            $ret = file_get_contents($url, false, $context);
            if (empty($http_response_header)) {
                throw new \Exception("error. response header is empty");
            }

            preg_match('/HTTP\/1\.[0|1|x] ([0-9]{3})/', $http_response_header[0], $matches);
            $statusCode = $matches[1];

            if ($statusCode !== "200") {
                throw new \Exception("error. status code:" . $statusCode);
            }
            if (!$ret) {
                throw new \Exception("error. response is empty");
            }
        } catch (\Exception $e) {
            return false;
        }
        return $ret;
    }
    /**
     * Get the file from the outside and save it locally
     * @param string $url
     * @param string $path
     * @return boolean
     */
    public static function get_external_to_local($url, $path)
    {
        $response = file_get_contents($url);
        if (!$response) {
            return false;
        }
        $file_name = basename($url);
        $key = Config::getPublicBase() . $path . $file_name;
        self::write($key, $response);
        return $file_name;
    }
    /**
     * Search for files in the specified directory
     * @param string $dir
     * @param string $filename
     * @return array
     */
    public static function search($dir, $filename)
    {
        $files = [];
        $list = scandir($dir);
        foreach ($list as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            } else if (is_file($dir . $file)) {
                $target_file = $dir . $file;
                if (basename($target_file) == $filename) {
                    $files[] = $target_file;
                }
            } else if (is_dir($dir . $file)) {
                $target_dir = $dir . $file . '/';
                $files = array_merge($files, self::search($target_dir, $filename));
            }
        }
        return $files;
    }
    /**
     * Move files
     * @param String $file
     * @param String $destnation
     */
    public static function move($file, $destnation, $unlink = true)
    {
        if (file_exists($file)) {
            self::make_dir($destnation);
            copy($file, $destnation);
            if ($unlink) {
                unlink($file);
            }
        }
    }
    /**
     * Delete the specified file.pecified file.
     * @param String $file
     */
    public static function delete($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    /**
     * Delete all files
     * @param String $dir
     */
    public static function delete_all($dir)
    {
        $iterator = new \RecursiveDirectoryIterator($dir);
        foreach (new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        //rmdir($dir);
    }
    /**
     * upload file to data
     * @param $name
     * @param $filePath
     * @return boolean
     */
    public static function upload($name, $destnation)
    {
        ini_set('memory_limit', '512M');
        if (!isset($_FILES[$name])) {
            return false;
        }
        if ($_FILES[$name]['error'] !== 0) {
            return false;
        }
        //ディレクトリがなければ作る
        self::make_dir($destnation);
        //ファイルを移動する
        if (move_uploaded_file($_FILES[$name]['tmp_name'], $destnation) == FALSE) {
            return false;
        }
        $finfo = finfo_open(FILEINFO_MIME);
        $type = finfo_file($finfo, $destnation);
        finfo_close($finfo);

        if (strpos($type, 'text/plain') !== false) {
            $data = self::read($destnation);
            $data = str_replace(["\r\n", "\r"], "\n", $data);
            self::write($destnation, $data);
        }
        chmod($destnation, 0777);
        return true;
    }
    public static function getExtensionFromMimeType($file, $mime_type = [])
    {
        $_mime_types = [
            'text' => ['text/plain'],
            'htm' => ['text/html'],
            'html' => ['text/html'],
            'ai' => ['application/postscript'],
            'psd' => ['image/x-photoshop'],
            'eps' => ['application/postscript'],
            'pdf' => ['application/pdf'],
            'swf' => ['application/x-shockwave-flash'],
            'lzh' => ['application/x-lha-compressed'],
            'zip' => ['application/x-zip-compressed'],
            'sit' => ['application/x-stuffit'],
            //OFFICEファイル
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'doc' => ['application/mswordt'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            //画像ファイル
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'gif' => ['image/gif'],
            'png' => ['image/png'],
            'bmp' => ['image/x-bmp'],
            'heic' => ['image/heic'],
            'heif' => ['image/heif'],
            //動画ファイル
            'mov' => ['video/quicktime'],
            'mpeg' => ['video/mpeg'],
            'mp4' => ['video/mp4'],
            'webm' => ['video/webm'],
            'ogv' => ['video/ogg'],
            'avi' => ['video/x-msvideo']
        ];
        $_mime_types = ($mime_type) ?: $_mime_types;

        $type = self::getMimeType($file);
        foreach ($_mime_types as $key => $val) {
            foreach ($val as $data) {
                if (strpos($type, $data) !== false) {
                    return $key;
                }
            }
        }
        return false;
    }
    public static function getMimeTypeFormExtension($ext)
    {
        $_mime_types = [
            'text' => ['text/plain'],
            'htm' => ['text/html'],
            'html' => ['text/html'],
            'ai' => ['application/postscript'],
            'psd' => ['image/x-photoshop'],
            'eps' => ['application/postscript'],
            'pdf' => ['application/pdf'],
            'swf' => ['application/x-shockwave-flash'],
            'lzh' => ['application/x-lha-compressed'],
            'zip' => ['application/x-zip-compressed'],
            'sit' => ['application/x-stuffit'],
            //画像ファイル
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'gif' => ['image/gif'],
            'png' => ['image/png'],
            'bmp' => ['image/x-bmp'],
            //動画ファイル
            'mov' => ['video/quicktime'],
            'mpeg' => ['video/mpeg'],
            'mp4' => ['video/mp4'],
            'webm' => ['video/webm'],
            'ogv' => ['video/ogg'],
            'avi' => ['video/x-msvideo']
        ];
        return $_mime_types[$ext][0] ?? null;
    }
    public static function getMimeType($file)
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $type = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $type;
    }
}