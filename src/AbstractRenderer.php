<?php
namespace Sellastica\PhpGenerator;

class AbstractRenderer
{
	/** @var int */
	private static $indent = 0;
	/** @var bool */
	protected $phpBeginning = false;


	protected function indent()
	{
		self::$indent++;
	}

	protected function outdent()
	{
		self::$indent--;
	}

	protected function clearIndentation()
	{
		self::$indent = 0;
	}

	/**
	 * @param string $line
	 * @param int $indent
	 * @return string
	 */
	protected function renderLine(string $line, int $indent = null): string
	{
		if (!isset($indent)) {
			$indent = self::$indent;
		}
		
		return str_repeat("\t", $indent) . $line;
	}

	/**
	 * @param int $count
	 * @return string
	 */
	public function newLine($count = 1): string
	{
		return str_repeat("\n", $count);
	}

	/**
	 * @return $this
	 */
	public function phpBeginning()
	{
		$this->phpBeginning = true;
		return $this;
	}

	/**
	 * @return string
	 */
	protected function renderPhpBeginning(): string
	{
		return "<?php";
	}

	/**
	 * @param mixed $value
	 * @return string|int|null
	 */
	protected function renderValue($value)
	{
		if (is_null($value)) {
			return 'null';
		} elseif ($value === true) {
			return 'true';
		} elseif ($value === false) {
			return 'false';
		} elseif (is_numeric($value) || $value === '[]') {
			return $value;
		} elseif (is_array($value)) {
			return '[]';
		} else {
			return "'$value'";
		}
	}
}