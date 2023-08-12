<?php

/*
Copyright (c) 2008 Sebastián Grignoli
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
3. Neither the name of copyright holders nor the names of its
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/
/**
 * @author   "Sebastián Grignoli" <grignoli@gmail.com>
 * @package  Encoding
 * @version  2.0
 * @link     https://github.com/neitanod/forceutf8
 * @example  https://github.com/neitanod/forceutf8
 * @license  Revised BSD
 */
namespace VendorDuplicator\ForceUTF8;

class Encoding
{
    const ICONV_TRANSLIT = "TRANSLIT";
    const ICONV_IGNORE = "IGNORE";
    const WITHOUT_ICONV = "";
    protected static $win1252ToUtf8 = array(128 => "€", 130 => "‚", 131 => "ƒ", 132 => "„", 133 => "…", 134 => "†", 135 => "‡", 136 => "ˆ", 137 => "‰", 138 => "Š", 139 => "‹", 140 => "Œ", 142 => "Ž", 145 => "‘", 146 => "’", 147 => "“", 148 => "”", 149 => "•", 150 => "–", 151 => "—", 152 => "˜", 153 => "™", 154 => "š", 155 => "›", 156 => "œ", 158 => "ž", 159 => "Ÿ");
    protected static $brokenUtf8ToUtf8 = array("" => "€", "" => "‚", "" => "ƒ", "" => "„", "" => "…", "" => "†", "" => "‡", "" => "ˆ", "" => "‰", "" => "Š", "" => "‹", "" => "Œ", "" => "Ž", "" => "‘", "" => "’", "" => "“", "" => "”", "" => "•", "" => "–", "" => "—", "" => "˜", "" => "™", "" => "š", "" => "›", "" => "œ", "" => "ž", "" => "Ÿ");
    protected static $utf8ToWin1252 = array("€" => "\x80", "‚" => "\x82", "ƒ" => "\x83", "„" => "\x84", "…" => "\x85", "†" => "\x86", "‡" => "\x87", "ˆ" => "\x88", "‰" => "\x89", "Š" => "\x8a", "‹" => "\x8b", "Œ" => "\x8c", "Ž" => "\x8e", "‘" => "\x91", "’" => "\x92", "“" => "\x93", "”" => "\x94", "•" => "\x95", "–" => "\x96", "—" => "\x97", "˜" => "\x98", "™" => "\x99", "š" => "\x9a", "›" => "\x9b", "œ" => "\x9c", "ž" => "\x9e", "Ÿ" => "\x9f");
    static function toUTF8($text)
    {
        /**
         * Function \ForceUTF8\Encoding::toUTF8
         *
         * This function leaves UTF8 characters alone, while converting almost all non-UTF8 to UTF8.
         *
         * It assumes that the encoding of the original string is either Windows-1252 or ISO 8859-1.
         *
         * It may fail to convert characters to UTF-8 if they fall into one of these scenarios:
         *
         * 1) when any of these characters:   ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß
         *    are followed by any of these:  ("group B")
         *                                    ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶•¸¹º»¼½¾¿
         * For example:   %ABREPRESENT%C9%BB. «REPRESENTÉ»
         * The "«" (%AB) character will be converted, but the "É" followed by "»" (%C9%BB)
         * is also a valid unicode character, and will be left unchanged.
         *
         * 2) when any of these: àáâãäåæçèéêëìíîï  are followed by TWO chars from group B,
         * 3) when any of these: ðñòó  are followed by THREE chars from group B.
         *
         * @name toUTF8
         * @param string $text  Any string.
         * @return string  The same string, UTF8 encoded
         *
         */
        if (\is_array($text)) {
            foreach ($text as $k => $v) {
                $text[$k] = self::toUTF8($v);
            }
            return $text;
        }
        if (!\is_string($text)) {
            return $text;
        }
        $max = self::strlen($text);
        $buf = "";
        for ($i = 0; $i < $max; $i++) {
            $c1 = $text[$i];
            if ($c1 >= "\xc0") {
                //Should be converted to UTF8, if it's not UTF8 already
                $c2 = $i + 1 >= $max ? "\x00" : $text[$i + 1];
                $c3 = $i + 2 >= $max ? "\x00" : $text[$i + 2];
                $c4 = $i + 3 >= $max ? "\x00" : $text[$i + 3];
                if ($c1 >= "\xc0" & $c1 <= "\xdf") {
                    //looks like 2 bytes UTF8
                    if ($c2 >= "\x80" && $c2 <= "\xbf") {
                        //yeah, almost sure it's UTF8 already
                        $buf .= $c1 . $c2;
                        $i++;
                    } else {
                        //not valid UTF8.  Convert it.
                        $cc1 = \chr(\ord($c1) / 64) | "\xc0";
                        $cc2 = $c1 & "?" | "\x80";
                        $buf .= $cc1 . $cc2;
                    }
                } elseif ($c1 >= "\xe0" & $c1 <= "\xef") {
                    //looks like 3 bytes UTF8
                    if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf") {
                        //yeah, almost sure it's UTF8 already
                        $buf .= $c1 . $c2 . $c3;
                        $i = $i + 2;
                    } else {
                        //not valid UTF8.  Convert it.
                        $cc1 = \chr(\ord($c1) / 64) | "\xc0";
                        $cc2 = $c1 & "?" | "\x80";
                        $buf .= $cc1 . $cc2;
                    }
                } elseif ($c1 >= "\xf0" & $c1 <= "\xf7") {
                    //looks like 4 bytes UTF8
                    if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf") {
                        //yeah, almost sure it's UTF8 already
                        $buf .= $c1 . $c2 . $c3 . $c4;
                        $i = $i + 3;
                    } else {
                        //not valid UTF8.  Convert it.
                        $cc1 = \chr(\ord($c1) / 64) | "\xc0";
                        $cc2 = $c1 & "?" | "\x80";
                        $buf .= $cc1 . $cc2;
                    }
                } else {
                    //doesn't look like UTF8, but should be converted
                    $cc1 = \chr(\ord($c1) / 64) | "\xc0";
                    $cc2 = $c1 & "?" | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } elseif (($c1 & "\xc0") === "\x80") {
                // needs conversion
                if (isset(self::$win1252ToUtf8[\ord($c1)])) {
                    //found in Windows-1252 special cases
                    $buf .= self::$win1252ToUtf8[\ord($c1)];
                } else {
                    $cc1 = \chr(\ord($c1) / 64) | "\xc0";
                    $cc2 = $c1 & "?" | "\x80";
                    $buf .= $cc1 . $cc2;
                }
            } else {
                // it doesn't need conversion
                $buf .= $c1;
            }
        }
        return $buf;
    }
    static function toWin1252($text, $option = self::WITHOUT_ICONV)
    {
        if (\is_array($text)) {
            foreach ($text as $k => $v) {
                $text[$k] = self::toWin1252($v, $option);
            }
            return $text;
        } elseif (\is_string($text)) {
            return static::utf8_decode($text, $option);
        } else {
            return $text;
        }
    }
    static function toISO8859($text, $option = self::WITHOUT_ICONV)
    {
        return self::toWin1252($text, $option);
    }
    static function toLatin1($text, $option = self::WITHOUT_ICONV)
    {
        return self::toWin1252($text, $option);
    }
    static function fixUTF8($text, $option = self::WITHOUT_ICONV)
    {
        if (\is_array($text)) {
            foreach ($text as $k => $v) {
                $text[$k] = self::fixUTF8($v, $option);
            }
            return $text;
        }
        if (!\is_string($text)) {
            return $text;
        }
        $last = "";
        while ($last != $text) {
            $last = $text;
            $text = self::toUTF8(static::utf8_decode($text, $option));
        }
        $text = self::toUTF8(static::utf8_decode($text, $option));
        return $text;
    }
    static function UTF8FixWin1252Chars($text)
    {
        // If you received an UTF-8 string that was converted from Windows-1252 as it was ISO8859-1
        // (ignoring Windows-1252 chars from 80 to 9F) use this function to fix it.
        // See: http://en.wikipedia.org/wiki/Windows-1252
        return \str_replace(\array_keys(self::$brokenUtf8ToUtf8), \array_values(self::$brokenUtf8ToUtf8), $text);
    }
    static function removeBOM($str = "")
    {
        if (\substr($str, 0, 3) === \pack("CCC", 0xef, 0xbb, 0xbf)) {
            $str = \substr($str, 3);
        }
        return $str;
    }
    protected static function strlen($text)
    {
        return \function_exists('mb_strlen') && (int) \ini_get('mbstring.func_overload') & 2 ? \mb_strlen($text, '8bit') : \strlen($text);
    }
    public static function normalizeEncoding($encodingLabel)
    {
        $encoding = \strtoupper($encodingLabel);
        $encoding = \preg_replace('/[^a-zA-Z0-9\\s]/', '', $encoding);
        $equivalences = array('ISO88591' => 'ISO-8859-1', 'ISO8859' => 'ISO-8859-1', 'ISO' => 'ISO-8859-1', 'LATIN1' => 'ISO-8859-1', 'LATIN' => 'ISO-8859-1', 'UTF8' => 'UTF-8', 'UTF' => 'UTF-8', 'WIN1252' => 'ISO-8859-1', 'WINDOWS1252' => 'ISO-8859-1');
        if (empty($equivalences[$encoding])) {
            return 'UTF-8';
        }
        return $equivalences[$encoding];
    }
    public static function encode($encodingLabel, $text)
    {
        $encodingLabel = self::normalizeEncoding($encodingLabel);
        if ($encodingLabel === 'ISO-8859-1') {
            return self::toLatin1($text);
        }
        return self::toUTF8($text);
    }
    protected static function utf8_decode($text, $option = self::WITHOUT_ICONV)
    {
        if ($option == self::WITHOUT_ICONV || !\function_exists('iconv')) {
            $o = \utf8_decode(\str_replace(\array_keys(self::$utf8ToWin1252), \array_values(self::$utf8ToWin1252), self::toUTF8($text)));
        } else {
            $o = \iconv("UTF-8", "Windows-1252" . ($option === self::ICONV_TRANSLIT ? '//TRANSLIT' : ($option === self::ICONV_IGNORE ? '//IGNORE' : '')), $text);
        }
        return $o;
    }
}
