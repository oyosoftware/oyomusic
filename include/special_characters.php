<?php

/**
 * Converts all UTF-8 accent characters to ASCII characters.
 * Thanks to Rap2hpoutre for extracting these characters from WordPress
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/formatting.php
 * @param $string
 * @param null $locale
 * @return string
 */
function strunacc($string, $locale = null) {
    if (!preg_match('/[\x80-\xff]/', $string)) {
        return $string;
    }

    $chars = [
        // Decompositions for Latin-1 Supplement
        'ª' => 'a',
        'º' => 'o',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'AE',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ð' => 'D',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'TH',
        'ß' => 's',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'ae',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'd',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ý' => 'y',
        'þ' => 'th',
        'ÿ' => 'y',
        'Ø' => 'O',
        // Decompositions for Latin Extended-A
        'Ā' => 'A',
        'ā' => 'a',
        'Ă' => 'A',
        'ă' => 'a',
        'Ą' => 'A',
        'ą' => 'a',
        'Ć' => 'C',
        'ć' => 'c',
        'Ĉ' => 'C',
        'ĉ' => 'c',
        'Ċ' => 'C',
        'ċ' => 'c',
        'Č' => 'C',
        'č' => 'c',
        'Ď' => 'D',
        'ď' => 'd',
        'Đ' => 'D',
        'đ' => 'd',
        'Ē' => 'E',
        'ē' => 'e',
        'Ĕ' => 'E',
        'ĕ' => 'e',
        'Ė' => 'E',
        'ė' => 'e',
        'Ę' => 'E',
        'ę' => 'e',
        'Ě' => 'E',
        'ě' => 'e',
        'Ĝ' => 'G',
        'ĝ' => 'g',
        'Ğ' => 'G',
        'ğ' => 'g',
        'Ġ' => 'G',
        'ġ' => 'g',
        'Ģ' => 'G',
        'ģ' => 'g',
        'Ĥ' => 'H',
        'ĥ' => 'h',
        'Ħ' => 'H',
        'ħ' => 'h',
        'Ĩ' => 'I',
        'ĩ' => 'i',
        'Ī' => 'I',
        'ī' => 'i',
        'Ĭ' => 'I',
        'ĭ' => 'i',
        'Į' => 'I',
        'į' => 'i',
        'İ' => 'I',
        'ı' => 'i',
        'Ĳ' => 'IJ',
        'ĳ' => 'ij',
        'Ĵ' => 'J',
        'ĵ' => 'j',
        'Ķ' => 'K',
        'ķ' => 'k',
        'ĸ' => 'k',
        'Ĺ' => 'L',
        'ĺ' => 'l',
        'Ļ' => 'L',
        'ļ' => 'l',
        'Ľ' => 'L',
        'ľ' => 'l',
        'Ŀ' => 'L',
        'ŀ' => 'l',
        'Ł' => 'L',
        'ł' => 'l',
        'Ń' => 'N',
        'ń' => 'n',
        'Ņ' => 'N',
        'ņ' => 'n',
        'Ň' => 'N',
        'ň' => 'n',
        'ŉ' => 'n',
        'Ŋ' => 'N',
        'ŋ' => 'n',
        'Ō' => 'O',
        'ō' => 'o',
        'Ŏ' => 'O',
        'ŏ' => 'o',
        'Ő' => 'O',
        'ő' => 'o',
        'Œ' => 'OE',
        'œ' => 'oe',
        'Ŕ' => 'R',
        'ŕ' => 'r',
        'Ŗ' => 'R',
        'ŗ' => 'r',
        'Ř' => 'R',
        'ř' => 'r',
        'Ś' => 'S',
        'ś' => 's',
        'Ŝ' => 'S',
        'ŝ' => 's',
        'Ş' => 'S',
        'ş' => 's',
        'Š' => 'S',
        'š' => 's',
        'Ţ' => 'T',
        'ţ' => 't',
        'Ť' => 'T',
        'ť' => 't',
        'Ŧ' => 'T',
        'ŧ' => 't',
        'Ũ' => 'U',
        'ũ' => 'u',
        'Ū' => 'U',
        'ū' => 'u',
        'Ŭ' => 'U',
        'ŭ' => 'u',
        'Ů' => 'U',
        'ů' => 'u',
        'Ű' => 'U',
        'ű' => 'u',
        'Ų' => 'U',
        'ų' => 'u',
        'Ŵ' => 'W',
        'ŵ' => 'w',
        'Ŷ' => 'Y',
        'ŷ' => 'y',
        'Ÿ' => 'Y',
        'Ź' => 'Z',
        'ź' => 'z',
        'Ż' => 'Z',
        'ż' => 'z',
        'Ž' => 'Z',
        'ž' => 'z',
        'ſ' => 's',
        // Decompositions for Latin Extended-B
        'Ș' => 'S',
        'ș' => 's',
        'Ț' => 'T',
        'ț' => 't',
        // Euro Sign
        '€' => 'E',
        // GBP (Pound) Sign
        '£' => '',
        // Vowels with diacritic (Vietnamese)
        // unmarked
        'Ơ' => 'O',
        'ơ' => 'o',
        'Ư' => 'U',
        'ư' => 'u',
        // grave accent
        'Ầ' => 'A',
        'ầ' => 'a',
        'Ằ' => 'A',
        'ằ' => 'a',
        'Ề' => 'E',
        'ề' => 'e',
        'Ồ' => 'O',
        'ồ' => 'o',
        'Ờ' => 'O',
        'ờ' => 'o',
        'Ừ' => 'U',
        'ừ' => 'u',
        'Ỳ' => 'Y',
        'ỳ' => 'y',
        // hook
        'Ả' => 'A',
        'ả' => 'a',
        'Ẩ' => 'A',
        'ẩ' => 'a',
        'Ẳ' => 'A',
        'ẳ' => 'a',
        'Ẻ' => 'E',
        'ẻ' => 'e',
        'Ể' => 'E',
        'ể' => 'e',
        'Ỉ' => 'I',
        'ỉ' => 'i',
        'Ỏ' => 'O',
        'ỏ' => 'o',
        'Ổ' => 'O',
        'ổ' => 'o',
        'Ở' => 'O',
        'ở' => 'o',
        'Ủ' => 'U',
        'ủ' => 'u',
        'Ử' => 'U',
        'ử' => 'u',
        'Ỷ' => 'Y',
        'ỷ' => 'y',
        // tilde
        'Ẫ' => 'A',
        'ẫ' => 'a',
        'Ẵ' => 'A',
        'ẵ' => 'a',
        'Ẽ' => 'E',
        'ẽ' => 'e',
        'Ễ' => 'E',
        'ễ' => 'e',
        'Ỗ' => 'O',
        'ỗ' => 'o',
        'Ỡ' => 'O',
        'ỡ' => 'o',
        'Ữ' => 'U',
        'ữ' => 'u',
        'Ỹ' => 'Y',
        'ỹ' => 'y',
        // acute accent
        'Ấ' => 'A',
        'ấ' => 'a',
        'Ắ' => 'A',
        'ắ' => 'a',
        'Ế' => 'E',
        'ế' => 'e',
        'Ố' => 'O',
        'ố' => 'o',
        'Ớ' => 'O',
        'ớ' => 'o',
        'Ứ' => 'U',
        'ứ' => 'u',
        // dot below
        'Ạ' => 'A',
        'ạ' => 'a',
        'Ậ' => 'A',
        'ậ' => 'a',
        'Ặ' => 'A',
        'ặ' => 'a',
        'Ẹ' => 'E',
        'ẹ' => 'e',
        'Ệ' => 'E',
        'ệ' => 'e',
        'Ị' => 'I',
        'ị' => 'i',
        'Ọ' => 'O',
        'ọ' => 'o',
        'Ộ' => 'O',
        'ộ' => 'o',
        'Ợ' => 'O',
        'ợ' => 'o',
        'Ụ' => 'U',
        'ụ' => 'u',
        'Ự' => 'U',
        'ự' => 'u',
        'Ỵ' => 'Y',
        'ỵ' => 'y',
        // Vowels with diacritic (Chinese, Hanyu Pinyin)
        'ɑ' => 'a',
        // macron
        'Ǖ' => 'U',
        'ǖ' => 'u',
        // acute accent
        'Ǘ' => 'U',
        'ǘ' => 'u',
        // caron
        'Ǎ' => 'A',
        'ǎ' => 'a',
        'Ǐ' => 'I',
        'ǐ' => 'i',
        'Ǒ' => 'O',
        'ǒ' => 'o',
        'Ǔ' => 'U',
        'ǔ' => 'u',
        'Ǚ' => 'U',
        'ǚ' => 'u',
        // grave accent
        'Ǜ' => 'U',
        'ǜ' => 'u',
    ];
    // Used for locale-specific rules
    if ('de_DE' == $locale || 'de_DE_formal' == $locale || 'de_CH' == $locale || 'de_CH_informal' == $locale) {
        $chars['Ä'] = 'Ae';
        $chars['ä'] = 'ae';
        $chars['Ö'] = 'Oe';
        $chars['ö'] = 'oe';
        $chars['Ü'] = 'Ue';
        $chars['ü'] = 'ue';
        $chars['ß'] = 'ss';
    } elseif ('da_DK' === $locale) {
        $chars['Æ'] = 'Ae';
        $chars['æ'] = 'ae';
        $chars['Ø'] = 'Oe';
        $chars['ø'] = 'oe';
        $chars['Å'] = 'Aa';
        $chars['å'] = 'aa';
    } elseif ('ca' === $locale) {
        $chars['l·l'] = 'll';
    } elseif ('sr_RS' === $locale || 'bs_BA' === $locale) {
        $chars['Đ'] = 'DJ';
        $chars['đ'] = 'dj';
    }

    $string = strtr($string, $chars);
    return $string;
}

/**
 * Converts all UTF-8 mark characters to given replace string.
 * Empty replace string replaces all mark characters to a blank
 * Empty pattern replaces all, given pattern only replaces given characters.
 * Pattern characters not in characters array are not converted.
 *
 * @param $string
 * @param ' ' $replace
 * @param null $pattern
 * @return string
 */
function strunmrk($string, $replace = ' ', $pattern = null) {
    $charsall = [
        '!' => $replace, // Exclamtion mark
        '"' => $replace, // Double quotes
        '#' => $replace, // Number sign
        '$' => $replace, // Dollar
        '%' => $replace, // Percent sign
        '&' => $replace, // Ampersand
        "'" => $replace, // Single quote
        '(' => $replace, // Open parenthesis, open bracket
        ')' => $replace, // Close parenthesis, close bracket
        '*' => $replace, // Asterisk
        '+' => $replace, // Plus
        ',' => $replace, // Comma
        '-' => $replace, // Hyphen, minus
        '.' => $replace, // Period, dot, full stop
        '/' => $replace, // Slash, divide
        ':' => $replace, // Colon
        ';' => $replace, // Semicolon
        '<' => $replace, // Less than, open angled bracket
        '=' => $replace, // Equals
        '>' => $replace, // Greater than, close angled bracket
        '?' => $replace, // Question mark
        '@' => $replace, // At sign
        '[' => $replace, // Open bracket
        '\\' => $replace, // Backslash
        ']' => $replace, // Close bracket
        '^' => $replace, // Caret, circumflex
        '_' => $replace, // Underscore
        '`' => $replace, // Grave accent
        '{' => $replace, // Open brace
        '|' => $replace, // Vertcal bar
        '}' => $replace, // Close brace
        '~' => $replace, // Tilde
        // extended ASCII
        '€' => $replace, // Euro sign
        '‚' => $replace, // Single low nine quotation mark
        'ƒ' => $replace, //
        '„' => $replace, // Double low nine quotation mark
        '…' => $replace, // Horizontal ellipsis
        '†' => $replace, // Dagger
        '‡' => $replace, // Double dagger
        'ˆ' => $replace, // Modifier letter circumflex accent
        '‰' => $replace, // Per mille sign
        '‹' => $replace, // Single left-pointing angle quotation
        '‘' => $replace, // Left single quotation mark
        '’' => $replace, // Right single quotation mark
        '“' => $replace, // Left double quotation mark
        '”' => $replace, // Right double quotation mark
        '•' => $replace, // Bullet
        '–' => $replace, // En dash
        '—' => $replace, // Em desh
        '˜' => $replace, // Small tilde
        '™' => $replace, // Trade mark sign
        '›' => $replace, // Single right-pointing angle quotation
        // extended ASCII
        ' ' => $replace, // Non-breaking space
        '¡' => $replace, // Inverted exlamation mark
        '¢' => $replace, // Cent sign
        '£' => $replace, // Pound sign
        '¤' => $replace, // Currency sign
        '¥' => $replace, // Yen sign
        '¦' => $replace, // Pipe
        '§' => $replace, // Section sign
        '¨' => $replace, // Umlaut
        '©' => $replace, // Copyright sign
        'ª' => $replace, // Feminine ordinal indicator
        '«' => $replace, // Left double angle quotes
        '¬' => $replace, // Negation
        '­' => $replace, // Soft hyphen
        '®' => $replace, // Registered trade mark sign
        '¯' => $replace, // Spacing macron, overline
        '°' => $replace, // Degree sign
        '±' => $replace, // Plusminus sign
        '²' => $replace, // Sperscript two, squared
        '³' => $replace, // Superscript three, cubed
        '´' => $replace, // Acute accent, spacing acute
        'µ' => $replace, // Micro sign
        '¶' => $replace, // Pilcrow sign, paragraph sign
        '·' => $replace, // Middle dot, Georgian comma
        '¸' => $replace, // Spacing cedilla
        '¹' => $replace, // Superscript one
        'º' => $replace, // Masculine ordinal indicator
        '»' => $replace, // Right double angle quotes
        '¼' => $replace, // Fraction one quarter
        '½' => $replace, // Fraction one half
        '¾' => $replace, // Fraction three quarters
        '¿' => $replace, // Inverted question mark
        '×' => $replace, // Multiplication sign
        '÷' => $replace, // Division sign
    ];

    $chars = [];
    if ($pattern !== null) {
        $search = str_split($pattern);
        foreach ($search as $value) {
            if (array_key_exists($value, $charsall)) {
                $chars[$value] = $replace;
            }
        }
    } else {
        $chars = $charsall;
    }

    $string = strtr($string, $chars);
    return $string;
}

/**
 * Binary safe case sensitive unaccented string comparison
 *
 * @param $string1
 * @param $string2
 * @return int
 */
function stracccmp($string1, $string2) {
    $string1 = strunaccent($string1);
    $string2 = strunaccent($string2);
    $value = strcmp($string1, $string2);
    return $value;
}

/**
 * Binary safe case insensitive unaccented string comparison
 *
 * @param $string1
 * @param $string2
 * @return int
 */
function stracccasecmp($string1, $string2) {
    $string1 = strunaccent($string1);
    $string2 = strunaccent($string2);
    $value = strcasecmp($string1, $string2);
    return $value;
}

/**
 * Escape access database characters
 *
 * @param $string
 * @return string
 */
function access_escape_string($string) {
    $chars = [
        '[' => "[[]", // Open bracket
        "'" => "''", // Single quote
        '"' => '""', // Double quote
        '?' => "[?]", // Question mark
        '*' => "[*]", // Asterisk
        '#' => "[#]", // Number sign
        '%' => "[%]", // Percent sign
        '_' => "[_]", // underscore
    ];

    $string = strtr($string, $chars);
    return $string;
}

?>