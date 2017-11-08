<?php
namespace Sellastica\PhpGenerator;

use Nette\Utils\Strings;
use Sellastica\Reflection\ReflectionProperty;

class PhpDocRenderer extends AbstractRenderer
{
	/** @var string|null */
	private $description;
	/** @var array */
	private $lines = [];
	/** @var string|null */
	private $return;
	/** @var string Whole PhpDoc incl. start and end symbols */
	private $docComment;

	/**
	 * @param string $name
	 * @param string $type
	 * @return $this
	 */
	public function param(string $name, string $type = null)
	{
		$this->lines[] = '@param ' . ($type ? "$type " : '') . '$' . $name;
		return $this;
	}

	/**
	 * @param string $name
	 * @param string $returnType
	 * @param bool $static
	 * @return $this
	 */
	public function method(string $name, string $returnType, bool $static = false)
	{
		$this->lines[] = '@method ' . ($static ? 'static ' : '') . $returnType . ' ' . $name;
		return $this;
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $description
	 * @param string $tag
	 * @return $this
	 */
	public function property(string $name, string $type, string $description = null, string $tag = 'property')
	{
		$line = [];
		$line[] = '@' . $tag;
		$line[] = $type;
		$line[] = Strings::startsWith($name, '$') ? $name : '$' . $name;
		if (!empty($description)) {
			$line[] = $description;
		}

		$this->lines[] = implode(' ', $line);

		return $this;
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $description
	 * @return $this
	 */
	public function propertyRead(string $name, string $type, string $description = null)
	{
		return $this->property($name, $type, $description, 'property-read');
	}

	/**
	 * @param string $what
	 * @return $this
	 */
	public function see(string $what)
	{
		$this->lines[] = '@see ' . $what;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function inheritDoc()
	{
		$this->lines[] = '@inheritDoc';
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function var(string $type)
	{
		$this->lines[] = '@var ' . $type;
		return $this;
	}

	/**
	 * @param $text
	 * @return $this
	 */
	public function line($text)
	{
		$this->lines[] = $text;
		return $this;
	}

	/**
	 * @param string $return
	 * @return $this
	 */
	public function return (string $return)
	{
		$this->return = $return;
		return $this;
	}

	/**
	 * @return string
	 */
	public function renderAsPropertyAnnotation(): string
	{
		if (!$this->description && !sizeof($this->lines)) {
			return '';
		}

		$return = [];
		$return[] = "/**";
		foreach ($this->lines as $line) {
			$return[] = $line;
		}

		if ($this->description) {
			$return[] = $this->description;
		}

		$return[] = "*/";

		return $this->renderLine(implode(' ', $return));
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		if (isset($this->docComment)) {
			return $this->renderLine($this->docComment);
		} elseif (!$this->description && !sizeof($this->lines) && !isset($this->return)) {
			return '';
		}

		$return = [];
		$return[] = $this->renderLine("/**");
		if ($this->description) {
			$return[] = $this->renderLine($this->description);
		}

		foreach ($this->lines as $line) {
			$return[] = $this->renderLine(' * ' . $line);
		}

		if (isset($this->return)) {
			$return[] = $this->renderLine(" * @return $this->return");
		}

		$return[] = $this->renderLine(" */");

		return implode("\n", $return);
	}

	/**
	 * @param array|ReflectionProperty[] $params
	 * @param string $return
	 * @return self
	 */
	public static function create(array $params = [], string $return = null)
	{
		$doc = new self();
		foreach ($params as $param) {
			$doc->param($param->getName(), $param->getType());
		}

		if (isset($return)) {
			$doc->return($return);
		}

		return $doc;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function propertyAnnotation(string $type)
	{
		return "/** @var " . $type . " */\n";
	}

	/**
	 * @param string $docComment
	 */
	public function docComment(string $docComment)
	{
		$this->docComment = $docComment;
	}
}