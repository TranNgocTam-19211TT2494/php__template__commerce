<?php
namespace Commerce\Core;

use Carbon\Carbon;
use Commerce\App\Utils\Format;

class Validate
{
    public function __construct()
    {
        
    }
    public static function run($rules, &$instatnce = null)
    {

        $result_flg = true;
        $error_messages = [];
        foreach ($rules as $column => $value) {

            //バリデーションを実行
            $functions = explode("|", $value['validates']);

            foreach ($functions as $function) {
                $error = "none";
                preg_match('/([a-zA-Z0-9]+)\[([a-zA-Z0-9,_]+)\]/', $function, $matches);
                $method = isset($matches[1]) ? $matches[1] : $function;
                $params = isset($matches[2]) ? $matches[2] : null;
                $is_instance_member_method = ($instatnce) ? method_exists($instatnce, $method) : false;
                if ($is_instance_member_method) {
                    $validate_method = [$instatnce, $method];
                    $args = [$value['name'], $column];
                } else {
                    $validate_method = [__NAMESPACE__ . '\Validate', $method];
                    $args = ($instatnce) ? [$value['name'], $instatnce->{$column}] : [$value['name'], $column];
                }

                if ($params !== null) {
                    $vals = explode(",", $params);
                    foreach ($vals as $val) {
                        $args[] = $val;
                    }
                }
                $args[] = &$error;
                //バリデートメソッドを実行
                $is_valid = call_user_func_array($validate_method, $args);
                if (!$is_valid && !$is_instance_member_method) {
                    $error_messages[$column][] = $error;
                }
            }
        }
        $instatnce_error_count = 0;
        if ($instatnce) {
            $instatnce_error_count = count($instatnce->errorMessages);
        }
        if (count($error_messages) !== 0 || $instatnce_error_count !== 0) {
            $result_flg = false;
            if ($instatnce) {
                $instatnce->errorMessages = array_merge($instatnce->errorMessages, $error_messages);
            }
        }
        return $result_flg;
    }

    /**
     * Bali date rules
     * @param string $name
     * @param string $validates
     */
    public static function setRule($name, $validates)
    {
        return array("name" => $name, "validates" => $validates);
    }

    /**
     * Returns whether the input is empty.
     * @param string $name
     * @param mixed $data
     * @param string $error
     * @return bool
     */
    public static function required($name, $data = null, &$error = "")
    {
        if (is_null($data) || $data === "" || $data === "--" || $data === "-" || $data === []) {
            $error = sprintf("%sは必須です。", $name);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Verify if the url is in the correct format
     * @access    public
     * @param string|null $data
     * @param string|null $name
     * @return    Boolean
     */
    public static function isUrl($name, $text = null, &$error = "")
    {
        if (!$text) {
            return true;
        }
        if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $text)) {
            return true;
        } elseif (preg_match('/^\/([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $text)) {
            return true;
        } else {
            $error = sprintf("%sIs not in the correct format.", $name);
            return false;
        }
    }

    /**
     * Verify if it is a number
     * @access    public
     * @param string|null $data
     * @param string|null $name
     * @return    Boolean
     */
    public static function isNumber($name, $text = null, &$error = "")
    {
        if (!$text) {
            return true;
        }
        if (is_numeric($text)) {
            return true;
        } else {
            $error = sprintf("%sPlease enter in half-width numbers.", $name);
            return false;
        }
    }

    /**
     * Verify that the email address is in the correct format
     * @access    public
     * @param string $name
     * @param string|null $email
     * @return bool
     */
    public static function isEmail($name, $email = null, &$error = "")
    {
        if (!$email) {
            return true;
        }
        $email_regexp = '^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$';
        if (!preg_match("/{$email_regexp}/iD", $email)) {
            $error = sprintf("It cannot be used in the format of the specified e-mail address.", $name);
            return false;
        }
        return true;
    }

    /**
     * Verify the correct format of your mobile email address
     * @access    public
     * @param string|null $data
     * @param string|null $name
     * @return    string|null $errorMessage
     */
    public static function isMobileMail($name, $email = null, &$error = "")
    {
        if (!$email) {
            return true;
        }
        if (!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._\-~\+])*@(docomo\.ne\.jp|ezweb\.ne\.jp|softbank\.ne\.jp|i\.softbank\.jp|disney\.ne\.jp|vodafone\.ne\.jp|pdx\.ne\.jp)$/', $email)) {
            $error = sprintf("%sPlease enter the correct mobile email address.", $name);
            return false;
        }
        return true;
    }

    /**
     * Find out if the phone number format is correct
     * @param $name
     * @param string $phone
     * @param string $error
     * @return bool
     */
    public static function isPhoneNumber($name, $phone = null, &$error = "")
    {
        if (!$phone) {
            return true;
        }
        if (!$phone || $phone == "--") {
            return true;
        }
        $phone = Format::phoneNumber($phone);
        if (preg_match('/^\d{2,4}-\d{2,4}-\d{3,4}$/', $phone)) {
            return true;
        }
        $error = sprintf("%sIs not in the correct format.", $name);
        return false;
    }

    /**
     * 正しい郵便番号フォーマットかどうかを調べる
     * /**
     * @param $name
     * @param null $zipcode
     * @param string $error
     * @return bool
     */
    public static function isPostCode($name, $zipcode = null, &$error = "")
    {
        if (!$zipcode) {
            return true;
        }
        if (preg_match('/^\d{7}$/', $zipcode)) {
            return true;
        }
        if (preg_match('/^\d{3}-\d{4}$/', $zipcode)) {
            return true;
        }
        $error = sprintf("%sの形式が正しくありません。", $name);
        return false;
    }

    public static function lengthEqual($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data, 'UTF-8') == $length) {
            return true;
        } else {
            $error = sprintf("%sは%d文字で入力してください。", $name, $length);
            return false;
        }
    }

    public static function lengthEqualOr($name, $data = null, $length, $length2, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data, 'UTF-8') == $length || mb_strlen($data, 'UTF-8') == $length2) {
            return true;
        } else {
            $error = sprintf("%sEnter with% d or% d characters.", $name, $length, $length2);
            return false;
        }
    }

    /**
     * Returns whether it is less than the specified length.
     * @access    public
     * @param string|null $data
     * @param int $length
     * @return    string|null $errorMessage
     */
    public static function lessThan($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data, 'UTF-8') < $length) {
            return true;
        } else {
            $error = sprintf("%sPlease enter less than% d characters.", $name, $length);
            return false;
        }
    }

    /**
     * Returns whether it is greater than the specified length.
     * @access    public
     * @param string|null $data
     * @param int $length
     * @return    string|null $errorMessage
     */
    public static function greaterThan($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data) > $length) {
            return true;
        } else {
            $error = sprintf("%sEnter a value greater than the% d character.", $name, $length);
            return false;
        }
    }

    /**
     * Returns whether it is greater than the specified number.
     * @access    public
     * @param string|null $data
     * @param int $length
     * @return    string|null $errorMessage
     */
    public static function greaterNumEqual($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if ($data >= $length) {
            return true;
        } else {
            $error = sprintf("%sEnter a value greater than% d.", $name, $length);
            return false;
        }
    }

    /**
     * Returns whether it is greater than the specified length.
     * @access    public
     * @param string|null $data
     * @param int $length
     * @return    string|null $errorMessage
     */
    public static function countThan($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if ($data > $length) {
            return true;
        } else {
            $error = sprintf("%sEnter a value greater than% d.", $name, $length);
            return false;
        }
    }

    /**
     * Returns whether it is less than or equal to the specified length.
     * @access    public
     * @param string|null $data
     * @param int $length
     * @return    string|null $errorMessage
     */
    public static function lessEqual($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data) <= $length) {
            return true;
        } else {
            $error = sprintf("%sPlease enter no more than% d characters.", $name, $length);
            return false;
        }
    }

    /**
     * Returns whether it is longer than the specified length.
     * @access    public
     * @param string|null $data
     * @param int $length
     * @return    string|null $errorMessage
     */
    public static function greaterEqual($name, $data = null, $length, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data) >= $length) {
            return true;
        } else {
            $error = sprintf("%sPlease enter at least% d characters.", $name, $length);
            return false;
        }
    }

    /**
     * Returns whether the character string is a half-width alphanumerical character.
     * @access    public
     * @param string|null $data
     * @return    string|null $errorMessage
     */
    public static function isAlphaNumeric($name = "", $data = null, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (preg_match('/^[a-zA-Z0-9\-\_\s]+$/', $data)) {
            return true;
        } else {
            $error = sprintf("%sPlease enter in half-width alphanumerical characters.", $name);
            return false;
        }
    }

    /**
     * Returns whether the character string is a half-width alphanumerical + symbol.
     * @access    public
     * @param string|null $data
     * @return    string|null $errorMessage
     */
    public static function isAlphaNumericSymbol($name = "", $data = null, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (preg_match('/^[a-zA-Z0-9\-\_\.\/]+$/', $data)) {
            return true;
        } else {
            $error = sprintf("%sPlease enter only half-width alphanumericals or symbols (./).", $name);
            return false;
        }
    }

    /**
     * Returns whether all strings are katakana.
     * @access    public
     * @param string|null $data
     * @return    string|null $errorMessage
     */
    public static function isKana($name = "", $data = null, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (preg_match('/^[ァ-ヾ\s　]+$/u', $data)) {
            return true;
        } else {
            $error = sprintf("%sPlease enter in katakana.", $name);
            return false;
        }
    }

    /**
     * Returns whether all strings are katakana.
     * @access    public
     * @param string|null $data
     * @return    string|null $errorMessage
     */
    public static function isHiraKana($name = "", $data = null, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (preg_match('/^(\xe3(\x81[\x81-\xbf]|\x82[\x80-\x93]|\x83\xbc))*$/', $data)) {
            return true;
        } else {
            $error = sprintf("%sPlease enter in hiragana.", $name);
            return false;
        }
    }

    /**
     * Returns whether the length of the string is within the range.
     * @access    public
     * @param string|null $data
     * @param int $min
     * @param int $max
     * @return    string|null $errorMessage
     */
    public static function inRange($name, $data = null, $min, $max, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (mb_strlen($data) >= $min && mb_strlen($data) <= $max) {
            return true;
        }
        $error = sprintf("%sPlease enter% d characters or more and% d characters or less.", $name, $min, $max);
        return false;
    }

    /**
     * Returns whether the given number is within range.
     * @access    public
     * @param int $data
     * @param int $min
     * @param int $max
     * @return    string|null $errorMessage
     */
    public static function inRangeNum($name, $data = null, $min, $max, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if ($data >= $min && $data <= $max) {
            return true;
        }
        $error = sprintf("%sPlease enter% d or more and% d or less.", $name, $min, $max);
        return false;
    }

    /**
     * Returns whether the date is the correct date.
     * @param $m
     * @param $d
     * @param $y
     * @param $name
     * @return string|null $errorMessage
     */
    public static function isValidDate($m, $d, $y, $name)
    {
        return checkdate($m, $d, $y);
    }

    /**
     * Returns whether the date is correct.
     * @param $name
     * @param $data
     * @return string|null $errorMessage
     */
    public static function isDate($name, $data, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        $date_pattern = '/^(\d{4})(\/|-)(\d{1,2}|\d)(\/|-)(\d{1,2}|\d)$/';
        $date_pattern2 = '/^(\d{4})(\/|-)(\d{1,2}|\d)(\/|-)(\d{1,2}|\d) (\d{1,2}):(\d{1,2})$/';
        $date_pattern3 = '/^(\d{4})(\/|-)(\d{1,2}|\d)(\/|-)(\d{1,2}|\d) (\d{1,2}):(\d{1,2}):(\d{1,2})$/';
        if (preg_match($date_pattern, $data, $match)) {
            if (checkdate($match[3], $match[5], $match[1]))  // month, day, year
                return true;
        } else if (preg_match($date_pattern2, $data, $match)) {
            if (checkdate($match[3], $match[5], $match[1]))  // month, day, year
                return true;
        } else if (preg_match($date_pattern3, $data, $match)) {
            if (checkdate($match[3], $match[5], $match[1]))  // month, day, year
                return true;
        }
        $error = sprintf("%sPlease enter in the correct format.", $name);
        return false;
    }

    /**
     * Returns whether the date is correct.
     * @param $name
     * @param $data
     * @return bool
     */
    public static function isFuture($name, $data, $add_day = 1, &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (!self::isDate($name, $data)) {
            return true;
        }
        $now = new Carbon();
        if ($add_day) {
            $now->modify("+{$add_day} days");
        }
        $target = new Carbon($data);
        if ($now <= $target) {
            return true;
        }
        $error = sprintf("%sCan only be registered in the future date and time.", $name);
        return false;
    }

    /**
     * Returns whether it is in the correct format.
     * @param $name
     * @param $data
     * @return bool
     */
    public static function isFormat($name, $data, $format = '/^(0|1|2|3)(\d{1})$/', &$error = "")
    {
        if ($data === "" || $data === null) {
            return true;
        }
        if (preg_match($format, $data)) {
            return true;
        }
        $error = sprintf("%sPlease enter in the correct format.", $name);
        return false;
    }

    /**
     * Returns whether the string is suitable for a url.
     * @access    public
     * @param string|null $data
     * @return    bool
     */
    public static function isValidUrl($data = "")
    {
        if (empty($data)) {
            return true;
        }
        return preg_match('/^[a-zA-Z0-9\.\_-]+$/', $data);
    }

    /**
     * Whether the string is a color code
     * @param $name
     * @param $data
     * @param string $error
     * @return bool
     */
    public static function isColor($name, $data, &$error = "")
    {
        if (empty($data)) {
            return true;
        }
        if (preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $data)) {
            return true;
        }
        $error = sprintf("%sPlease enter in the correct format.", $name);
        return false;
    }

    /**
     * @param $name
     * @param $data
     * @param string $error
     * @return bool
     */
    public static function isStrongPassword($name, $data, &$error = "")
    {
        if (empty($data)) {
            return true;
        }
        $r1 = '/[A-Z]/';
        $r2 = '/[a-z]/';
        $r3 = '#[ !"\#$%&\'()*+,-./:;<=>?@[\]^_`{|}~]#';
        $r4 = '/[0-9]/';

        $count = 0;
        //Including uppercase
        if (preg_match($r1, $data)) {
            $count++;
        }

        //Including lowercase
        if (preg_match($r2, $data)) {
            $count++;
        }

        //Including symbols
        if (preg_match($r3, $data)) {
            $count++;
        }
        if ($count < 3) {
            //Including numbers
            if (!preg_match($r4, $data)) {
                $count++;
            }
        }

        $result = true;
        //The same character is not more than 3 consecutive characters
        if (preg_match('/(.)\1{2}/', $data)) {
            $result = false;
        }
        //Contains no characters other than Ascii
        if(!preg_match('/^[\x20-\x7E]+$/', $data)){
            $result = false;
        }
        //10 or more characters
        if(strlen($data) >= 10){
            $result = false;
        }

        if($result && $count < 3){
            return true;
        }

        $error = sprintf("%sIs half-width alphabetic characters (uppercase /lowercase), numbers, symbols( !\"\#$%&\'()*+,-./:;<=>?@[\]^_`{|}~)Please specify 10 characters or more using 4 to 3 types of.", $name);
        return false;
    }

}