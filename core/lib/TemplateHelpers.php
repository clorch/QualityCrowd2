<?php
namespace Clho\QualityCrowd;

class TemplateHelpers
{
    /**
     * trims text to a space then adds ellipses if desired
     * @param string $input text to trim
     * @param int $length in characters to trim to
     * @param bool $ellipses if ellipses (...) are to be added
     * @param bool $strip_html if html tags are to be stripped
     * @return string 
     */
    public function trimText($input, $length, $ellipses = true, $strip_html = true) {
        //strip tags, if desired
        if ($strip_html) {
            $input = strip_tags($input);
        }
      
        //no need to trim, already shorter than trim length
        if (strlen($input) <= $length) {
            return $input;
        }
      
        //find last space within length
        $last_space = strrpos(substr($input, 0, $length), ' ');
        $trimmed_text = substr($input, 0, $last_space);
      
        //add ellipses (...)
        if ($ellipses) {
            $trimmed_text .= '...';
        }
      
        return $trimmed_text;
    }

    public function formatTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $seconds = $seconds - ($hours * 3600);
        $minutes = floor($seconds / 60);
        $seconds = round($seconds - ($minutes * 60), 0);

        $hours = str_pad($hours, 2 ,'0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2 ,'0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2 ,'0', STR_PAD_LEFT);

        return $hours . ':' . $minutes . ':' . $seconds;
    }

    public function formatFileSize($size) {
        $digits = 1;
        if ($size < 1024) {
            $r = $size;
            $u = "B";
            $digits = 0;
        } elseif ($size < 1024 * 1024) {
            $r = round($size / 1024, 1);
            $u = "KiB";
        } elseif ($size < 1024 * 1024 * 1024) {
            $r = round($size / 1024 / 1024, 1);
            $u = "MiB"; 
        } else {
            $r = round($size / 1024 / 1024 / 1024, 1);
            $u = "GiB"; 
        }       

        $r = number_format($r, $digits, ',', '') . " " . $u;
        return $r;
    }

    public function formatPropertyValue($pv)
    {
        if ($pv === true) return '<i>True</i>';
        if ($pv === false) return '<i>False</i>';
        if ($pv === '') return '';
        return $this->trimText($pv, 90);
    }

    public function ifset(&$var) 
    {
        return (isset($var) ? $var : '');
    }

    public function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public function js($file)
    {
        $str = '<script type="text/javascript" src="'.BASE_URL.$file.'"></script>'."\n";
        return $str;
    }

    public function css($file)
    {
        $str = '<link rel="stylesheet" href="'.BASE_URL.$file.'">'."\n";
        return $str;
    }

    public function link($text, $url)
    {
        $str = '<a href="'.BASE_URL.$url.'">'.$text.'</a>';
        return $str;
    }

    public function csvEscape($value) 
    {
        if (is_numeric($value)) {
            return $value;
        } else {
            return '"'.str_replace('"', '\"', $value).'"';
        }
    }
}
