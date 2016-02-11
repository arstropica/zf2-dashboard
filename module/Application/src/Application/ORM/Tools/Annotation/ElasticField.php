<?php

namespace Application\ORM\Tools\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Search\Mapping\Annotations\Field;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
final class ElasticField extends Field {
	
	/**
	 *
	 * @var boolean
	 */
	public $includeInAll;
	
	/**
	 *
	 * @var string
	 */
	public $index;
	
	/**
	 *
	 * @var array
	 */
	public $fields;
	
	/**
	 *
	 * @var array
	 */
	public $properties;
	
	/**
	 *
	 * @var string
	 */
	public $analyzer;
	
	/**
	 *
	 * @var string
	 */
	public $path;
	
	/**
	 *
	 * @var string
	 */
	public $indexName;
	
	/**
	 *
	 * @var boolean
	 */
	public $store;
	
	/**
	 *
	 * @var mixed
	 */
	public $nullValue;
	
	/*
	 * @var string
	 */
	public $indexOptions;

}
