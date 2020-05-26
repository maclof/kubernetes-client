<?php namespace Maclof\Kubernetes\Repositories\Utils;

class JSONStreamingParserHelper
{
	public static function isDigit(string $ctext): bool
	{
		// Only concerned with the first character in a number.
		return ctype_digit($ctext) || '-' === $ctext;
	}

	public static function isHexCharacter(string $char): bool
	{
		return ctype_xdigit($char);
	}

	public static function convertCodepointToCharacter(int $char): string
	{
		if ($char <= 0x7F) {
			return \chr($char);
		}
		if ($char <= 0x7FF) {
			return \chr(($char >> 6) + 192).\chr(($char & 63) + 128);
		}
		if ($char <= 0xFFFF) {
			return \chr(($char >> 12) + 224).\chr((($char >> 6) & 63) + 128).\chr(($char & 63) + 128);
		}
		if ($char <= 0x1FFFFF) {
			return \chr(($char >> 18) + 240)
				.\chr((($char >> 12) & 63) + 128)
				.\chr((($char >> 6) & 63) + 128)
				.\chr(($char & 63) + 128);
		}

		return '';
	}

	public static function convertToNumber(string $text)
	{
		// thanks to #andig for the fix for big integers
		if (ctype_digit($text) && (float) $text === (float) ((int) $text)) {
			// natural number PHP_INT_MIN < $num < PHP_INT_MAX
			return (int) $text;
		}

		// real number or natural number outside PHP_INT_MIN ... PHP_INT_MAX
		return (float) $text;
	}
}