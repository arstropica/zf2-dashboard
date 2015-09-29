<?php
namespace TenStreet\Entity;

class Authentication
{

	protected $ClientId;

	protected $Password;

	protected $Service;

	public function getArrayCopy ()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $ClientId
	 */
	public function getClientId ()
	{
		return $this->ClientId;
	}

	/**
	 *
	 * @return the $Password
	 */
	public function getPassword ()
	{
		return $this->Password;
	}

	/**
	 *
	 * @return the $Service
	 */
	public function getService ()
	{
		return $this->Service;
	}

	/**
	 *
	 * @param field_type $ClientId        	
	 */
	public function setClientId ($ClientId)
	{
		$this->ClientId = $ClientId;
		return $this;
	}

	/**
	 *
	 * @param field_type $Password        	
	 */
	public function setPassword ($Password)
	{
		$this->Password = $Password;
		return $this;
	}

	/**
	 *
	 * @param field_type $Service        	
	 */
	public function setService ($Service)
	{
		$this->Service = $Service;
		return $this;
	}
}