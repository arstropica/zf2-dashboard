<?php

namespace Agent\Entity\Filters;

use Doctrine\ORM\Mapping as ORM;
use Account\Entity\Account;
use Application\Provider\EntityDataTrait;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;

/**
 * AccountFilter
 *
 * @Annotation\Instance("\Agent\Entity\Filters\AccountFilter")
 * @ORM\Table(name="agent_filter_account")
 * @ORM\Entity
 */
class AccountFilter {
	
	use EntityDataTrait;
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="mode", type="string", nullable=false)
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Required(false)
	 *      @Annotation\Type("Zend\Form\Element\Select")
	 *      @Annotation\Options({
	 *      "required":"false",
	 *      "label":"Filter by: ",
	 *      "empty_option": "None",
	 *      "value_options": {
	 *      "orphan":"Unassigned",
	 *      "account":"Account",
	 *      }
	 *      })
	 *     
	 */
	private $mode;
	
	/**
	 *
	 * @var \Account\Entity\Account @ORM\ManyToOne(targetEntity="Account\Entity\Account")
	 *      @ORM\JoinColumn(name="account_id",
	 *      referencedColumnName="id",
	 *      nullable=true
	 *      )
	 *      @Annotation\Instance("\Account\Entity\Account")
	 *      @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Validator({"name":"Digits"})
	 *      @Annotation\Required(false)
	 *      @Annotation\Options({
	 *      "required":"false",
	 *      "label":"Account",
	 *      "empty_option": "Select Account",
	 *      "target_class":"Account\Entity\Account",
	 *      "property": "name"
	 *      })
	 */
	private $account;
	
	/**
	 *
	 * @var Collection @ORM\OneToMany(targetEntity="Agent\Entity\Filter",
	 *      mappedBy="accountFilter",
	 *      cascade={"persist"})
	 */
	private $filters;

	/**
	 *
	 * @return integer $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @param integer $id        	
	 *
	 * @return AccountFilter
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return string $mode
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 *
	 * @param string $mode        	
	 *
	 * @return AccountFilter
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 *
	 * @return Account $account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 *
	 * @param \Account\Entity\Account $account        	
	 *
	 * @return AccountFilter	 *
	 */
	public function setAccount($account)
	{
		$this->account = $account;
		return $this;
	}

	/**
	 *
	 * @return Collection $filters
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 *
	 * @param Collection $filters        	
	 *
	 * @return AccountFilter
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
		return $this;
	}

	/**
	 * Add filters to the filter.
	 *
	 * @param Collection $filters        	
	 *
	 * @return void
	 */
	public function addFilters(Collection $filters)
	{
		foreach ( $filters as $filter ) {
			if (!$this->filters->contains($filter)) {
				$this->filters->add($filter);
				$filter->setAccountFilter($this);
			}
		}
	}

	/**
	 *
	 * @param Collection $filters        	
	 *
	 * @return AccountFilter
	 */
	public function removeFilters(Collection $filters)
	{
		foreach ( $filters as $filter ) {
			if ($this->filters->contains($filter)) {
				$this->filters->removeElement($filter);
				$filter->setAccountFilter(null);
			}
		}
		
		return $this;
	}

}

?>