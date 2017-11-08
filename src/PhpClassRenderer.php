<?php
namespace Sellastica\PhpGenerator;

class PhpClassRenderer extends AbstractRenderer
{
	/** @var string Class or interface */
	private $classType = 'class';
	/** @var string */
	private $className;
	/** @var bool */
	private $abstract = false;
	/** @var array */
	private $extends = [];
	/** @var array */
	private $implements = [];
	/** @var string|null */
	private $namespace;
	/** @var array */
	private $imports = [];
	/** @var PhpDocRenderer|null */
	private $annotation;
	/** @var array */
	private $traits = [];
	/** @var PhpPropertyRenderer[] */
	private $properties = [];
	/** @var PhpMethodRenderer[] */
	private $methods = [];


	/**
	 * @param string $className
	 * @param array $extends
	 * @param array $implements
	 */
	public function __construct(string $className, array $extends = [], array $implements = [])
	{
		$this->className = $className;
		$this->extends = $extends;
		$this->implements = $implements;
	}

	/**
	 * @param string $classType Class or interface
	 * @return $this
	 */
	public function classType(string $classType)
	{
		$this->classType = $classType;
		return $this;
	}

	/**
	 * @param bool $abstract
	 * @return $this
	 */
	public function abstract(bool $abstract = true)
	{
		$this->abstract = $abstract;
		return $this;
	}

	/**
	 * @param string $import
	 * @return $this
	 */
	public function import(string $import)
	{
		$this->imports[] = $import;
		return $this;
	}

	/**
	 * @return PhpDocRenderer
	 */
	public function annotation(): PhpDocRenderer
	{
		return $this->annotation = new PhpDocRenderer();
	}

	/**
	 * @param string $trate
	 * @return $this
	 */
	public function trait(string $trate)
	{
		$this->traits[] = $trate;
		return $this;
	}

	/**
	 * @param PhpPropertyRenderer $property
	 */
	public function property(PhpPropertyRenderer $property)
	{
		$this->properties[] = $property;
	}

	/**
	 * @param string $name
	 * @param string $visibility
	 * @return PhpPropertyRenderer
	 */
	public function createProperty(string $name, string $visibility = 'private'): PhpPropertyRenderer
	{
		return $this->properties[] = new PhpPropertyRenderer($name, $visibility);
	}

	/**
	 * @param string $name
	 * @param string $visibility
	 * @return PhpMethodRenderer
	 */
	public function createMethod(string $name, string $visibility = 'public'): PhpMethodRenderer
	{
		return $this->methods[] = new PhpMethodRenderer($name, $visibility);
	}

	/**
	 * @param string $visibility
	 * @return PhpMethodRenderer
	 */
	public function createConstructor(string $visibility = 'public'): PhpMethodRenderer
	{
		return $this->createMethod('__construct');
	}

	/**
	 * @param string $propertyName
	 * @param string $methodName
	 * @return PhpMethodRenderer
	 */
	public function createGetter(string $propertyName, string $methodName = null): PhpMethodRenderer
	{
		return $this->methods[] = PhpMethodRenderer::getter($propertyName, $methodName);
	}

	/**
	 * @param string $propertyName
	 * @param string $methodName
	 * @param bool $fluent
	 * @return PhpMethodRenderer
	 */
	public function createSetter(
		string $propertyName,
		string $methodName = null,
		bool $fluent = false): PhpMethodRenderer
	{
		return $this->methods[] = PhpMethodRenderer::setter($propertyName, $methodName, $fluent);
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$this->clearIndentation();
		$return = [];
		if ($this->phpBeginning) {
			$return[] = $this->renderLine($this->renderPhpBeginning());
		}

		if ($this->namespace) {
			$return[] = $this->renderLine('namespace ' . $this->namespace . ';');
		}

		if (sizeof($this->imports)) {
			$return[] = null; //new line
			foreach ($this->imports as $import) {
				$return[] = $this->renderLine("use $import;");
			}
		}

		$return[] = null; //new line
		if (isset($this->annotation)) {
			$return[] = $this->annotation->render();
		}

		$return[] = $this->renderLine($this->renderHeader());
		$return[] = $this->renderLine('{');
		$this->indent();
		if (sizeof($this->traits)) {
			foreach ($this->traits as $trait) {
				$return[] = $this->renderLine("use $trait;");
			}

			$return[] = null; //new line
		}

		//class properties
		if (sizeof($this->properties)) {
			foreach ($this->properties as $property) {
				$return[] = $property->render();
			}

			$return[] = null; //new line
		}

		//methods
		if (sizeof($this->methods)) {
			foreach ($this->methods as $key => $method) {
				$return[] = $method->render();
				if ($key != sizeof($this->methods) - 1) {
					$return[] = null; //new line
				}
			}
		}

		$this->outdent();
		$return[] = $this->renderLine('}');

		return implode("\n", $return);
	}

	/**
	 * @return string
	 */
	private function renderHeader(): string
	{
		$return = [];
		if ($this->abstract) {
			$return[] = 'abstract';
		}

		$return[] = $this->classType . ' ' . $this->className;
		if (sizeof($this->extends)) {
			$return[] = 'extends ' . implode(', ', $this->extends);
		}

		if (sizeof($this->implements)) {
			$return[] = 'implements ' . implode(', ', $this->implements);
		}

		return implode(' ', $return);
	}

	/**
	 * @param string $namespace
	 * @return $this
	 */
	public function namespace(string $namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}
}