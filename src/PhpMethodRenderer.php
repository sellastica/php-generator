<?php
namespace Sellastica\PhpGenerator;

use Nette\Utils\Strings;

class PhpMethodRenderer extends AbstractRenderer
{
	/** @var string */
	private $name;
	/** @var bool */
	private $static = false;
	/** @var string|null */
	private $visibility;
	/** @var PhpMethodParameterRenderer[] */
	private $params = [];
	/** @var string */
	private $return;
	/** @var PhpDocRenderer|null */
	private $annotation;
	/** @var array */
	private $body = [];


	/**
	 * @param string $name
	 * @param string $visibility
	 */
	public function __construct(string $name, string $visibility = 'public')
	{
		$this->name = $name;
		$this->visibility = $visibility;
	}

	/**
	 * @return $this
	 */
	public function static()
	{
		$this->static = true;
		return $this;
	}

	/**
	 * @return PhpDocRenderer
	 */
	public function createAnnotation(): PhpDocRenderer
	{
		return $this->annotation = new PhpDocRenderer();
	}

	/**
	 * @param string $return
	 * @return $this
	 */
	public function return(string $return)
	{
		$this->return = $return;
		return $this;
	}

	/**
	 * @param string $name
	 * @return PhpMethodParameterRenderer
	 */
	public function createParameter(string $name): PhpMethodParameterRenderer
	{
		return $this->params[] = new PhpMethodParameterRenderer($name);
	}

	/**
	 * @param PhpMethodParameterRenderer $parameter
	 * @return $this
	 */
	public function addParameter(PhpMethodParameterRenderer $parameter)
	{
		$this->params[] = $parameter;
		return $this;
	}

	/**
	 * @param \ReflectionMethod $reflectionMethod
	 * @return string
	 */
	public function parseContent(\ReflectionMethod $reflectionMethod)
	{
		$filename = $reflectionMethod->getFileName();
		$startLine = $reflectionMethod->getStartLine();
		$endLine = $reflectionMethod->getEndLine();
		$length = $endLine - $startLine;
		$source = file($filename);
		$body = implode('', array_slice($source, $startLine, $length));
		$body = Strings::after($body, '{');
		$body = Strings::before($body, '}', -1);

		return trim($body);
	}

	/**
	 * @param string $line
	 * @param int $indent
	 * @return $this
	 */
	public function addBody(string $line, int $indent = 0)
	{
		$this->body[] = $this->renderLine($line, $indent);
		return $this;
	}

	/**
	 * @param string $body
	 * @return $this
	 */
	public function setBody(string $body)
	{
		$this->body = [$body];
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function propertyAssignation(string $name)
	{
		$this->body[] = '$this->' . $name . ' = $' . $name . ";";
		return $this;
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$return = [];
		if ($this->annotation) {
			$return[] = $this->annotation->render();
		}

		$heading = '';
		if ($this->visibility) {
			$heading .= $this->visibility . ' ';
		}

		if ($this->static) {
			$heading .= 'static ';
		}

		$heading .= 'function ' . $this->name . '(';
		if (!sizeof($this->params)) {
			$heading .= $this->headingEnd();
			$return[] = $this->renderLine($heading);
		} elseif (sizeof($this->params) === 1) {
			$heading .= trim($this->params[0]->render());
			$heading .= $this->headingEnd();
			$return[] = $this->renderLine($heading);
		} else {
			$return[] = $this->renderLine($heading);
			$params = [];
			$this->indent();
			foreach ($this->params as $param) {
				$params[] = $param->render();
			}

			$this->outdent();
			$return[] = implode(",\n", $params);
			$return[] = $this->renderLine($this->headingEnd());
		}

		$return[] = $this->renderLine('{');
		$this->indent();
		foreach ($this->body as $line) {
			$return[] = $this->renderLine($line);
		}

		$this->outdent();
		$return[] = $this->renderLine('}');

		return implode("\n", $return);
	}

	/**
	 * @return string
	 */
	private function headingEnd()
	{
		return ')' . ($this->return ? ': ' . $this->return : '');
	}

	/**
	 * @param string $propertyName
	 * @param string $methodName
	 * @return PhpMethodRenderer
	 */
	public static function getter(
		string $propertyName,
		string $methodName = null
	): self
	{
		$getter = new self($methodName ?? 'get' . ucfirst($propertyName));
		$getter->addBody('return $this->' . $propertyName . ';');

		return $getter;
	}

	/**
	 * @param string $propertyName
	 * @param string $methodName
	 * @param bool $fluent
	 * @return PhpMethodRenderer
	 */
	public static function setter(
		string $propertyName,
		string $methodName = null,
		bool $fluent = false
	): self
	{
		$setter = new self($methodName ?? 'set' . ucfirst($propertyName));
		$setter->addBody('$this->' . $propertyName . ' = $' . $propertyName . ';');
		if (true === $fluent) {
			$setter->addBody('return $this;');
		}

		return $setter;
	}
}
