<?php
class ScanDir {
    private static $files = [];

    public const SORT_DEFAULT = 0;
    public const SORT_REVERSE = 1;
    public const SORT_SHUFFLE = 2;
    public const SORT_PATH_LENGTH = 3;
    public const SORT_MULTI = 4;

    public static function scan($dir, $file_ext, $recursive = false, $sort = ScanDir::SORT_DEFAULT) {
        if (empty($file_ext)) {
            return null;
        }

        // Get list of files in dir(s)
        self::rscan($dir, $file_ext, $recursive);

        // Sort the files
        switch ($sort) {
            case self::SORT_REVERSE:
                return rsort(self::$files);

            case self::SORT_SHUFFLE:
                return shuffle(self::$files);

            case self::SORT_PATH_LENGTH:
                self::path_sort();
                return self::$files;
 
            case self::SORT_MULTI:
                self::path_sort();
                return sort(self::$files);

            default:
                return self::$files;
        }
    }

    public static function asHtmlLinks() {
        $html_links = [];
        foreach (self::$files as $file) {
            $file_parts = pathinfo($file);
            $html_links[] = "{$file_parts['dirname']} / <a href=\"{$file}\">{$file_parts['basename']}</a>";
        }
        return $html_links;
    }

    protected static function rscan($dir, $file_ext, $recursive = false) {
        // Make sure the directory isn't empty
        $working_files = array_slice(scandir($dir), 2);
        if (count($working_files) < 1) {
            return [];
        }

        // Add files that match the entension. And process directories
        // (recursively) if requested.
        for ($i = 0; $i < count($working_files); $i++) {
            $file_parts = pathinfo($dir . "/" . $working_files[$i]);

            // Build full file path for is_dir() to use
            $local_file_path = empty($file_parts['filename']) ? null : $file_parts['filename'];
            if ($local_file_path !== null && !empty($file_parts['dirname'])) {
                $local_file_path = $file_parts['dirname'] . "/" . $file_parts['filename'];
            }

            if (strcasecmp($file_parts['extension'] ?? "", $file_ext) == 0) {
                self::$files[] = str_replace(__DIR__, "", $local_file_path) . "." . $file_parts['extension'];
            } else if (is_dir($local_file_path) && $recursive) {
                self::$files = self::$files + self::rscan($dir . "/" . $file_parts['filename'], $file_ext, $recursive);
            }
        }

        return self::$files;
    }

    protected static function path_sort() {
        return uasort(self::$files, function($a, $b) {
            $a_info = pathinfo($a);
            $b_info = pathinfo($b);
            return (strlen($a_info['dirname']) < strlen($b_info['dirname'])) ? -1 : 1;
        });
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
    ul {
        padding: 0;
    }
    li {
        list-style-type: none;
    }
</style>
</head>
<body>
<h1>Welcome to nginx!</h1>
<p>If you see this page, the nginx web server is successfully installed and
working. Further configuration is required.</p>

<p>For online documentation and support please refer to
<a href="http://nginx.org/">nginx.org</a>.<br/>
Commercial support is available at
<a href="http://nginx.com/">nginx.com</a>.</p>

<p><em>Thank you for using nginx.</em></p>

<?php ScanDir::scan(__DIR__, "php", true, ScanDir::SORT_MULTI); ?>

<ul>
<?php foreach (ScanDir::asHtmlLinks() as $file) { ?>
    <li>
        <?= $file; ?>
    </li>
<?php } ?>
</ul>

</body>
</html>